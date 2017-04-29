<?php
include_once('../dbconfig.php');
header('Access-Control-Allow-Origin:*');

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pwd);

    $query = $pdo->prepare(
        "SELECT * FROM categories ORDER BY alias ASC"
    );

    $query->execute();

    $res = $query->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode($res, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(503);
    echo $e;
}
