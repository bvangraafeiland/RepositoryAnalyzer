<?php
namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 19-02-2016
 * Time: 23:03
 */
class AutoCollectCommand extends Command
{
    const LANGUAGES = ['java', 'javascript', 'ruby', 'python'];

    protected function configure()
    {
        $this->setName('collect:all')->setDescription('Retrieve repositories of all languages')
            ->addOption('stars', null, InputOption::VALUE_REQUIRED, 'Minimum number of stars', 200);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $application = $this->getApplication();
        $application->setAutoExit(false);

        $command = 'search:repositories';
        $stars = $input->getOption('stars');
        foreach (static::LANGUAGES as $language) {
            foreach (range(2008, 2016) as $year) {
                $application->run(new ArrayInput(compact('command', 'language', 'year') + ['--stars' => $stars]), $output);
            }
        }
    }
}
