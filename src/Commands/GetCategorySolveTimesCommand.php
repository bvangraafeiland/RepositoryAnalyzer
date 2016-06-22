<?php
namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 22-06-2016
 * Time: 08:40
 */
class GetCategorySolveTimesCommand extends Command
{
    protected function configure()
    {
        $this->setName('get:solvetimes')->addArgument('categoryId', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        collect(scandir(PROJECT_DIR . '/results/solve_times'))->filter(function ($file) {
            return ends_with($file, '.json');
        })->map(function ($file) use ($input) {
            $solveTimes = array_get(json_decode(file_get_contents(PROJECT_DIR . "/results/solve_times/$file"), true), $input->getArgument('categoryId'), []);

            return ['repo' => str_replace('.json', '', $file), 'count' => count($solveTimes), 'average' => collect($solveTimes)->average('count')];
        })->sortByDesc('average')->filter(function ($item) {
            return $item['average'];
        })->each(function ($item) use ($output) {
            $output->writeln($item['repo'] . ' (' . $item['count'] . '): ' . $item['average']);
        });
    }
}
