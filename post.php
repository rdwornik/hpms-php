<?php

function post($url, $postVars = array()){
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
    if($result === false){
        //If the request failed, throw an Exception containing
        //the error.
        $error = error_get_last();
        throw new Exception('POST request failed: ' . $error['message']);
    }
    //If everything went OK, return the response.
    return $result;
}

try{
    $result = post('http://www.hpd.example.com/hpmsPHP/test.php', array(
        'name' => 'bar',
        'age' => 'Wayne'
    ));
    echo $result;
} catch(Exception $e){
    echo $e->getMessage();
}

function save()
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
