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

    }
    public function save()
    {
        $tmp = array();

        $tmp['datetime'] = $this->datetime;
        $tmp['server'] = $this->server;
        print_r($this->filter);
        if ((!empty($this->srv)))
        {
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

    public function post($url, $postVars = array())
    {
        //Transform our POST array into a URL-encoded query string.
        $postStr = http_build_query($postVars);
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
        $result = file_get_contents($url, false, $streamContext);
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


    public function isStrange($str)
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