<?php
namespace App\Commands;

use App\Repository;
use App\Warning;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\Builder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
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

        //$this->addArgument('repo', InputArgument::REQUIRED)->addArgument('hash', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repos = Repository::has('results')->whereNotIn('full_name', ['bumptech/glide', 'capitalone/Hygieia', 'checkstyle/checkstyle', 'FreeCodeCamp/FreeCodeCamp', 'google/auto', 'gulpjs/gulp', 'vuejs/vue', 'numenta/nupic', 'pyinstaller/pyinstaller', 'cython/cython', 'bower/bower'])->get();

        foreach ($repos as $repository) {
            $output->writeln("<comment>Analyzing $repository->full_name</comment>");
            chdir(absoluteRepositoriesDir() . '/' . $repository->full_name);
            $results = DB::table('results')->where('repository_id', $repository->id)->whereExists(function (Builder $query) {
                $query->select(DB::raw(1))->from('warnings')->whereRaw('warnings.result_id = results.id');
            })->get(['id', 'hash']);
            foreach ($results as $result) {
                exec("git checkout $result->hash");
                $start = microtime(true);
                $warnings = DB::table('warnings')->where('result_id', $result->id)->get(['id', 'file', 'line', 'code']);
                $warningsRetrieved = microtime(true);
                $output->writeln(count($warnings) . ' warnings retrieved: ' . ($warningsRetrieved - $start));
                $sed = 0;
                $update = 0;
                foreach ($warnings as $warning) {
                    unset($sedResult);
                    $startSearch = microtime(true);
                    exec("sed '{$warning->line}q;d' $warning->file", $sedResult);
                    if (empty($sedResult)) {
                        //var_dump($warning);
                        //throw new \Exception("Sed result empty!");
                        $lineOfCode = null;
                    }
                    else {
                        $lineOfCode = substr(mb_convert_encoding(trim($sedResult[0]), 'UTF-8', 'UTF-8'), 0, 255);
                    }
                    $startUpdate = microtime(true);
                    $sed += ($startUpdate - $startSearch);
                    if ($warning->code != $lineOfCode) {
                        DB::table('warnings')->where('id', $warning->id)->update(['code' => $lineOfCode]);
                    }
                    $endUpdate = microtime(true);
                    $update += ($endUpdate - $startUpdate);
                };
                $warningsUpdated = microtime(true);
                $output->writeln('warnings updated: ' . ($warningsUpdated - $warningsRetrieved));
                $output->writeln('sed: ' . $sed);
                $output->writeln('updating: ' . $update);
            }
            exec("git checkout $repository->default_branch");
        }
    }
}
