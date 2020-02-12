<?php
require "../../../private/settings.php";
class DBconnection
{
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $db;

    public function __construct()
    {
        global $config;
        $this->host = $config['DBhost'];
        $this->dbname = $config['DBname'];
        $this->username = $config['DBuser'];
        $this->password = $config['DBpass'];
        $dsn = "pgsql:dbname=$this->dbname;host=$this->host;user=$this->username;password=$this->password";

        try
        {
            // create a PostgreSQL database connection
            $this->db = new PDO($dsn);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // display a message if connected to the PostgreSQL successfully
            if ($this->db)
            {
            }
        } catch (PDOException $e)
        {
            // report error message
            die();
        }

    }

    public function __destruct()
    {
        unset($this->db);
    }

    public function getdb()
    {
        return $this->db;
    }

}
