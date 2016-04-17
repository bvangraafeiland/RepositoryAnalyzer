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
    protected function getResults($tool)
    {
        chdir($this->projectDir);
        $buildTools = $this->repository->buildTools;
        if ($buildTools->contains('name', 'maven')) {
            $location = $tool == 'checkstyle' ? 'target/checkstyle-result.xml' : 'target/pmd.xml';
            $buildToolCommand = "mvn $tool:check";
        }
        elseif ($buildTools->contains('name', 'gradle')) {
            $location = "build/reports/$tool/main.xml";
            $gradleCmd = file_exists('./gradlew') ? 'chmod 755 gradlew && ./gradlew' : 'gradle';
            $buildToolCommand = "$gradleCmd {$tool}Main";
        }

        if (!isset($location, $buildToolCommand)) {
            throw new Exception('Only projects that can be built using maven or gradle are supported.');
        }

        system("rm -f $location");
        exec($buildToolCommand);

        return $this->getWarnings($tool, $location);
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
}
