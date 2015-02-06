<?php

namespace Craft;

require_once craft()->path->pluginsPath . 'redirectmanager/resources/parsers/CSVParser.php';
require_once craft()->path->pluginsPath . 'redirectmanager/resources/parsers/Parser.php';
require_once craft()->path->pluginsPath . 'redirectmanager/resources/interfaces/MyFactoryInterface.php';

class ParserFactory implements MyFactoryInterface
{
    public function __construct()
    {

    }
    public static function create($type)
    {
        switch($type)
        {
            case 'csv':
                $fileSeparator = ',';
                break;
            default:
                $fileSeparator = ',';
        }
        return new Parser($fileSeparator);
    }



}