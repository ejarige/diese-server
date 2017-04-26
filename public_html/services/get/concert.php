<?php
include_once('../dbconfig.php');
include_once('../Eventful.php');
header('Access-Control-Allow-Origin:*');

if(isset($_POST["concert_id"]) && $_POST["concert_id"]
    && isset($_POST["user_id"]) && $_POST["user_id"]){

    $search = new Eventful();
    $search
        ->service('get')
        ->setParams(array(
            'id'          => $_POST["concert_id"],
            'image_sizes' => 'large'
        ))
        ->exec();

    $res = $search->get();

    $tab = json_decode($res);

    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pwd);
        // check if user is on wainting list
        $userWaiting = $pdo->prepare(
            "SELECT * FROM concerts_waiting
            WHERE concert_id = :concert_id AND user_id = :user_id"
        );

        $userWaiting->bindParam(':user_id', $_POST["user_id"]);
        $userWaiting->bindParam(':concert_id', $_POST["concert_id"]);
        $userWaiting->execute();

        $tab->{'user_waiting'} = $userWaiting->rowCount() == 1;

        // get waiting list
        $allWaiting = $pdo->prepare(
            "SELECT users.id, users.login, users.avatar FROM concerts_waiting
            LEFT JOIN users
            ON concerts_waiting.user_id = users.id
            WHERE concerts_waiting.concert_id = :concert_id
            AND concerts_waiting.user_id <> :user_id"
        );

        $allWaiting->bindParam(':user_id', $_POST["user_id"]);
        $allWaiting->bindParam(':concert_id', $_POST["concert_id"]);
        $allWaiting->execute();

        $tab->{'all_waiting'} = $allWaiting->fetchAll(PDO::FETCH_ASSOC);

        // get friends waiting
        $friendWaiting = $pdo->prepare(
            "SELECT users.id, users.login, users.avatar FROM concerts_waiting
            LEFT JOIN users_friends
            ON concerts_waiting.user_id = users_friends.user1_id
            LEFT JOIN users
            ON concerts_waiting.user_id = users.id
            WHERE concerts_waiting.concert_id = :concert_id
            AND users_friends.user2_id = :user_id
            UNION
            SELECT users.id, users.login, users.avatar FROM concerts_waiting
            LEFT JOIN users_friends
            ON concerts_waiting.user_id = users_friends.user2_id
            LEFT JOIN users
            ON concerts_waiting.user_id = users.id
            WHERE concerts_waiting.concert_id = :concert_id
            AND users_friends.user1_id = :user_id"
        );

        $friendWaiting->bindParam(':user_id', $_POST["user_id"]);
        $friendWaiting->bindParam(':concert_id', $_POST["concert_id"]);
        $friendWaiting->execute();

        $tab->{'friend_waiting'} = $friendWaiting->fetchAll(PDO::FETCH_ASSOC);
    } catch(Exception $e){
        http_response_code(503);
        echo $e;
    }

    http_response_code(200);
    echo $tab != null ? json_encode($tab, JSON_UNESCAPED_UNICODE) : '{}';
} else {
    http_response_code(400);
}
