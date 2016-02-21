<?php
namespace App;

use App\Exceptions\GitHubException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;
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

    public function __construct(OutputInterface $output)
    {
        $this->github = new Client([
            'base_uri' => 'https://api.github.com',
            'headers' => [
                'Authorization' => 'token ' . getenv('GITHUB_TOKEN')
            ]
        ]);

        $this->output = $output;
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
            throw new GitHubException('Too many or incomplete results!');
        };

        $remainingResults = $this->followPages();

        return array_merge($firstChunk['items'], $remainingResults);
    }

    protected function followPages()
    {
        $pageCount = $this->totalPageCount();
        if ($pageCount == 1)
            return [];

        $currentPage = 2;
        $results = [];
        $nextPage = $this->getNextPageURL();
        while ($nextPage) {
            $this->output->writeln("Fetching page <comment>$currentPage of $pageCount</comment>");
            $results = array_merge($results, $this->get($nextPage)['items']);
            $nextPage = $this->getNextPageURL();
            $currentPage++;
        }

        return $results;
    }

    protected function asArray($uri, $options = [], $method = 'get')
    {
        $this->lastResponse = $this->waitIfLimitExceeded(function () use ($method, $uri, $options) {
            $this->output->writeln("$method $uri");
            return $this->github->request($method, $uri, $options);
        });

        return json_decode($this->lastResponse->getBody(), true);
    }

    protected function waitIfLimitExceeded(callable $callback)
    {
        try {
            $result = $callback();
            return $result;
        } catch (ClientException $e) {
            $response = $e->getResponse();
            if ($e->getCode() != 403 || (int) $response->getHeader('X-RateLimit-Remaining')[0] !== 0)
                throw $e;

            $resetAt = (int) $response->getHeader('X-RateLimit-Reset')[0];
            $timeLeft = $resetAt - time();

            $this->output->writeln("<comment>Rate limit exceeded, waiting $timeLeft seconds for reset</comment>");
            sleep($timeLeft);

            return $this->waitIfLimitExceeded($callback);
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
            preg_match('%<.+page=(\d+)>; rel="last"%i', trim($header[0], ','), $matches);

            return (int) array_get($matches, 1, 1);
        }

        return 1;
    }
}
