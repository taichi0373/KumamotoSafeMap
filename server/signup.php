<?php

function connectToDatabase($servername, $username, $password, $dbname)
{
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        echo json_encode(array("success" => false, "message" => "エラー: " . $e->getMessage()));
        return null;
    }
}

function checkIfUsernameExists($conn, $username)
{
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    return $stmt->rowCount() > 0;
}

function registerNewUser($conn, $username, $password)
{
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->execute();
}


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gis_project";


$conn = connectToDatabase($servername, $username, $password, $dbname);

if ($conn) {
    $username = $_POST['username'];

    if (checkIfUsernameExists($conn, $username)) {
        echo json_encode(array("success" => false, "message" => "ユーザー名が既に存在します。"));
    } else {
        registerNewUser($conn, $username, $_POST['password']);
        echo json_encode(array("success" => true));
    }

    $conn = null;
}
