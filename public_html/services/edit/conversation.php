<?php
include_once('../dbconfig.php');
include_once('../Eventful.php');
header('Access-Control-Allow-Origin:*');

// DEBUG
$_POST["conv_id"] = 13;

if(
    isset($_POST["conv_id"]) && $_POST["conv_id"]
){

    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pwd);

        // close conv
        $close = $pdo->prepare("UPDATE msg_conv SET open = 1 WHERE id=:conv_id");

        $close->bindParam(':conv_id', $_POST["conv_id"]);
        $close->execute();

        // get conv users
        $conv_users = $pdo->prepare("SELECT user_id FROM msg_conv_users WHERE conv_id=:conv_id");

        $conv_users->bindParam(':conv_id', $_POST["conv_id"]);
        $conv_users->execute();
        $users = $conv_users->fetchAll(PDO::FETCH_ASSOC);

        /* TODO CONV DE GROUPE
        foreach($users as $u){

        }*/

        // FIX SOUTENANCE conv 1v1
        $add = $pdo->prepare(
            "INSERT INTO users_friends (user1_id, user2_id)
                 VALUES(:user_id, :friend_id)"
        );
        $add->bindParam(':user_id',     $users[0]['user_id']);
        $add->bindParam(':friend_id',   $users[1]['user_id']);
        $add->execute();

    } catch (Exception $e) {
        http_response_code(503);
        echo $e;
    }
} else {
    http_response_code(400);
}