<?php
namespace App\Runners;

use App\Parsers\XmlParser;
use Exception;

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
            return $this->getMavenResults($tool);
        }

        throw new Exception('No build tool defined!');
    }

    protected function getMavenResults($tool)
    {
        //exec("mvn $tool:check", $output, $exitCode);
        //
        //if ($exitCode !== 0) {
        //    throw new Exception("$this->buildTool analyzer exited with code $exitCode");
        //}

        $results = $tool == 'checkstyle' ? file_get_contents("target/checkstyle-result.xml") : file_get_contents("target/pmd.xml");
        $parser = new XmlParser($results);
        $results = [];
        foreach($parser->xpath('file') as $fileAttribute) {
            // TODO blabla
        };
    }
}
