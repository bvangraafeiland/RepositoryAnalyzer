<?php
namespace App\Runners;

use Exception;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 17-03-2016
 * Time: 15:26
 */
class RubyToolRunner extends ToolRunner
{
    protected function getResults($tool)
    {
        if (! $this->buildTool == 'rake') {
            throw new Exception('Only projects using rake are supported');
        }

        exec('ruby ' . PROJECT_DIR . "/ruby/run_tool.rb $this->projectDir", $output, $exitCode);

        if ($exitCode !== 0) {
            var_dump($output);
            throw new Exception("$this->buildTool analyzer exited with code $exitCode");
        }

        $results = [];
        foreach ($this->jsonOutputToArray($output)['files'] as $file) {
            $offenses = $file['offenses'];
            foreach ($offenses as $offense) {
                $offenseParts = explode('/', $offense['cop_name']);
                $rule = end($offenseParts);
                $results[] = [
                        'file' => $file['path'],
                        'rule' => $rule
                    ] + array_only($offense, ['message']) + array_only($offense['location'], ['line', 'column']);
            }
        }

        return $results;
    }
}
