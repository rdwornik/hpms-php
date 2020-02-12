<?php
include_once "Table.class.php";
require "../../../private/settings.php";

class Log extends Table
{
    private $argByPost; //By POST
    private $argByGet; //By GET
    private $datetime; //connection time
    private $srv; //$_SERVER HTTP
    private $server; //user server name
    private $filter; //filter
    private $strange; //strange
    private $strangeStr; //strange info string
    private $table_name; //table name to which data will be added it
    private $column_name; //column name to which data inserted

    public function __construct($db)
    {
       global $config;
       parent::__construct($db);
        $this->srv = $_SERVER;
        $this->argByPost = $_POST;
        $this->argByGet = $_GET;
        $this->datetime = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $this->server = $config['serverName'];
        $this->filter = $config['filter'];
        $this->strange = $config['strange'];
        $this->strangeStr = $config['strangeStr'];
        $this->table_name = $config['DBtable'];
        $this->column_name = $config['DBcolumn'];
    }
    public function save()
    {
        $tmp = array();
        $result = true;
        if ((!empty($this->srv)))
        {
            foreach ($this->srv as $key => $value)
            {
                if (!in_array($key, $filter))
                {
                    if ($this->isStrange($value))
                    {
                        $value = $strangeStr;
                    }
                    $tmp[$key] = $value;
                }

            }
            $result = $this->sentData($tmp) && $result;
        }

        if (($this->srv['REQUEST_METHOD'] === 'GET') && !empty($this->argByGet)) 
        {
            foreach ($this->argByGet as $key => $value)
            {
                if (!in_array($key, $this->filter))
                {
                    $key = "[GET] " . $key;
                    if ($this->isStrange($value))
                    {
                        $value = $strangeStr;
                    }
                    $tmp[$key] = $value;
                }
            }
            $result = $this->sentData($tmp) && $result;
        }

        if (($this->srv['REQUEST_METHOD'] === 'POST') && !empty($this->argByPost))
        {
            foreach ($this->argByPost as $key => $value)
            {
                if (!in_array($key, $this->filter))
                {
                    $key = "[POST] " . $key;
                    if ($this->isStrange($value))
                    {
                        $value = $strangeStr;
                    }
                    $tmp[$key] = $value;
                }
            }
            $result = $this->sentData($tmp) && $result;
        }

        $key = "[POST] [RAW POST]";
        $value = file_get_contents("php://input");
        if (strlen($value) > 0) 
        {
            if ($this->isStrange($value))
            {
                $value = $this->$strangeStr;
            }
            $tmp = array($key => $value);
            $result = $this->sentData($tmp) && $result;
        }
        return $result;
    }

    public function sentData(&$logArray)
    {
        $this->result = false;
        $tmp = (array) $logArray;
       //
        $entrySQL = "insert into logs_log (data) values (?)";
        $tmp['time'] = $this->datetime;
        $myJson = json_encode($tmp);
        $formData = array($myJson);
        if (!(empty($formData)))
        {
            $this->result = $this->makeStatement($entrySQL, $formData);
        }
        $logArray = array();
        return $this->result;
    }

    private function isStrange($str)
    {
        $strange = array(
            "01",
            "03",
            "80");

        foreach (str_split($str) as $c)
        {
            $h = sprintf("%02x", ord($c));
            if (in_array($h, $this->strange))
            {
                return true;
            }
        }
        return false;
    }

}