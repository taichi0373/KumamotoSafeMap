<?php
// コメント保存
function insertComment($conn, $accident_id, $username, $comment)
{
  # datetime Tokyo
  date_default_timezone_set('Asia/Tokyo');
  $datetime = date("Y-m-d H:i:s");

  $sql = "INSERT INTO comments (accident_id, username, comment, date_comment) VALUES ('$accident_id', '$username', '$comment', '$datetime')";

  if ($conn->query($sql) === TRUE) {
    $response = array("status" => "success");
  } else {
    $response = array("status" => "error");
  }
  return $response;
}

// コメント取得
function getComments($conn, $accident_ids)
{
  if (!empty($accident_ids)) {
    // datetimeが古い順にコメントを取得
    $accident_ids_string = implode(',', $accident_ids);
    $sqlQuery = "SELECT comments.comment_id, comments.accident_id, comments.username, comments.comment, comments.date_comment
    FROM comments 
    INNER JOIN traffic_accident_kumamoto ON comments.accident_id = traffic_accident_kumamoto.id
    WHERE accident_id IN ($accident_ids_string) ORDER BY date_comment";

    $result = $conn->query($sqlQuery);
    $comments = array();
    while ($row = $result->fetch_assoc()) {
      $comments[] = array(
        'comment_id' => $row['comment_id'],
        'accident_id' => $row['accident_id'],
        'username' => $row['username'],
        'comment' => $row['comment'],
        'date_comment' => $row['date_comment']
      );
    }
  } else {
    $responseData = ['status' => 'error'];
    $jsonResponse = json_encode($responseData);
    echo $jsonResponse;
    exit;
  }
  return $comments;
}

// コメントIDから緯度・経度取得
function get_location($conn, $accident_id)
{
  $sqlQuery = "SELECT traffic_accident_kumamoto.latitude, traffic_accident_kumamoto.longitude
  FROM traffic_accident_kumamoto
  WHERE traffic_accident_kumamoto.id = $accident_id;";
  $result = $conn->query($sqlQuery);
  $comments = array();
  while ($row = $result->fetch_assoc()) {
    $comments[] = array(
      'latitude' => $row['latitude'],
      'longitude' => $row['longitude']
    );
  }
  return $comments;
}

// コメント編集
function updateComment($conn, $comment_id, $comment)
{
  if (empty($comment_id)) {
    $responseData = ['status' => 'error'];
    $jsonResponse = json_encode($responseData);
    echo $jsonResponse;
    exit;
  }
  # datetime Tokyo
  date_default_timezone_set('Asia/Tokyo');
  $datetime = date("Y-m-d H:i:s");
  $sqlQuery = "UPDATE comments SET comment = '$comment', date_comment = '$datetime' WHERE comment_id = $comment_id";
  $result = $conn->query($sqlQuery);

  return $result;
}

// コメント削除
function deleteComment($conn, $comment_id)
{
  if (empty($comment_id)) {
    $responseData = ['status' => 'error'];
    $jsonResponse = json_encode($responseData);
    echo $jsonResponse;
    exit;
  }

  $sqlQuery = "DELETE FROM comments WHERE comment_id = '$comment_id'";
  $result = $conn->query($sqlQuery);

  return $result;
}


function connectToDatabase($servername, $username, $password, $dbname)
{
  $conn = new mysqli($servername, $username, $password, $dbname);
  if ($conn->connect_error) {
    die("接続に失敗しました: " . $conn->connect_error);
  }
  return $conn;
}

function closeConnection($conn)
{
  $conn->close();
}

// POSTリクエストからデータを受け取る
$job = $_POST['job'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gis_project";

$conn = connectToDatabase($servername, $username, $password, $dbname);

if ($job === 'get_comments') {
  if ($conn instanceof mysqli) {
    $accident_id_json = $_POST['accident_id'];
    $accident_ids = json_decode($accident_id_json, true);

    $comments = getComments($conn, $accident_ids);
    $responseData = ['status' => 'success', 'comments' => $comments];
    $jsonResponse = json_encode($responseData);
    echo $jsonResponse;
  } else {
    echo json_encode(array("status" => "error", "message" => "Database connection error"));
  }
} else if ($job === 'keep_comments') {
  if ($conn instanceof mysqli) {
    $accident_id = $_POST['accident_id'];
    $username = $_POST['username'];
    $comment = $_POST['comment'];
    $responseData = insertComment($conn, $accident_id, $username, $comment);
    $jsonResponse = json_encode($responseData);
    echo $jsonResponse;
  } else {
    echo json_encode(array("status" => "error", "message" => "Database connection error"));
  }
} else if ($job === 'delete_comments') {
  if ($conn instanceof mysqli) {
    $comment_id = $_POST['comment_id'];
    $result = deleteComment($conn, $comment_id);
    $responseData = ['status' => 'success'];
    $jsonResponse = json_encode($responseData);
    echo $jsonResponse;
  } else {
    echo json_encode(array("status" => "error", "message" => "Database connection error"));
  }
} else if ($job === 'edit_comments') {
  if ($conn instanceof mysqli) {
    $comment_id = $_POST['comment_id'];
    $comment = $_POST['comment'];
    $result = updateComment($conn, $comment_id, $comment);
    $responseData = ['status' => 'success'];
    $jsonResponse = json_encode($responseData);
    echo $jsonResponse;
  } else {
    echo json_encode(array("status" => "error", "message" => "Database connection error"));
  }
} else if ($job === 'get_location') {
  if ($conn instanceof mysqli) {
    $accident_id = $_POST['accident_id'];
    $get_location = get_location($conn, $accident_id);
    $responseData = ['status' => 'success', 'get_location' => $get_location];
    $jsonResponse = json_encode($responseData);
    echo $jsonResponse;
  } else {
    echo json_encode(array("status" => "error", "message" => "Database connection error"));
  }
} else {
  echo json_encode(array("status" => "error", "message" => "Invalid request"));
}

closeConnection($conn);
