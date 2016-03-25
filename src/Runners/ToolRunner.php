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
    public $results;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
        $this->projectDir = absoluteRepositoriesDir() . '/' . $repository->full_name;
        $this->buildTool = $this->getBuildTool();
        $this->results = [];
    }

    public function run($tool)
    {
        $changedDir = @chdir($this->projectDir);
        if (!$changedDir) {
            throw new InvalidArgumentException("Project directory {$this->repository->full_name} does not exist, clone it first!");
        }
        $this->results[$tool] = $this->getAsatResults($tool);
    }

    abstract protected function getAsatResults($tool);

    abstract public function numberOfWarnings($tool);

    protected function getBuildTool()
    {
        $buildTools = $this->repository->buildTools;
        if ($buildTools->count() == 1) {
            return $buildTools->first()->name;
        }
        return null;
    }

    // TODO
    // - Map output to GDC
    // - only run if at least 2 out of 3 asat properties true
}
