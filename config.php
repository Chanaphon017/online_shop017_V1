<?php
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "online_shop";

    $dns ="mysql:host=$host;dbname=$database";
    try {

        $conn = new PDO($dns, $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    } catch(PDOException $e){
    }