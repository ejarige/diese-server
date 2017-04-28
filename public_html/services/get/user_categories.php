<?php
include_once('../dbconfig.php');
header('Access-Control-Allow-Origin:*');

if(
    isset($_POST["user_id"]) && $_POST["user_id"]
){
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pwd);

        $query = $pdo->prepare(
            "SELECT eventful_id
             FROM users_categories
             LEFT JOIN categories
             ON users_categories.category_id = categories.id
             AND users_categories.user_id = :user_id"
        );

        $query->bindParam(':user_id', $_POST["user_id"]);
        $query->execute();

        $res = $query->fetchAll(PDO::FETCH_ASSOC);

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