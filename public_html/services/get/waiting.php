<?php
include_once('../dbconfig.php');
header('Access-Control-Allow-Origin:*');

if(
    isset($_POST["concert_id"]) && $_POST["concert_id"]
){
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pwd);

        $query = $pdo->prepare(
            "SELECT users.id, login, prenom, avatar, bio, sexe, age, lieu
             FROM concerts_waiting
             INNER JOIN users
             ON concerts_waiting.user_id = users.id
             AND concerts_waiting.concert_id = :concert_id
             AND NOT concerts_waiting.user_id = :user_id"
        );

        $query->bindParam(':user_id', $_POST["user_id"]);
        $query->bindParam(':concert_id', $_POST["concert_id"]);
        $query->execute();

        $res = $query->fetchAll(PDO::FETCH_ASSOC);

        // user tags
        $tags = $pdo->prepare(
            "SELECT category_id, alias
             FROM users_categories
             INNER JOIN categories
             ON users_categories.category_id = categories.eventful_id
             WHERE users_categories.user_id=:wait_id"
        );

        for($i=0;$i<count($res);$i++){
            $tags->execute(array(":wait_id" => $res[$i]['id']));
            $res[$i]['tags'] = $tags->fetchAll(PDO::FETCH_ASSOC);
        }

        /* if(empty($res)){
             $add = $pdo->prepare(
                 "INSERT INTO concerts_waiting VALUES (:user_id, :concert_id)"
             );

             $add->bindParam(':user_id', $_POST["user_id"]);
             $add->bindParam(':concert_id', $_POST["concert_id"]);
             $add->execute();
         }*/

        http_response_code(200);
        echo is_null($res[0]['id']) ? '[]' : json_encode($res, JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) {
        http_response_code(503);
        echo $e;
    }

    http_response_code(200);
} else {
    http_response_code(400);
}