<?php
namespace App\Runners;

use Exception;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 17-03-2016
 * Time: 19:39
 */
class JavaScriptToolRunner extends ToolRunner
{
    protected $dependenciesInstalled;

    protected function runEslint()
    {
        return $this->getAsatResults($this->buildTool, 'eslint');
    }

    protected function runJshint()
    {
        return $this->getAsatResults($this->buildTool, 'jshint');
    }

    protected function runJscs()
    {
        return $this->getAsatResults($this->buildTool, 'jscs');
    }

    protected function getAsatResults($buildTool, $asatName)
    {
        exec('node ' . PROJECT_DIR . "/javascript/run_tool.js $buildTool $asatName $this->projectDir", $output, $exitCode);

        if ($exitCode !== 0) {
            var_dump($output);
            throw new Exception("$buildTool analyzer exited with code $exitCode");
        }

        $results = [];
        foreach ($output as $lineNumber => $line) {
            $decodedLine = json_decode($line, true);
            if (is_array($decodedLine))
                $results = array_merge($results, $decodedLine);
        }

        return $results;
    }

    protected function getBuildTool()
    {
        // temporarily force example project to use gulp
        // return 'gulp';
        if ($default = parent::getBuildTool()) {
            return $default;
        }

        $readmeContents = @file_get_contents($this->projectDir . '/readme.md');

        if (str_contains($readmeContents, 'grunt')) {
            return 'grunt';
        }

        if (str_contains($readmeContents, 'gulp')) {
            return 'gulp';
        }

        return $this->repository->buildTools->first()->name;
    }

    /**
     * @param $tool
     *
     * @return mixed
     */
    public function numberOfWarnings($tool)
    {
        return count($this->results[$tool]);
    }
}
