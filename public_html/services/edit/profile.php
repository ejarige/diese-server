<?php
include_once('../dbconfig.php');
include_once('../Eventful.php');
header('Access-Control-Allow-Origin:*');

$hasFile = isset($_FILES['avatar']) && !(0 < $_FILES['avatar']['error']);
$filePath = '';

if($hasFile){
    $fileName = uniqid().'.'.pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $filePath = AVATAR_REAL_PATH .$fileName;
    move_uploaded_file($_FILES['avatar']['tmp_name'], $filePath);
}

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pwd);

    $query = "UPDATE users SET prenom = :prenom, lieu = :ville, age = :age"
        .($hasFile ? ", avatar = :avatar" : '' )
        ." WHERE id = :userid";

    $req = $pdo->prepare($query);
    $req->bindParam(':age',     $_POST['age']);
    $req->bindParam(':ville',   $_POST['ville']);
    $req->bindParam(':prenom',  $_POST['prenom']);
    $req->bindParam(':userid',  $_POST['userId']);

    if($hasFile){
        $realUrl = "http://diese.pe.hu/avatar/".$fileName;
        $req->bindParam(':avatar',  $realUrl);

        $check = $pdo->prepare(
            "SELECT avatar FROM users WHERE id=:userid"
        );

        $check->bindParam(":userid", $_POST['userId']);
        $check->execute();

        $res = $check->fetchColumn();
        if($res){
            $oldFile = AVATAR_REAL_PATH.pathinfo($res, PATHINFO_BASENAME);
            if(file_exists($oldFile))
                unlink(AVATAR_REAL_PATH.pathinfo($res, PATHINFO_BASENAME));
        }
    }

    $req->execute();

    http_response_code(200);

} catch (Exception $e) {
    http_response_code(503);
    echo $e;
}