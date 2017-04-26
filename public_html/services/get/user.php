<?php
include_once('../dbconfig.php');
header('Access-Control-Allow-Origin:*');

if(isset($_POST["user_id"]) && $_POST["user_id"]){
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pwd);

        // user info
        $user = $pdo->prepare(
            "SELECT login ,nom ,prenom ,sexe ,age ,avatar ,bio ,lieu
             FROM users WHERE id=:user_id"
        );
        $user->bindParam(":user_id", $_POST['user_id']);
        $user->execute();

        $res = $user->fetchAll(PDO::FETCH_ASSOC)[0];

        // concerts_interested
        $interested = $pdo->prepare(
            "SELECT
                concerts.eventful_id, concerts.title,       concerts.artist_name,
                concerts.city_name,   concerts.region_name, concerts.thumb
             FROM concerts_interested WHERE concerts_interested.user_id=:user_id
             LEFT JOIN concerts
             ON concerts_interested.concert_id = concerts.id"
        );
        $interested->bindParam(":user_id", $_POST['user_id']);
        $interested->execute();

        $res['concerts_interested'] = $interested->fetchAll(PDO::FETCH_ASSOC);

        // concerts_participate
        $participate = $pdo->prepare(
            "SELECT
                concerts.eventful_id, concerts.title,       concerts.artist_name,
                concerts.city_name,   concerts.region_name, concerts.thumb
             FROM concerts_participate WHERE concerts_participate.user_id=:user_id
             LEFT JOIN concerts
             ON concerts_participate.concert_id = concerts.id"
        );
        $participate->bindParam(":user_id", $_POST['user_id']);
        $participate->execute();

        $res['concerts_participate'] = $participate->fetchAll(PDO::FETCH_ASSOC);

        // artists fan
        $artists = $pdo->prepare(
            "SELECT artists.eventful_id, artists.name, artists.thumb,
             FROM artists_fans WHERE artists_fans.user_id=:user_id
             LEFT JOIN artists
             ON artists_fans.artist_id = artists.id"
        );
        $artists->bindParam(":user_id", $_POST['user_id']);
        $artists->execute();

        $res['artists_fan'] = $artists->fetchAll(PDO::FETCH_ASSOC);

        // open groups
        $groups = $pdo->prepare(
            "SELECT msg_conv.id, msg_conv.name, msg_conv.concert_id
             FROM msg_conv_users WHERE msg_conv_users.user_id=:user_id
             LEFT JOIN msg_conv
             ON msg_conv_users.conv_id = msg_conv.id"
        );
        $groups->bindParam(":user_id", $_POST['user_id']);
        $groups->execute();

        $res['open_groups'] = $groups->fetchAll(PDO::FETCH_ASSOC);

        // groups members
        $members = $pdo->prepare(
            "SELECT users.id, users.login, users.avatar
             FROM msg_conv_users WHERE msg_conv_users.conv_id=:conv_id
             LEFT JOIN users
             ON msg_conv_users.user_id = users.id"
        );

        foreach($res['open_groups'] as $group){
            $members->execute(array(':conv_id' => $group['id']));
            $group['users'] = $members->fetchAll(PDO::FETCH_ASSOC);
        }

        http_response_code(200);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) {
        http_response_code(503);
        echo $e;
    }
} else {
    http_response_code(400);
}
	