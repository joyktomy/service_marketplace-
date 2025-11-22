<?php
session_start();
$conn = new mysqli("localhost", "root", "", "servicemarketplace");

if (!isset($_SESSION['id'])) exit;

$sender = $_SESSION['id'];
$receiver = intval($_POST['receiver_id']);
$message = trim($_POST['message']);

if ($message != "") {
    $stmt = $conn->prepare("INSERT INTO chats (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $sender, $receiver, $message);
    $stmt->execute();
    $stmt->close();
}

echo "OK";
?>
