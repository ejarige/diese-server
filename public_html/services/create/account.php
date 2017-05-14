<?php
require_once '../dbconfig.php';
header('Access-Control-Allow-Origin:*');

if(
    isset($_POST["login"])      && $_POST["login"]
    && isset($_POST["email"])    && $_POST["email"]
    && isset($_POST["password"]) && $_POST["password"]
    && isset($_POST["prenom"])
){
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pwd);

        // verif non existance user
        $check = $pdo->prepare(
            "SELECT login, email FROM users WHERE login=:login OR email=:email"
        );

        $check->bindParam(":login", $_POST['login']);
        $check->bindParam(":email", $_POST['email']);

        $check->execute();

        $array = array();
        while ($row = $check->fetch(PDO::FETCH_ASSOC))
            $array[] = $row;

        if(!$array){
            $add = $pdo->prepare(
                "INSERT INTO users (login, email, password, prenom, avatar)
                 VALUES(:login, :email, :password, :prenom, 'http://diese.pe.hu/avatar/default-image-profile.jpg')"
            );

            foreach($_POST as $k=>&$v)
                $add->bindParam(":$k", $v);

            $add->execute();

            http_response_code(200);
        } else {
            http_response_code(401);
            echo json_encode($array);
        }
    } catch (Exception $e) {
        http_response_code(503);
        echo $e;
    }
} else {
    http_response_code(400);
}