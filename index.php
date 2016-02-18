<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/helpers.php';

use Carbon\Carbon;

$github = new Github\Client(new Github\HttpClient\CachedHttpClient(array('cache_dir' => __DIR__ . '/tmp/github-api-cache')));
$github->authenticate(getenv('GITHUB_TOKEN'), null, Github\Client::AUTH_HTTP_TOKEN);

$projects = json_decode(file_get_contents('pylint.json'), true);

var_dump($github->api('rate_limit')->getRateLimits());

$candidates = [];
foreach ($projects as $project) {
    $parts = explode('/', trim($project['repository'], '/'));
    $user = $parts[0];
    $repository = $parts[1];
    
    $repoInfo = $github->api('repo')->show($user, $repository);

    if ($repoInfo['stargazers_count'] > 200)
        $candidates[] = sub_array($repoInfo, ['name', 'html_url', 'stargazers_count', 'pushed_at']);
    echo $repoInfo['stargazers_count'] . PHP_EOL;
}

file_put_contents('pylint_filtered.json', json_encode($candidates, JSON_PRETTY_PRINT));
