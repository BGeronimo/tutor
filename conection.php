<?php

$host = 'localhost';
$db = 'dbtutorias';
$user = 'root';
$pass  = '';


try{

    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
 
}catch(Exception $ex ){
    echo $ex->getMessage();
}

?>