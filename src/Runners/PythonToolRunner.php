<?php
namespace App\Runners;

use Exception;

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
        chdir($this->projectDir);
        $dirName = $this->getModuleDirectory();
        exec("pylint $dirName --output-format=json", $output, $exitCode);
        $json = implode('', array_map('trim', $output));
        $results = json_decode($json, true);

        return array_map(function ($violation) {
            return [
                'file' => $violation['path'],
                'rule' => $violation['symbol'],
                'message' => trim($violation['message'])
            ] + array_only($violation, ['line', 'column']);
        }, $results);
    }

    protected function getModuleDirectory()
    {
        $shortProjectName = strtolower(basename($this->repository->full_name));
        foreach ([$shortProjectName, "src/$shortProjectName", "lib/$shortProjectName"] as $dir) {
            if (file_exists("$dir/__init__.py"))
                return $dir;
        }
        throw new Exception('Source directory could not be found');
    }
}
