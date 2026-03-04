<?php
    $host="localhost";
    $username= "root";
    $password="";
    $database="nhom11ltw";

    $conn=mysqli_connect($host, $username, $password, $database);
    mysqli_set_charset($conn,'utf8');
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    //check database
    if(!$conn)
    {
        die("Connection Faild ". mysqli_connect_errno());
    }

?>