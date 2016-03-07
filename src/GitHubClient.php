<?php
namespace App;

use App\Exceptions\TooManyResultsException;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 20-02-2016
 * Time: 01:04
 */
class GitHubClient
{
    /**
     * @var Client
     */
    protected $github;

    /**
     * @var ResponseInterface
     */
    protected $lastResponse;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct($output = null)
    {
        $this->github = new Client([
            'base_uri' => 'https://api.github.com',
            'headers' => [
                'Authorization' => 'token ' . getenv('GITHUB_TOKEN')
            ]
        ]);

        $this->output = $output ?: new ConsoleOutput;
    }

    public function get($url)
    {
        return $this->asArray($url);
    }

    public function getRateLimits()
    {
        return $this->asArray('/rate_limit')['resources'];
    }

    public function searchRepositories($query)
    {
        $firstChunk = $this->asArray('/search/repositories', [
            'query' => ['q' => $query, 'per_page' => 100]
        ]);
        $numResults = $firstChunk['total_count'];
        $this->output->writeln("<comment>$numResults found.</comment>");

        if ($numResults > 1000 || $firstChunk['incomplete_results']) {
            throw new TooManyResultsException('Too many or incomplete results!');
        };

        $remainingResults = $this->followPages();

        return array_merge($firstChunk['items'], $remainingResults);
    }

    public function countRepositories($query)
    {
        $result = $this->asArray('/search/repositories', [
            'query' => ['q' => $query]
        ]);

        return $result['total_count'];
    }

    public function getRepository($name)
    {
        return $this->asArray("/repos/$name");
    }

    public function searchInRepository($repoName, $query)
    {
        return $this->asArray('/search/code', [
            'query' => ['q' => "$query repo:$repoName"]
        ]);
    }

    protected function followPages($startingAt = null, $pageCount = null)
    {
        $startingAt = $startingAt ?: $this->getNextPageURL();
        $pageCount = $pageCount ?: $this->totalPageCount();

        if ($pageCount == 1)
            return [];

        $currentPage = $this->getPageNumberFromURL($startingAt);
        $results = [];

        $nextPage = $startingAt;

        while ($nextPage) {
            $this->output->writeln("Fetching page <comment>$currentPage of $pageCount</comment>");
            $results = array_merge($results, $this->get($nextPage)['items']);
            $nextPage = $this->getNextPageURL();
            $currentPage++;
        }

        return $results;
    }

    protected function asArray($uri, $options = [], $method = 'get', $waitForLimitReset = true)
    {
        $this->lastResponse = $this->requestRespectingRateLimit(function () use ($method, $uri, $options) {
            if ($this->output->isDebug())
                $this->output->writeln("$method $uri");

            return $this->github->request($method, $uri, $options);
        }, $waitForLimitReset);

        return json_decode($this->lastResponse->getBody(), true);
    }

    protected function requestRespectingRateLimit(callable $callback, $waitForReset = true)
    {
        try {
            $result = $callback();
            return $result;
        } catch (ClientException $e) {
            $response = $e->getResponse();
            if ($e->getCode() != 403 || (int) $response->getHeader('X-RateLimit-Remaining')[0] !== 0 || !$waitForReset)
                throw $e;

            $currentTime = Carbon::parse($response->getHeader('Date')[0])->timestamp;
            $resetAt = (int) $response->getHeader('X-RateLimit-Reset')[0];
            $timeLeft = $resetAt - $currentTime;

            $this->output->writeln("<comment>Rate limit exceeded, waiting $timeLeft seconds for reset</comment>");

            sleep($timeLeft);
            return $this->requestRespectingRateLimit($callback);
        }
    }

    /**
     * @return string
     */
    protected function getNextPageURL()
    {
        $header = $this->lastResponse->getHeader('Link');
        if ($header) {
            preg_match('%<(.+)>; rel="next",%i', trim($header[0], ','), $matches);

            return array_get($matches, 1);
        }
        return null;
    }

    protected function totalPageCount()
    {
        $header = $this->lastResponse->getHeader('Link');

        if ($header) {
            preg_match('%<(.+)>; rel="last"%i', trim($header[0], ','), $matches);
            $url = array_get($matches, 1);
            $pageNumber = $this->getPageNumberFromURL($url);

            return (int) $pageNumber ?: 1;
        }

        return 1;
    }

    protected function getPageNumberFromURL($url)
    {
        preg_match('%.+page=(\d+).*%', $url, $matches);

        return array_get($matches, 1);
    }

    public function getContent($repo, $path = '')
    {
        return $this->asArray("/repos/$repo/contents/$path");
    }
}
