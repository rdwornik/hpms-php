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
    private $headers;

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
        $this->headers = (array)$config['headers'];
    }
    private function getLog()
    {
        $tmp = array();
        $tmp['datetime'] = $this->datetime;
        $tmp['server'] = $this->server;
        if ((!empty($this->srv)))
        {
            $tmp=array_merge($tmp,$this->srv);
        }

        if (($this->srv['REQUEST_METHOD'] === 'GET') && !empty($this->argByGet))
        {
          //  array_walk($this->argByGet,function(&$item,$key, $prefix){$item = "[GET] $prefix $item";});
            $tmp=array_merge($tmp,$this->argByGet);
        }

        if (($this->srv['REQUEST_METHOD'] === 'POST') && !empty($this->argByPost))
        {
            $tmp=array_merge($tmp,$this->argByPost);
        }

        $value = file_get_contents("php://input");
        if (strlen($value) > 0)
        {
            $key = "[POST] [RAW POST]";

            if ($this->isStrange($value))
            {
                $value =$this->strangeStr;
            }
            $tmp = array_merge($tmp,array($key => $value));
        }
        return $tmp;
    }

    public function post()
    {

        //Transform our POST array into a URL-encoded query string.
        $postStr = http_build_query($this->getLog());
        $this->setContentLength($postStr);
      
        //Create an $options array that can be passed into stream_context_create.
        print_r($postStr);
        $h = array_map(function ($h, $v) {return "$h: $v";}, array_keys($this->headers), $this->headers);
        echo "HEADERS \n";
        print_r($h);

        
        $options = array(
            'http' =>
                array(
                    'method'  => 'POST', //We are using the POST HTTP method.
                    'header'  =>  $this->headers,
                    'content' => $postStr //Our URL-encoded query string.
                    )
        );
 

        //Pass our $options array into stream_context_create.
        //This will return a stream context resource.
        $streamContext  = stream_context_create($options);
        echo "stream context is \n";
        print_r($streamContext);
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

    private function setContentLength($str)
    {
        $this->headers['Content-Length'] = strlen((string)$str);
    }

}