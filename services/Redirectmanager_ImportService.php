<?php

//TODO: change event listeners in js to just grab form values
//TODO: move any data iterations to process, remove iterations and just have core functionality
namespace Craft;

require_once craft()->path->pluginsPath . 'redirectmanager/resources/factories/ParserFactory.php';
require_once craft()->path->pluginsPath . 'redirectmanager/resources/factories/MatchTypeFactory.php';
require_once craft()->path->pluginsPath . 'redirectmanager/resources/interfaces/ParserInterface.php';
require_once craft()->path->pluginsPath . 'redirectmanager/resources/interfaces/MatchTypeInterface.php';

class Redirectmanager_ImportService extends BaseApplicationComponent
{
    private $parser;
    private $matchType;
    private $redirectType;
    private $redirectTime;


    /**
     * import a csv file into the database.
     * @param string $fileContents , the contents of a csv
     * @param $parserType
     * @param $matchType
     * @param $redirectType
     * @param $redirectTime
     * @return bool $redirectsProcessed, true if successful, false otherwise.
     */
    public function import($fileContents, $parserType, $matchType, $redirectType, $redirectTime)
    {
        //initialize the class
        $this->initialize($parserType, $matchType, $redirectType, $redirectTime);

        //dos to unix
        $unixContents = $this->dos2unix($fileContents);

        //parse
        $data = $this->parse($unixContents);

        //remove base url
        $data = $this->removeBaseURL($data);

        //convert to type
        $data = $this->convertToMatchType($data);

        //process import
        $redirectsProcessed = $this->processImport($data);

        return $redirectsProcessed;

    }

    /**
     * initialize the attributes
     * @param string $parserType, the parser to use
     * @param string $matchType, the type of matching to use
     * @param string $redirectType, the type of redirect to use. Either 301 or 302.
     * @param string $redirectTime
     */
    public function initialize($parserType, $matchType, $redirectType, $redirectTime)
    {
        $this->parser = $this->buildParser($parserType);
        $this->matchType = $this->buildMatchType($matchType);
        $this->redirectType = $redirectType;
        $this->redirectTime = $redirectTime;
    }

    /**
     * process the file data and save to database
     * @param array $data, the array of the file contents
     * @return bool $redirectsProcessed, true if successful, false if there was some issue saving.
     */
    public function processImport($data)
    {
        $redirectsProcessed = false;
        foreach ($data as $key => $value)
        {
            $model = craft()->redirectmanager_redirect->newRedirect();
            $attributes = array();
            $attributes['uri'] = $value[0];
            $attributes['location'] = $value[1];
            $attributes['type'] = $this->redirectType;
            $attributes['redirectTime'] = $this->redirectTime;
            $model->setAttributes($attributes);

            if(!craft()->redirectmanager_redirect->saveRedirect($model)) {
                Craft::log('Could not save redirect: ' . $model);
                return $redirectsProcessed;
            }

        }
        $redirectsProcessed = true;
        return $redirectsProcessed;
    }

    /**
     * Convert the file contents from dos to unix.
     * @param $str $str, the contents of the csv file
     * @return string $str, the contents of the csv file converted to unix format.
     */
    public function dos2unix($str)
    {
        $str = str_replace("\r\n", "\n", $str);
        $str = str_replace("\r", "\n", $str);
        $str = preg_replace("/\n{2,}/", "\n\n", $str);
        return $str;
    }

    /**
     * remove the base url from each url in the file
     * @param array $data , the array of the file contents. assumes a 2D array
     * @return array
     */
    public function removeBaseURL(&$data)
    {
        //TODO: move this to a separete data structure
        $extensions = array('.com','.org', '.net', '.gov');
        foreach($data as &$entry)
        {
            foreach($entry as &$val)
            {
                foreach ($extensions as $ext)
                {
                    //find where the url extension is
                    if($pos = strpos($val, $ext))
                    {
                        //grab everything after the extension. If there is nothing else, set it to the homepage as '/'
                        if(!$val = substr($val, $pos + strlen($ext)))
                        {
                            $val = '/';
                        }
                    }
                }
                //remove the leading '/' on redirects to places other than the homepage.
                if($val{0} === '/' and strlen($val )> 1)
                {
                    $val =substr($val, 1);
                }
            }
        }
        return $data;
    }

    /**
     * convert the array to match type
     * @param array $data, the array of file contents
     * @return array, $data in the correct match type form.
     */
    public function convertToMatchType($data)
    {
        return $this->matchType->convert($data);
    }

    /**
     * parse the file contents with the parser
     * @param string $fileContents
     * @return array, filecontents in 2D array.
     */
    public function parse($fileContents)
    {
        return $this->parser->parse($fileContents);
    }

    /**
     * @param string $parserType
     * @return CSVParser|null
     */
    public function buildParser($parserType)
    {
        $parser = ParserFactory::create($parserType);
        return $parser;
    }

    /**
     * @param $matchTypeString
     * @return RegexMatch|StringMatch|null
     */
    public function buildMatchType($matchTypeString)
    {
        $match = MatchTypeFactory::create($matchTypeString);
        return $match;
    }

    /**
     * get parser
     * @return ParserInterface $parser
     */
    public function getParser(){
        return $this->parser;
    }

    /**
     * get matchType
     * @return MatchTypeInterface $matchType
     */
    public function getMatchType(){
        return $this->matchType;
    }

    /**
     * get redirect type
     * @return string $redirectType
     */
    public function getRedirectType(){
        return $this->redirectType;
    }

    /**
     * @return mixed
     */
    public function getRedirectTime(){
        return $this->redirectTime;
    }

    public function test($contents)
    {

        $parser = $this->buildParser('csv');
        return $parser->getSeparatorChar();
        $val = $parser->getEolChar();
        return $val;
        return $data;

        $model = new Redirectmanager_ImportModel();
        $model->setAttribute('uri', $data[0][0]);
        $model->setAttribute('location', $data[0][1]);
        return $model;
    }






}