<?php
include_once('../dbconfig.php');
header('Access-Control-Allow-Origin:*');

if(
    isset($_POST["user_id"])        && $_POST["user_id"]
    && isset($_POST["concert_id"])  && $_POST["concert_id"]
){
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pwd);

        $add = $pdo->prepare(
            "INSERT INTO concerts_participants (user_id, concert_id)
                 VALUES(:user_id,   :concert_id)"
        );

        foreach($_POST as $k=>&$v)
            $add->bindParam(":$k", $v);

        $add->execute();

        http_response_code(200);

    } catch (Exception $e) {
        http_response_code(503);
        echo $e;
    }
} else {
    http_response_code(400);
}