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
    private $auth;

    public function __construct()
    {
       global $config;
        $this->srv = array_filter($_SERVER, 'strlen');
        $this->argByPost =  array_filter($_POST, 'strlen');
        $this->argByGet = array_filter($_GET, 'strlen');
        $this->datetime = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $this->server = $config['serverName'];
        $this->filter = (array)$config['filter'];
        $this->strange = (array)$config['strange'];
        $this->strangeStr = $config['strangeStr'];
        $this->url = $config['url'];
        $this->headers = (array)$config['headers'];
        $this->auth = $config['user'].":".$config['password'];
    }


    private function getLog()
    {
        $result[] = $this->add('VISITORS_IP', $this->getUserIP());

        foreach($this->srv as $key => $value)
        {
            if(!in_array($key, $this->filter))
            {
                if($this->isStrange($value))
                    $value=$this->strangeStr;
                $result[] = $this->add($key, $value);
            }
        }

        foreach($this->argByGet as $key => $value)
        {
            if(!in_array($key, $this->filter))
            {
                if($this->isStrange($value))
                    $value=$this->strangeStr;
                $result[] = $this->add('[GET]',"key: ".$key." value: ".$value);
            }
        }

        foreach($this->argByPost as $key => $value)
        {
            if(!in_array($key, $this->filter))
            {
                if($this->isStrange($value))
                    $value=$this->strangeStr;
                $result[] = $this->add('[POST]',"key: ".$key." value: ".$value);
            }
        }

        $value = file_get_contents("php://input");
        if (strlen($value) > 0)
        {
            $key = "[POST] [RAW POST]";
            if ($this->isStrange($value))
                $value = $this->strangeStr;
            $result[] = $this->add($key,$value);
        }

        print_r($result);
        return array(
            'time' => $this->datetime,
            'server' => $this->server,
            'headers' => $result
        );
    }

    public function post()
    {
        //If everything went OK, return the response.
        $header =  array_map(function ($h, $v) {return "$h: $v";}, array_keys($this->headers), $this->headers);
        $body = json_encode($this->getLog());
        $this->setContentLength($body);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERPWD, $this->auth); 
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $result = curl_exec($ch);
        return $result;
    }

    private function setContentLength($str)
    {
        $this->headers['Content-Length'] = strlen((string)$str);
    }

    private function getUserIP()
    {
        if( array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')>0) {
                $addr = explode(",",$_SERVER['HTTP_X_FORWARDED_FOR']);
                return trim($addr[0]);
            } else {
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        }
        else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    private function isStrange($str)
    {
        foreach(str_split($str) as $c)
        {
            $h = sprintf("%02x", ord($c));
            if(in_array($h, $this->strange))
                return true;
        }
        return false;
    }

    private function add($h, $v)
    {
        return array('name' => $h, 'value' => $v);
    }
}