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

        exec("$buildTool eslint", $output, $exitCode);

        dd($output[0]);
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

        $readmeContents = file_get_contents($this->projectDir . '/readme.md');

        if ($readmeContents && str_contains($readmeContents, 'grunt')) {
            return 'grunt';
        }

        return 'gulp';
    }

    protected function installDependencies()
    {
        //TODO update virtualbox
        exec('npm install');
    }
}
