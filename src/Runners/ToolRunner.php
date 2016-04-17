<?php
namespace App\Runners;

use App\Repository;
use InvalidArgumentException;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 15-03-2016
 * Time: 15:40
 */
abstract class ToolRunner
{
    /**
     * @var Repository
     */
    protected $repository;
    protected $projectDir;
    protected $buildTool;
    protected $countPerCategory;
    public $results;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
        $this->projectDir = absoluteRepositoriesDir() . '/' . $repository->full_name;
        $this->buildTool = $this->getBuildTool();
        $this->results = [];
        $this->countPerCategory = [];
    }

    public function run($tool)
    {
        $changedDir = @chdir($this->projectDir);
        if (!$changedDir) {
            throw new InvalidArgumentException("Project directory {$this->repository->full_name} does not exist, clone it first!");
        }

        $this->results[$tool] = $this->getGCDAugmentedResults($tool);
        $this->countPerCategory[$tool] = array_count_values(array_pluck($this->results[$tool], 'classification'));
    }

    public function numberOfWarnings($tool)
    {
        return count($this->results[$tool]);
    }

    public function numWarningsPerCategory($tool)
    {
        return $this->countPerCategory[$tool];
    }

    protected function getBuildTool()
    {
        $buildTools = $this->repository->buildTools;
        if ($buildTools->count() == 1) {
            return $buildTools->first()->name;
        }
        return null;
    }

    /**
     * @param array $output
     *
     * @return array
     */
    protected function jsonOutputToArray(array $output)
    {
        $results = [];
        foreach ($output as $lineNumber => $line) {
            $decodedLine = json_decode($line, true);
            if (is_array($decodedLine)) {
                $results = array_merge($results, $decodedLine);
            }
        }

        return $results;
    }

    abstract protected function getResults($tool);

    /**
     * @param $tool
     */
    protected function getGCDAugmentedResults($tool)
    {
        $mappings = require PROJECT_DIR . "/gdc_mappings/$tool.php";
        return array_map(function ($result) use ($mappings) {
            $result['file'] = $this->stripProjectDir($result['file']);
            return $result + ['classification' => $mappings[$result['rule']]];
        }, $this->getResults($tool));
    }

    /**
     * @param $fileName
     *
     * @return mixed
     */
    protected function stripProjectDir($fileName)
    {
        return str_replace($this->projectDir . DIRECTORY_SEPARATOR, '', $fileName);
    }

    // TODO
    // - Map output to GDC
    // - only run if at least 2 out of 3 asat properties true
}
