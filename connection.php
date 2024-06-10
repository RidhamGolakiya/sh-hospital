<?php
    $connection= new mysqli("localhost","root","","sh_project");
    $appUrl = "http://sh-hms.de";
    if ($connection->connect_error){
        die("Connection failed:  ".$connection->connect_error);
    }
?>