<?php
session_start();
$conn = new mysqli("localhost", "root", "", "servicemarketplace");

if (!isset($_SESSION['id'])) exit;

$currentUser = $_SESSION['id'];
$receiver_id = intval($_GET['receiver_id']);

$sql = "SELECT * FROM chats 
        WHERE (sender_id=? AND receiver_id=?)
        OR (sender_id=? AND receiver_id=?)
        ORDER BY created_at ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $currentUser, $receiver_id, $receiver_id, $currentUser);
$stmt->execute();
$result = $stmt->get_result();

while ($m = $result->fetch_assoc()) {
    $isMine = ($m['sender_id'] == $currentUser) ? "mine" : "theirs";

    echo "<div class='message $isMine'>
            <div class='bubble'>" . htmlspecialchars($m['message']) . "</div>
            <div class='time'>" . date('H:i', strtotime($m['created_at'])) . "</div>
          </div>";
}
?>
