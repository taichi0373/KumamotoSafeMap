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

function loginUser($pdo, $username, $password)
{
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // ログイン成功
        session_start();
        $_SESSION['username'] = $user['username'];
        return true;
    } else {
        // ログイン失敗
        return false;
    }
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gis_project";

$conn = connectToDatabase($servername, $username, $password, $dbname);

$username = $_POST['username'];
$password = $_POST['password'];

if (loginUser($conn, $username, $password)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'ユーザー名またはパスワードが違います']);
}
