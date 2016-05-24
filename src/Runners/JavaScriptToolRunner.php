<?php
namespace App\Runners;

use App\Checkers\JavaScriptChecker;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 17-03-2016
 * Time: 19:39
 */
class JavaScriptToolRunner extends ToolRunner
{
    protected $formatters = [
        'eslint' =>  PROJECT_DIR . '/javascript/eslint_formatter.js',
        'jshint' =>  PROJECT_DIR . '/javascript/jshint_reporter.js',
        'jscs' =>  PROJECT_DIR . '/javascript/jscs_reporter.js'
    ];

    protected function getResults($asatName)
    {
        $ignore = (array) array_get($this->getProjectConfig($asatName), 'ignore', []);
        $ignorePattern = implode(' ', array_map(function ($str) use ($asatName) {
            return ($asatName == 'eslint') ? "--ignore-pattern $str" : "--exclude=$str";
        }, $ignore));

        $formatFlag = ($asatName == 'eslint') ? '--format ' : '--reporter=';
        $formatter = $this->formatters[$asatName];

        $src = implode(' ', (array) array_get($this->getProjectConfig($asatName), 'src'));

        $localExecutable = "node_modules/.bin/$asatName";
        $version = array_get($this->getProjectConfig($asatName), 'asat-version');

        if ($version && !file_exists($localExecutable)) {
            $additionalInstalls = array_get($this->getProjectConfig($asatName), 'npm', '');
            system("npm install $asatName@$version $additionalInstalls");
        }

        $executable = file_exists($localExecutable) ? $localExecutable : $asatName;
        $additionalFlags = $asatName == 'jscs' ? '--max-errors 10000' : '';
        $cmd = "$executable $formatFlag$formatter $src $ignorePattern $additionalFlags";
        exec($cmd, $output, $exitCode);

        return array_filter($this->jsonOutputToArray($output), function ($warning) {
            return ! str_contains($warning['message'], 'was removed and replaced') && !is_null($warning['rule']);
        });
    }

    protected function hasConfigFile($tool)
    {
        return (bool) array_first(JavaScriptChecker::$configFileNames[$tool], function ($key, $fileName) {
            return file_exists($fileName);
        });
    }
}
