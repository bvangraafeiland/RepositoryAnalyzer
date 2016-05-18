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
    protected $additionalSources = [
        'SirVer/ultisnips' => ['plugin/*.py']
    ];

    protected function getResults($tool)
    {
        chdir($this->projectDir);
        $dirNames = $this->getSources()->implode(' ');

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
     * @return Collection
     * @throws Exception
     */
    protected function getSources()
    {
        $additional = array_get($this->additionalSources, $this->repository->full_name, []);
        return $this->getModuleDirectories()->merge($additional);
    }

    protected function customLocations()
    {
        $shortProjectName = strtolower(basename($this->repository->full_name));
        return ["src/$shortProjectName", 'pythonx/UltiSnips', 'plugin/UltiSnips', 'plugin/PySnipEmu'];
    }

    /**
     * @return Collection
     * @throws Exception
     */
    protected function getModuleDirectories()
    {
        $result = collect(scandir('.'))->merge($this->customLocations())->filter(function ($filename) {
            return !str_contains($filename, ['.', 'test']) && is_dir($filename) && file_exists("$filename/__init__.py");
        });

        if (empty($result)) {
            throw new Exception('Source directory could not be found');
        }
        return $result;
    }

    /**
     * @return string
     */
    protected function getConfigFile()
    {
        $configFile = file_exists('pylintrc') ? 'pylintrc' : '.pylintrc';

        return $configFile;
    }
}
