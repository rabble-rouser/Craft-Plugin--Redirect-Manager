<?php

namespace Craft;

require_once craft()->path->pluginsPath . 'redirectmanager/resources/interfaces/ParserInterface.php';

class CSVParser implements ParserInterface
{

    private $separatorChar;
    private $eolChar;

    public function __construct()
    {
        $this->separatorChar = ',';
        $this->eolChar = PHP_EOL;
    }

    public function getEolChar()
    {
        return $this->eolChar;
    }

    public function getSeparatorChar()
    {
        return $this->separatorChar;
    }

    // @author: Klemen Nagode
    /**
    * convert the csv contents str to a 2D array,
    * @param str $fileContents, the contents of the csv file as a string
    * @return array $data, the contents of the file as a 2D array with the structure $data[uri][location].
    */
    public function parse($contents)
    {
        $data = array();
        $size = strlen($contents);
        $columnIndex = 0;
        $rowIndex = 0;
        $fieldValue="";
        for($i=0; $i<$size;$i++) {

            $char = $contents{$i};
            $addChar = "";

            if($char==$this->separatorChar) {
                $data[$rowIndex][$columnIndex] = $fieldValue;
                $fieldValue = "";

                $columnIndex = $columnIndex + 1;
            }
            elseif($char==$this->eolChar)
            {
                echo $char;
                $data[$rowIndex][$columnIndex] = $fieldValue;
                $fieldValue = "";
                $columnIndex = 0;
                $rowIndex = $rowIndex + 1;
            }
            else
            {
                $addChar=$char;
            }

            if($addChar!="")
            {
                $fieldValue.=$addChar;

            }
        }

        if($fieldValue)
        { // save last field
            $data[$rowIndex][$columnIndex] = $fieldValue;
        }
        return $data;
    }
}