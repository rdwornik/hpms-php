<?php
include "models/Log.class.php";
include "DBconnection.class.php";

$dbcon = new DBconnection();
$db = $dbcon->getdb();
$log = new Log($db);
$res = $log->save();

if (!$res)
{
    echo "Can't save all logs to a database";
    die();
}

echo $config['notice'];