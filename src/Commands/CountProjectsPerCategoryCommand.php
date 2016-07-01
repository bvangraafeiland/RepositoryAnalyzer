<?php
namespace App\Commands;

use App\WarningClassification;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 26-06-2016
 * Time: 01:01
 */
class CountProjectsPerCategoryCommand extends Command
{
    protected function configure()
    {
        $this->setName('count:projectspercategory');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $categories = WarningClassification::pluck('name', 'id');
        $resultsDir = PROJECT_DIR . '/results/solve_times';
        $base = collect(scandir($resultsDir))->filter(function ($dir) {
            return ends_with($dir, '.json') && !str_contains($dir, 'nupic');
        })->map(function ($filename) use ($resultsDir) {
            return json_decode(file_get_contents("$resultsDir/$filename"), true);
        });
        $projectCounts = $base->reduce(function ($carry, $categories) {
            foreach (array_keys($categories) as $category) {
                $carry[$category] = array_get($carry, $category, 0) + 1;
            }
            return $carry;
        }, []);
        $projectPercentages = $base->map(function ($categories) {
            $result = [];
            foreach ($categories as $category => $data) {
                $result[$category] = count($data);
            }
            return $result;
        });

        $percentages = [];
        foreach ($categories as $id => $name) {
            $counts = $projectPercentages->pluck($id)->filter();
            if (!$counts->isEmpty())
                $percentages[$id] = round($counts->max() / $counts->sum(), 2) * 100;
        }

        //foreach ($percentages as $category => $percentage) {
        //    $output->writeln("$category,$percentage");
        //}

        foreach ($categories as $id => $name) {
            if (array_has($projectCounts, $id))
                $output->writeln("$name," . $projectCounts[$id] . ',' . $percentages[$id] . '%');
        }

        //foreach ($projectCounts as $categoryId => $count) {
        //    $output->writeln($categories[$categoryId] . ",$count");
        //}
    }
}
