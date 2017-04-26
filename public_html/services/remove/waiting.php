<?php
include_once('../dbconfig.php');
header('Access-Control-Allow-Origin:*');

if(
    isset($_POST["user_id"]) && $_POST["user_id"] &&
    isset($_POST["concert_id"]) && $_POST["concert_id"]
){
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pwd);

        $query = $pdo->prepare(
            "DELETE FROM concerts_waiting
             WHERE user_id = :user_id AND concert_id = :concert_id"
        );

        $query->bindParam(':user_id', $_POST["user_id"]);
        $query->bindParam(':concert_id', $_POST["concert_id"]);
        $query->execute();

        http_response_code(200);
    } catch (Exception $e) {
        http_response_code(503);
        echo $e;
    }

    http_response_code(200);
} else {
    http_response_code(400);
}