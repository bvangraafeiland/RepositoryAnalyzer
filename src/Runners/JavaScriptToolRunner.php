<?php
namespace App\Runners;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 17-03-2016
 * Time: 19:39
 */
class JavaScriptToolRunner extends ToolRunner
{
    protected function runEslint()
    {
        $buildTool = $this->getBuildTool();

        exec("eslint src --format json", $output, $exitCode);

        dd($output);
    }

    protected function runJshint()
    {

    }

    protected function runJscs()
    {

    }

    protected function getBuildTool()
    {
        if ($default = parent::getBuildTool()) {
            return $default;
        }

        $readmeContents = @file_get_contents($this->projectDir . '/readme.md');

        if ($readmeContents && str_contains($readmeContents, 'grunt')) {
            return 'grunt';
        }

        return 'gulp';
    }

    protected function installDependenciesCommand()
    {
        return 'npm install';
    }

    /**
     * @param $tool
     *
     * @return mixed
     */
    public function numberOfWarnings($tool)
    {
        // TODO: Implement numberOfWarnings() method.
    }
}
