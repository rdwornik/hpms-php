<?php
require "../../../private/settings.php";

class Log
{
    private $argByPost; //By POST
    private $argByGet; //By GET
    private $datetime; //connection time
    private $srv; //$_SERVER HTTP
    private $server; //user server name
    private $filter; //filter
    private $strange; //strange
    private $strangeStr; //strange info string
    private $url;

    public function __construct()
    {
       global $config;
        $this->srv = $_SERVER;
        $this->argByPost = $_POST;
        $this->argByGet = $_GET;
        $this->datetime = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $this->server = $config['serverName'];
        $this->filter = $config['filter'];
        $this->strange = $config['strange'];
        $this->strangeStr = $config['strangeStr'];
        $this->url = $config['url'];

    }
    private function getLog()
    {
        $tmp = array();
        print_r($this->srv['REQUEST_METHOD']);
        print_r($this->argByGet);
        $tmp['datetime'] = $this->datetime;
        $tmp['server'] = $this->server;
        if ((!empty($this->srv)))
        {
           echo "loop1"; 
            foreach ($this->srv as $key => $value)
            {
                if (!in_array($key,(array)$this->filter))
                {
                    if ($this->isStrange($value))
                    {
                        $value = $this->strangeStr;
                    }
                    $tmp[$key] = $value;
                }

            }
        }

        if (($this->srv['REQUEST_METHOD'] === 'GET') && !empty($this->argByGet)) 
        {
            echo "loop2"; 

            foreach ($this->argByGet as $key => $value)
            {
                if (!in_array($key, (array)$this->filter))
                {
                    $key = "[GET] " . $key;
                    if ($this->isStrange($value))
                    {
                        $value = $this->strangeStr;
                    }
                    $tmp[$key] = $value;
                }
            }
        }

        if (($this->srv['REQUEST_METHOD'] === 'POST') && !empty($this->argByPost))
        {
            echo "loop3"; 

            foreach ($this->argByPost as $key => $value)
            {
                if (!in_array($key, (array)$this->filter))
                {
                    $key = "[POST] " . $key;
                    if ($this->isStrange($value))
                    {
                        $value = $this->strangeStr;
                    }
                    $tmp[$key] = $value;
                }
            }
        }

        $key = "[POST] [RAW POST]";
        $value = file_get_contents("php://input");
        if (strlen($value) > 0) 
        {
            if ($this->isStrange($value))
            {
                $value =$this->strangeStr;
            }
            $tmp = array($key => $value);
        }
        return $tmp;
    }

    public function post()
    {
        //Transform our POST array into a URL-encoded query string.
        $postStr = http_build_query($this->getLog());
        //Create an $options array that can be passed into stream_context_create.
        $options = array(
            'http' =>
                array(
                    'method'  => 'POST', //We are using the POST HTTP method.
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $postStr //Our URL-encoded query string.
                )
        );
        //Pass our $options array into stream_context_create.
        //This will return a stream context resource.
        $streamContext  = stream_context_create($options);
        //Use PHP's file_get_contents function to carry out the request.
        //We pass the $streamContext variable in as a third parameter.
        $result = file_get_contents( $this->url, false, $streamContext);
        //If $result is FALSE, then the request has failed.
        if($result === false)
        {
            //If the request failed, throw an Exception containing
            //the error.
            $error = error_get_last();
            throw new Exception('POST request failed: ' . $error['message']);
        }
        //If everything went OK, return the response.
        return $result;
    }


    private function isStrange($str)
    {
        foreach (str_split($str) as $c)
        {
            $h = sprintf("%02x", ord($c));
            if (in_array($h, (array)$this->strange))
            {
                return true;
            }
        }
        return false;
    }

}