<?php
include_once('../dbconfig.php');
header('Access-Control-Allow-Origin:*');

if(
    isset($_POST["login"])        && $_POST["login"]
    && isset($_POST["password"])  && $_POST["password"]
){
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pwd);

        $check = $pdo->prepare(
            "SELECT id FROM users WHERE (login=:login OR email=:login) AND password=:password"
        );

        foreach($_POST as $k=>&$v)
            $check->bindParam(":$k", $v);

        $check->execute();

        http_response_code(200);
        echo $check->fetchColumn();

    } catch (Exception $e) {
        http_response_code(503);
        echo $e;
    }

    http_response_code(200);
} else {
    http_response_code(400);
}