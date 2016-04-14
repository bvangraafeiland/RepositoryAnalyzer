<?php
namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 19-02-2016
 * Time: 23:03
 */
class BatchExecuteCommand extends Command
{
    protected static $allLanguages = ['java', 'javascript', 'ruby', 'python'];

    protected function configure()
    {
        $this->setName('batch')->setDescription('Batch execute another repository-related command')
            ->addArgument('task', InputArgument::REQUIRED, 'Command to run')
            ->addOption('languages', null, InputOption::VALUE_REQUIRED, 'Languages to include as comma-separated list (if omitted, all languages are included)')
            ->addOption('start', null, InputOption::VALUE_REQUIRED, 'Limit to repositories created in this year or later')
            ->addOption('end', null, InputOption::VALUE_REQUIRED, 'Limit to repositories created no later than this year')
            ->addOption('stars', null, InputOption::VALUE_REQUIRED, 'Minimum number of stars');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $application = $this->getApplication();
        $application->setAutoExit(false);

        $languagesProvided = $input->getOption('languages');
        $languages = $languagesProvided ? explode(',', $languagesProvided) : static::$allLanguages;

        $start = (int) $input->getOption('start') ?: 2008;
        $end = (int) $input->getOption('end') ?: 2016;

        $command = $input->getArgument('task');
        $stars = $input->getOption('stars');
        $starsOption = $stars ? ['--stars' => $stars] : [];
        foreach ($languages as $language) {
            foreach (range($start, $end) as $year) {
                $application->run(new ArrayInput(compact('command', 'language', 'year') + $starsOption), $output);
            }
        }
    }
}
