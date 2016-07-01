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
 * Time: 11:35
 */
class AverageTimesPerProjectCommand extends Command
{
    protected function configure()
    {
        $this->setName('export:projectaverages');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $resultsDir = PROJECT_DIR . '/results/solve_times';
        $categories = WarningClassification::pluck('name', 'id');

        $projectData = collect(scandir($resultsDir))->filter(function ($fileName) {
            return ends_with($fileName, '.json');
        })->flatMap(function ($fileName) use ($resultsDir) {

            $categoryData = collect(json_decode(file_get_contents("$resultsDir/$fileName"), true))->map(function ($categoryData) {
                return array_pluck($categoryData, 'count');
            })->all();
            $fullName = str_replace(['-', '.json'], ['/', ''], $fileName);
            return [$fullName => $categoryData];
        })->filter(function ($categoryData, $projectName) {
            return count($categoryData) > 3 && !str_contains($projectName, 'nupic');
        });

        $results = [];

        foreach ($categories as $categoryId => $category) {
            foreach ($projectData as $projectName => $averages) {
                if (array_has($averages, $categoryId)) {
                    $sampleSize = count($averages[$categoryId]);
                    $results[$category][$projectName] = array_median($averages[$categoryId]) . " ($sampleSize)";
                }
            }
        }

        $projects = $projectData->keys();
        $categoryNames = array_keys($results);
        // Header with project names
        $output->writeln(',' . $projects->map('basename')->implode(','));
        foreach ($categoryNames as $categoryName) {
            $output->write("$categoryName,");
            $categoryMedians = $projects->map(function ($projectName) use ($results, $categoryName) {
                return array_get(array_get($results, $categoryName), $projectName);
            })->implode(',');
            $output->writeln($categoryMedians);
        }

        $projectTotals = $projectData->map(function ($solveTimes) {
            return array_flatten($solveTimes);
        });
        $projectAverages = $projectTotals->map(function ($solveTimes) {
            return !empty($solveTimes) ? round(collect($solveTimes)->average(), 2) : '';
        })->implode(',');
        $projectMedians = $projectTotals->map(function ($solveTimes) {
            return !empty($solveTimes) ? array_median($solveTimes) : '';
        })->implode(',');
        $output->writeln("Total Average,$projectAverages");
        $output->writeln("Total Median,$projectMedians");
    }
}
