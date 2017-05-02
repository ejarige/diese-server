<?php
include_once('../dbconfig.php');
header('Access-Control-Allow-Origin:*');

if(!isset($_POST["text"])) $_POST["text"] = '';
if(!isset($_POST["name"])) $_POST["name"] = '';
if(!isset($_POST["open"])) $_POST["open"] = false;
if(!isset($_POST["concert_id"])) $_POST["concert_id"] = '';

if(
    isset($_POST["user_ids"]) && $_POST["user_ids"]
    && isset($_POST["creator_id"]) && $_POST["creator_id"]
){
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pwd);

        $conv = $pdo->prepare(
            "INSERT INTO msg_conv (name, open, creator_id, concert_id)
                 VALUES(:name, :open, :creator_id, :concert_id)"
        );

        foreach($_POST as $k=>&$v)
            if($k != "user_ids" && $k != 'text')
                $conv->bindParam(":$k", $v);

        $conv->execute();

        $conv_id = $pdo->lastInsertId();

        $users  = explode(",", $_POST["user_ids"]);
        $values = [];

        $users[] = $_POST['creator_id'];
        foreach($users as $u){
            $values[] = array(
                ":conv_id"   => $conv_id,
                ":user_id"   => $u,
                ":privilege" => 0
            );
        }

        $conv_users = $pdo->prepare(
            'INSERT INTO msg_conv_users (conv_id,user_id,privilege)
                                  VALUES (:conv_id,:user_id,:privilege)'
        );

        foreach($values as $v)
            $conv_users->execute($v);

        $add = $pdo->prepare(
            "INSERT INTO msg (sender_id, conversation_id, date, text)
                 VALUES(:sender_id, :conversation_id, :date, :text)"
        );

        $add->execute(array(
            ":date" => time()*1000,
            ":text" => $_POST['text'],
            ":conversation_id" => $conv_id,
            ":sender_id" => $_POST['creator_id']
        ));

        echo $conv_id;
        http_response_code(200);

    } catch (Exception $e) {
        http_response_code(503);
        echo $e;
    }
} else {
    http_response_code(400);
}