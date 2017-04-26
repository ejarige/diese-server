<?php
include_once('../dbconfig.php');
header('Access-Control-Allow-Origin:*');

if(
    isset($_POST["user_id"]) && $_POST["user_id"]
){
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pwd);

        $friends = $pdo->prepare(
            "SELECT users.id, users.login, users.avatar
             FROM users_friends
             LEFT JOIN users
             ON users_friends.user1_id = users.id
             WHERE user2_id = :user_id
             UNION
             SELECT users.id, users.login, users.avatar
             FROM users_friends
             LEFT JOIN users
             ON users_friends.user2_id = users.id
             WHERE user1_id = :user_id"
        );

        $friends->bindParam(':user_id', $_POST["user_id"]);
        $friends->execute();

        $res = $friends->fetchAll(PDO::FETCH_ASSOC);

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