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
        $this->filter = $config['filter'];
        $this->strange = $config['strange'];
        $this->strangeStr = $config['strangeStr'];
        $this->url = $config['url'];
        $this->headers = (array)$config['headers'];
        $this->auth = $config['user'].":".$config['password'];
    }
    private function getLog()
    {
        $add = function($h, $v)
        {
            return array('name' => $h, 'value' => $v);
        };

        $tmp = array();
        array_push($tmp, $add('datetime',$this->datetime));
        array_push($tmp, $add('server',$this->server));
        $to_filtr = $this->filter;
        $result = array();

        $func = function (&$item1, &$key, $prefix) use(&$result, $to_filtr,$add)
        {
            if(!in_array($key, $to_filtr))
                $result[$prefix.$key] = $item1;
        };


        if ((!empty($this->srv)))
        {
            $result = array();
            array_walk($this->srv,$func,'');
            $tmp = array_merge($tmp,array_map($add, array_keys($result), $result));
        }

        if (($this->srv['REQUEST_METHOD'] === 'GET') && !empty($this->argByGet))
        {
            $result = array();
            array_walk($this->argByGet,$func,'[GET] ');
            $tmp = array_merge($tmp,array_map($add, array_keys($result), $result));
        }

        if (($this->srv['REQUEST_METHOD'] === 'POST') && !empty($this->argByPost))
        {
            $result = array();
            array_walk($this->argByPost,$func,'[POST] ');
            $tmp = array_merge($tmp,array_map($add, array_keys($result), $result));
        }

        $value = file_get_contents("php://input");
        if (strlen($value) > 0)
        {
            $key = "[POST] [RAW POST]";

            if ($this->isStrange($value))
            {
                $value =$this->strangeStr;
            }
            array_push($tmp,$add($key,$value));
        }
        return $tmp;
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