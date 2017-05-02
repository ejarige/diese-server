<?php
include_once('../dbconfig.php');
header('Access-Control-Allow-Origin:*');

if(
    isset($_POST["conv_id"]) && $_POST["conv_id"]
){
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pwd);

        $msg = $pdo->prepare(
            "SELECT msg.id, sender_id, date, text, avatar, login
             FROM msg
             LEFT JOIN users ON msg.sender_id = users.id
             WHERE msg.conversation_id = :conv_id
             ORDER BY date ASC"
        );

        $msg->bindParam(':conv_id', $_POST["conv_id"]);
        $msg->execute();

        $res = $msg->fetchAll(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) {
        http_response_code(503);
        echo $e;
    }

    http_response_code(200);
} else {
    http_response_code(400);
}