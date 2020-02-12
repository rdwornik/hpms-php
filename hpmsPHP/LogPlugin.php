<?php
// include "models/Log.class.php";
include "models/Log2.class.php";

// include "DBconnection.class.php";

// $dbcon = new DBconnection();
// $db = $dbcon->getdb();
// $log = new Log($db);
// $res = $log->save();
echo "log2";
$log2 = new Log2();
$log2->isStrange('roveer');
$res = $log2->save();
//print_r($res);
// if (!$res)
// {
//     echo "Can't save all logs to a database";
//     die();
// }

echo $config['notice'];