<?php
namespace RepoFinder;

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
        return $this->asArray('/search/repositories', [
            'query' => ['q' => $query, 'per_page' => 100]
        ]);
    }

    public function getAllPages(callable $resultHandler)
    {
        $nextLink = $this->getNextLink();
        while ($nextLink) {
            $nextResult = $this->get($nextLink);
            $resultHandler($nextResult);

            $nextLink = $this->getNextLink();
        }
    }

    protected function asArray($uri, $options = [], $method = 'get')
    {
        $this->lastResponse = $this->waitIfLimitExceeded(function () use ($method, $uri, $options) {
            $this->output->writeln('Requesting ' . $uri);
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
    protected function getNextLink()
    {
        $header = $this->lastResponse->getHeader('Link');
        if ($header) {
            preg_match('%<(.+)>; rel="next",%i', trim($header[0], ','), $matches);

            return array_get($matches, 1);
        }
        return null;
    }
}
