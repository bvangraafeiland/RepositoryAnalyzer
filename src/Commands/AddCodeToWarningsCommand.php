<?php
namespace App\Commands;

use App\Repository;
use App\Warning;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 13-06-2016
 * Time: 15:17
 */
class AddCodeToWarningsCommand extends Command
{
    protected function configure()
    {
        $this->setName('update:warnings:code')->setDescription('Add lines of code to warnings in the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repos = Repository::has('results')->with('results')->get();

        foreach ($repos as $repository) {
            chdir(absoluteRepositoriesDir() . '/' . $repository->full_name);
            foreach ($repository->results as $result) {
                exec("git checkout $result->hash");
                $result->warnings()->whereNull('code')->get(['id', 'file', 'line'])->each(function (Warning $warning) {
                    exec("sed -n {$warning->line}p $warning->file", $output);
                    $warning->code = substr(trim($output[0]), 0, 255);
                    $warning->save();
                });
            }
            exec("git checkout $repository->default_branch");
        }
    }
}
