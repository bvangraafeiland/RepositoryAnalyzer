<?php
namespace App\Runners;

use Exception;
use Illuminate\Support\Collection;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 17-03-2016
 * Time: 19:39
 */
class PythonToolRunner extends ToolRunner
{
    protected function getResults($tool)
    {
        $dirNames = implode(' ', array_get($this->getProjectConfig('pylint'), 'src', []));

        if (empty($dirNames)) {
            throw new Exception('No Python source directories could be determined');
        }

        $this->fixConfigErrors();

        $rcFileOption = file_exists('.pylintrc') ? '--rcfile .pylintrc' : '';
        exec("pylint -j 3 $dirNames --output-format=json $rcFileOption", $output, $exitCode);
        $this->revertConfigChanges();
        
        $json = implode('', array_map('trim', $output));
        $results = json_decode($json, true);

        if (is_null($results)) {
            throw new Exception('Invalid results, likely an error with the tool');
        }

        return array_map(function ($violation) {
            return [
                'file' => $violation['path'],
                'rule' => $violation['symbol'],
                'message' => trim($violation['message'])
            ] + array_only($violation, ['line', 'column']);
        }, $results);
    }

    protected function fixConfigErrors()
    {
        $configFile = $this->getConfigFile();
        $config = file_get_contents($configFile);
        file_put_contents($configFile, preg_replace('/(\n\s*)-/', '$1', $config));
    }

    protected function revertConfigChanges()
    {
        exec('git checkout ' . $this->getConfigFile());
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function getConfigFile()
    {
        $configFile = file_exists('pylintrc') ? 'pylintrc' : ( file_exists('.pylintrc') ? '.pylintrc' : null);

        if (!$configFile)
            throw new Exception('Pylint config file not found');

        return $configFile;
    }

    protected function hasConfigFile($tool)
    {
        return (bool) $this->getConfigFile();
    }
}
