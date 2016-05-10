<?php
namespace App\Export;

use App\Repository;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 07-05-2016
 * Time: 23:55
 */
class RepositoryDataExporter extends DataExporter
{
    protected function getFileHeaders()
    {
        return ['full_name', 'stargazers_count', 'language', 'uses_asats', 'uses_travis', 'pull_request_count', 'age', 'lifetime_density'];
    }

    protected function getFileName()
    {
        return 'repository_data';
    }

    protected function getItems()
    {
        return Repository::all()->map(function (Repository $repository) {
            return array_map(function ($field) use ($repository) {
                return $repository->getAttribute($field);
            }, $this->getFileHeaders());
        });
    }
}
