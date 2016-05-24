<?php
namespace App\Runners;

use App\Exceptions\RepositoryStateException;
use App\Repository;
use Exception;
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
    protected $countPerCategory;
    protected $projectConfigs;
    public $results;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
        $this->projectDir = absoluteRepositoriesDir() . '/' . $repository->full_name;
        $this->resetData();
        $this->checkProjectDir();

        $this->projectConfigs = require PROJECT_DIR . "/config/projects.php";
    }

    public function run($tool)
    {
        chdir($this->projectDir);
        if (! $this->hasConfigFile($tool)) {
            throw new RepositoryStateException('No config file found');
        }

        $this->results[$tool] = $this->getGCDAugmentedResults($tool);
        $this->countPerCategory[$tool] = array_count_values(array_filter(array_pluck($this->results[$tool], 'classification')));
    }

    protected abstract function hasConfigFile($tool);

    public function resetData()
    {
        $this->results = [];
        $this->countPerCategory = [];
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
     * @return Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param array $output
     *
     * @return array
     * @throws Exception
     */
    protected function jsonOutputToArray(array $output)
    {
        $results = [];
        $foundResults = false;
        foreach ($output as $lineNumber => $line) {
            $decodedLine = json_decode($line, true);
            if (is_array($decodedLine)) {
                $results = array_merge($results, $decodedLine);
                $foundResults = true;
            }
            else {
                echo $line . PHP_EOL;
            }
        }

        if (!$foundResults) {
            throw new Exception('No results found in output');
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
            $result['column'] = array_get($result, 'column', null);
            return $result + ['classification' => array_get($mappings, $result['rule'])];
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

    public function checkProjectDir()
    {
        $changedDir = @chdir($this->projectDir);
        if (!$changedDir) {
            throw new InvalidArgumentException("Project directory {$this->repository->full_name} does not exist, clone it first!");
        }
    }

    /**
     * @param $asatName
     *
     * @return mixed
     * @throws Exception
     */
    protected function getProjectConfig($asatName)
    {
        $asatConfigs = array_get($this->projectConfigs, $asatName);

        if (!isset($asatConfigs[strtolower($this->repository->full_name)])) {
            throw new Exception('Could not retrieve project configuration for running tool');
        }

        return $asatConfigs[strtolower($this->repository->full_name)];
    }
}
