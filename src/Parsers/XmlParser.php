<?php
namespace App\Parsers;

use SimpleXMLElement;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 07-03-2016
 * Time: 17:42
 */
abstract class XmlParser
{
    /**
     * @var SimpleXMLElement
     */
    protected $root;

    public function __construct($xmlContent)
    {
        $xmlWithoutNamespace = preg_replace('%xmlns="[^"]+"%', '', $xmlContent);
        $this->root = simplexml_load_string($xmlWithoutNamespace);
    }
}
