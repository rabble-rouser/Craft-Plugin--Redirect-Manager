<?php

namespace Craft;

require_once craft()->path->pluginsPath . 'redirectmanager/resources/interfaces/MatchTypeInterface.php';

class StringMatch implements MatchTypeInterface
{
    /**
     * @param $data
     * @return mixed
     */
    public function convert($data)
    {
        return $data;
    }
}