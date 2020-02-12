<?php
// include "models/Log.class.php";


include "models/Log.class.php";

try{
    $log = new Log();
    $res = $log->post();
    echo $res;
} catch(Exception $e){
    echo $e->getMessage();
}

echo $config['notice'];