<?php
include_once('../dbconfig.php');
header('Access-Control-Allow-Origin:*');

if(!isset($_POST["name"])) $_POST["name"] = '';
if(!isset($_POST["open"])) $_POST["open"] = 0;
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
            $add->bindParam(":$k", $v);

        $conv->execute();

        $conv_id = $conv->fetch(PDO::FETCH_ASSOC)['id'];

        $users  = explode(",", $_POST["user_ids"]);
        $values = [];

        foreach($users as $u){
            $values[] = array(
                ":conv_id"   => $conv_id,
                ":user_id"   => $u,
                ":privilege" => 0
            );
        }

        $conv_users = $pdo->prepare('INSERT INTO msg_conv_users (conv_id,user_id,privilege)
                                  VALUES (:conv_id,:user_id,:privilege)');

        foreach($values as $v){
            $conv_users->execute($v);
        }

        http_response_code(200);

    } catch (Exception $e) {
        http_response_code(503);
        echo $e;
    }
} else {
    http_response_code(400);
}