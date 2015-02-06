<?php

namespace Craft;

require_once craft()->path->pluginsPath . 'redirectmanager/resources/interfaces/MatchTypeInterface.php';

class RegexMatch implements MatchTypeInterface
{

    private $startDelim;
    private $endDelim;

    /**
     * default constructor
     */
    public function __construct()
    {
        $this->startDelim = '#^';
        $this->endDelim = '$#';
    }

    /**
     * @param $startDelim
     * @param $endDelim
     * @return RegexMatch $instance
     */
    public static function create($startDelim, $endDelim)
    {
        $instance = new self();
        $instance->setstartDelim($startDelim);
        $instance->setEndDelim($endDelim);
        return $instance;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function convert($data)
    {
        $index = 0;
        foreach ($data as &$value)
        {

            $string = $value[$index];
            $specialChars = "#$%^&*()+-[]';,./{}|<>?~";

            //check for any special characters and escape them
            $newstr = '';
            for($i=0; $i<strlen($string); $i++)
            {
                $char = $string{$i};
                if($charfound = strpbrk($char, $specialChars))
                {
                    //prepend '\' to escape
                    $char = "\\" . $char;
                }
                $newstr .= $char;
            }
            //append the delimiters
            $value[$index] = $this->startDelim . $newstr . $this->endDelim;

        }

        return $data;
    }

    /**
     * @return string
     */
    public function getStartDelim()
    {
        return $this->startDelim;
    }

    /**
     * @return string
     */
    public function getEndDelim()
    {
        return $this->endDelim;
    }

    /**
     * @param $endDelim
     */
    private function setEndDelim($endDelim)
    {
        $this->endDelim = $endDelim;
    }

    /**
     * @param $startDelim
     */
    private function setStartDelim($startDelim)
    {
        $this->startDelim = $startDelim;
    }
}