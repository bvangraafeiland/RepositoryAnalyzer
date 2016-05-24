<?php
namespace App\Runners;

use App\Parsers\XmlParser;
use Exception;
use SimpleXMLElement;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 17-03-2016
 * Time: 19:39
 */
class JavaToolrunner extends ToolRunner
{
    protected $outputLocation = 'asat-result.xml';

    protected function getResults($tool)
    {
        $buildToolCommand = $this->{'get' . ucfirst($tool) . 'Command'}();

        system("rm -f $this->outputLocation");
        $this->fixConfigErrors($tool);
        exec($buildToolCommand, $output);
        $this->revertGitChanges($tool);

        return $this->getWarnings($tool, $this->outputLocation);
    }

    protected function getCheckstyleCommand()
    {
        $projectConfig = $this->getProjectConfig('checkstyle');
        $version = array_get($projectConfig, 'asat-version', '6.15');
        $properties = array_get($projectConfig, 'properties', []);
        $commandProperties = '';
        foreach ($properties as $property => $value) {
            $commandProperties .= "-D$property=$value ";
        }

        $existingSrcDirs = array_filter((array)$projectConfig['src'], function ($dir) {
            return file_exists($dir);
        });
        $src = implode(' ', array_map(function ($dir) use ($version) {
            $prefix = $version <= '6.1.1' ? '-r ' : '';
            return "$prefix$dir/src/main/java";
        }, $existingSrcDirs));
        $configLocation = $this->getConfigLocation('checkstyle');
        return "java -Dcheckstyle.cache.file=checkstyle-cache $commandProperties -jar ~/checkstyle-$version-all.jar -c $configLocation $src -f xml -o $this->outputLocation";
    }

    protected function getPmdCommand()
    {
        $projectConfig = $this->getProjectConfig('pmd');
        $version = array_get($projectConfig, 'asat-version', '5.4.1');
        $src = implode(',', (array) $projectConfig['src']);

        $configLocation = array_get($projectConfig, 'config-location', 'pmd.xml');

        return "~/pmd-bin-$version/bin/run.sh pmd -d $src -R $configLocation -f xml -r $this->outputLocation";
    }

    protected function fixConfigErrors($tool)
    {
        if ($tool == 'pmd') {
            $pmdConfig = $this->getConfigLocation('pmd');
            $config = file_get_contents($pmdConfig);
            file_put_contents($pmdConfig, str_replace('<exclude name="ShortMethod"/>', '', $config));
        }
    }

    protected function revertGitChanges($tool)
    {
        exec('git checkout ' . $this->getConfigLocation($tool));
    }

    protected function getConfigLocation($tool)
    {
        return array_get($this->getProjectConfig($tool), 'config-location', "$tool.xml");
    }

    protected function getWarnings($tool, $resultsFileLocation)
    {
        $xmlContent = file_get_contents($resultsFileLocation);

        if (!$xmlContent) {
            throw new Exception("$tool results file could not be read.");
        }

        $parser = new XmlParser($xmlContent);

        $results = [];
        foreach ($parser->xpath('file') as $fileElement) {
            $this->{'get' . ucfirst($tool) . 'Results'}($fileElement, $fileElement['name'], $results);
        };

        return $results;
    }

    protected function getCheckstyleResults(SimpleXMLElement $fileElement, $file, &$results)
    {
        $warnings = $fileElement->xpath('error');
        foreach ($warnings as $warning) {
            $attributes = current($warning);
            $rule = $this->shortCheckstyleRule($attributes['source']);
            $results[] = compact('file', 'rule')
                + array_only($attributes, ['line', 'column', 'message']);
        }
    }

    protected function shortCheckstyleRule($fullRule)
    {
        return str_replace('Check', '', substr($fullRule, strrpos($fullRule, '.') + 1));
    }

    protected function getPmdResults(SimpleXMLElement $fileElement, $file, &$results) {
        $warnings = $fileElement->xpath('violation');
        foreach ($warnings as $warning) {
            $message = trim($warning);
            $attributes = current($warning);
            $rule = $attributes['rule'];
            $line = $attributes['beginline'];
            $column = $attributes['begincolumn'];
            $results[] = compact('file', 'message', 'rule', 'line', 'column');
        }
    }

    protected function hasConfigFile($tool)
    {
        return file_exists($this->getConfigLocation($tool));
    }
}
