<?php
include_once('../dbconfig.php');
header('Access-Control-Allow-Origin:*');

if(
    isset($_POST["user_id"])       && $_POST["user_id"]
    && isset($_POST["friend_id"])  && $_POST["friend_id"]
){
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pwd);

        $add = $pdo->prepare(
            "INSERT INTO users_friends (user1_id, user2_id)
                 VALUES(:user_id, :friend_id)"
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