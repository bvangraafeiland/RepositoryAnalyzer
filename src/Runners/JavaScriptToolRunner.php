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
    protected function getResults($asatName)
    {
        if (! in_array($this->buildTool, ['grunt', 'gulp'])) {
            throw new Exception('Only projects using grunt or gulp are supported');
        }

        exec('node ' . PROJECT_DIR . "/javascript/run_tool.js $this->buildTool $asatName $this->projectDir", $output, $exitCode);

        if ($exitCode !== 0) {
            var_dump($output);
            throw new Exception("$this->buildTool analyzer exited with code $exitCode");
        }

        return $this->jsonOutputToArray($output);
    }

    protected function getBuildTool()
    {
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
}
