<?php
namespace App\Runners;

use App\Repository;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 15-03-2016
 * Time: 15:40
 */
class ToolRunner
{
    /**
     * @var Repository
     */
    protected $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }


}
