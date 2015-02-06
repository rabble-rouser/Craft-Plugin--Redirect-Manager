<?php

namespace Craft;

require_once craft()->path->pluginsPath . 'redirectmanager/resources/matches/RegexMatch.php';
require_once craft()->path->pluginsPath . 'redirectmanager/resources/matches/StringMatch.php';
require_once craft()->path->pluginsPath . 'redirectmanager/resources/interfaces/MyFactoryInterface.php';

class MatchTypeFactory implements MyFactoryInterface
{
    public static function create($type)
    {
        switch($type)
        {
            case 'regex':
                return new RegexMatch();
                break;
            case 'string':
                return new StringMatch();
                break;
            default:
                return null;
        }
    }
}