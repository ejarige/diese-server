<?php
include_once('../dbconfig.php');
header('Access-Control-Allow-Origin:*');

if(
    isset($_POST["user_id"]) && $_POST["user_id"]
){
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pwd);

        // conversations
        $convs = $pdo->prepare(
            "SELECT msg_conv.id, msg_conv.concert_id, msg_conv.open, msg_conv.name
             FROM msg_conv_users
             INNER JOIN msg_conv ON msg_conv.id = msg_conv_users.conv_id
             AND msg_conv_users.user_id = :user_id"
        );

        $convs->bindParam(':user_id', $_POST["user_id"]);
        $convs->execute();

        $res = $convs->fetchAll(PDO::FETCH_ASSOC);

        // users
        $users = $pdo->prepare(
            "SELECT users.id, users.avatar, users.login, msg_conv_users.privilege
             FROM msg_conv_users
             INNER JOIN users ON msg_conv_users.user_id = users.id
             WHERE msg_conv_users.conv_id = :conv_id"
        );

        foreach($res as &$conv){
            $users->execute(array(":conv_id" => $conv['id']));
            $conv['users'] = $users->fetchAll(PDO::FETCH_ASSOC);
        }

        // last message
        $msg = $pdo->prepare(
            "SELECT text FROM msg
             WHERE conversation_id = :conv_id
             ORDER BY date DESC LIMIT 1"
        );

        foreach($res as &$conv){
            $msg->execute(array(":conv_id" => $conv['id']))['text'];
            $conv['lastmessage'] = $msg->fetchAll(PDO::FETCH_ASSOC);
        }

        http_response_code(200);
        echo json_encode($res, JSON_UNESCAPED_UNICODE );

    } catch (Exception $e) {
        http_response_code(503);
        echo $e;
    }

    http_response_code(200);
} else {
    http_response_code(400);
}