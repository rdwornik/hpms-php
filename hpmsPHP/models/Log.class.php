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
        $this->auth = $config['user'].":".$config['password'];
        $this->result = array();
    }
    private function getLog()
    {
        $add = function($h, $v)
        {
            return array('key' => $h, 'value' => $v);
        };

        $tmp = array();
        array_push($tmp, $add('datetime',$this->datetime));
        array_push($tmp, $add('server',$this->server));


        if ((!empty($this->srv)))
        {
            $tmp = array_merge($tmp,array_map($add, array_keys($this->srv), $this->srv));
        }

        if (($this->srv['REQUEST_METHOD'] === 'GET') && !empty($this->argByGet))
        {
          //  array_walk($this->argByGet,function(&$item,$key, $prefix){$item = "[GET] $prefix $item";});
            $tmp = array_merge($tmp,array_map($add, array_keys($this->argByGet), $this->argByGet));
        }

        if (($this->srv['REQUEST_METHOD'] === 'POST') && !empty($this->argByPost))
        {
            $tmp = array_merge($tmp,array_map($add, array_keys($this->argByPost), $this->argByPost));
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
        #$body = $this->getLog();
        $this->setContentLength($body);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERPWD, $this->auth);  
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $result = curl_exec($ch);
        print_r($result);
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