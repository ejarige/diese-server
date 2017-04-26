<?php
include_once('../dbconfig.php');
include_once('../Eventful.php');
header('Access-Control-Allow-Origin:*');

if(isset($_POST["artist_id"]) && $_POST["artist_id"]){

    $search = new Eventful('performers');
    $search
        ->service('get')
        ->setParams(array(
            'id'          => $_POST["artist_id"],
            'show_events' => 'true',
            'image_sizes' => 'large,thumb'
        ))
        ->exec();

    $res = $search->get();

    $tab = json_decode($res);

    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pwd);

        $check = $pdo->prepare(
            "INSERT INTO artists (eventful_id, name, bio, img, thumb)
             VALUES (:eventful_id, :name, :bio, :img, :thumb)"
        );

        $params = array(
            ':eventful_id' => $tab->id,
            ':bio'         => $tab->short_bio,
            ':name'        => $tab->name,
            ':img'         => $tab->image->large->url,
            ':thumb'       => $tab->image->thumb->url
        );

        $check->execute($params);


    } catch (Exception $e) {
        http_response_code(503);
        echo $e;
    }

    http_response_code(200);
    echo $tab != null ? $res : '[]';
} else {
    http_response_code(400);
}
