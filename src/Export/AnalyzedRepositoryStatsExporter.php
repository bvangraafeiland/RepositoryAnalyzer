<?php
namespace App\Export;

use App\Repository;
use Exception;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 10-06-2016
 * Time: 11:54
 */
class AnalyzedRepositoryStatsExporter extends DataExporter
{
    protected function getFileHeaders()
    {
        return ['full_name', 'asat_in_travis', 'avg_warning_count', 'loc', 'warnings_per_100_loc'];
    }

    protected function getItems()
    {
        $asatConfigs = require PROJECT_DIR . '/config/projects.php';
        $repositoryConfigs = collect($asatConfigs)->collapse()->all();
        $repositoryLanguages = Repository::whereIn('full_name', array_keys($repositoryConfigs))->pluck('language', 'full_name');

        $this->countLinesOfCode($repositoryConfigs);

        $averageWarningCounts = DB::table(DB::raw('(SELECT full_name, asat_in_travis, count(warnings.id) as warnings_count FROM repositories JOIN results ON repositories.id = results.repository_id LEFT JOIN warnings ON results.id = warnings.result_id GROUP BY results.id) AS warning_counts'))->groupBy('full_name', 'asat_in_travis')->orderBy('avg_warning_count', 'desc')->get(['full_name', 'asat_in_travis', DB::raw('ROUND(AVG(warnings_count), 2) as avg_warning_count')]);

        return collect($averageWarningCounts)->map(function ($result) use ($repositoryLanguages) {
            $language = $repositoryLanguages[$result->full_name];
            $loc = $this->getLoc($result->full_name, $language);
            $warnings_per_100_loc = round(($result->avg_warning_count / $loc) * 100, 2);
            return (array) $result + compact('loc', 'warnings_per_100_loc');
        })->sortByDesc('warnings_per_100_loc');
    }

    protected function getLoc($repositoryName, $language)
    {
        $xml = simplexml_load_file(absoluteRepositoriesDir() . "/$repositoryName/cloc.xml");
        if (!$xml) {
            throw new Exception("Cloc XML for $repositoryName could not be read.");
        }
        if ($language == 'JavaScript') {
            $language = 'Javascript';
        }

        return (int) array_get($xml->xpath("//languages/language[@name = '$language']/@code"), 0);
    }

    protected function getFileName()
    {
        return 'average_warning_counts';
    }

    protected function countLinesOfCode($repositoryConfigs)
    {
        foreach ($repositoryConfigs as $repositoryName => $config) {
            $sourceDirs = implode(' ', (array) array_get($config, 'src', 'lib'));
            $repositoryName = Repository::where('full_name', $repositoryName)->first(['full_name'])->full_name;
            chdir(absoluteRepositoriesDir() . '/' . $repositoryName);

            if (!file_exists('cloc.xml')) {
                exec("cloc $sourceDirs --xml --quiet --out=cloc.xml", $output, $exitCode);

                if ($exitCode != 0) {
                    unlink('cloc.xml');
                    throw new Exception("Counting code for $repositoryName failed!");
                }
            }
        }
    }
}
