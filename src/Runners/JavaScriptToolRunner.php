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

        if ($buildTool == 'grunt') {

        }
        elseif ($buildTool == 'gulp') {

        }
        else {
            $target = '.';
        }

        exec("eslint $target --format json", $output, $exitCode);

        return json_decode($output[0], true);
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

        if (str_contains($readmeContents, 'grunt')) {
            return 'grunt';
        }

        if (str_contains($readmeContents, 'gulp')) {
            return 'gulp';
        }

        return null;
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
