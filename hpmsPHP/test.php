<?php
echo "i am test ";

if(isset($_POST['name'],$_POST['age']))
{
   echo print_r($_POST);
}else{
    echo "noe";
}