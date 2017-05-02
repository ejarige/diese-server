<?php
include_once('../dbconfig.php');
header('Access-Control-Allow-Origin:*');

if(
    isset($_POST["text"])               && $_POST["text"]
    && isset($_POST["sender_id"])       && $_POST["sender_id"]
    && isset($_POST["conversation_id"]) && $_POST["conversation_id"]
){
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pwd);

        $add = $pdo->prepare(
            "INSERT INTO msg (sender_id, conversation_id, date, text)
                 VALUES(:sender_id, :conversation_id, :date, :text)"
        );

        foreach($_POST as $k=>&$v)
            $add->bindParam(":$k", $v);

        $time = time()*1000;
        $add->bindParam(":date", $time);

        $add->execute();

        echo $pdo->lastInsertId();

        http_response_code(200);

    } catch (Exception $e) {
        http_response_code(503);
        echo $e;
    }
} else {
    http_response_code(400);
}