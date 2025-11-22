<?php
session_start();
if (!isset($_SESSION['id'])) {
    die("<script>alert('Please login to report.'); window.location='login.php';</script>");
}

$conn = new mysqli("localhost", "root", "", "servicemarketplace");

$service_id = intval($_POST['service_id']);
$reason = trim($_POST['reason']);
$reported_by = $_SESSION['id'];

if (empty($reason)) {
    die("<script>alert('Please provide a reason.'); history.back();</script>");
}

$stmt = $conn->prepare("INSERT INTO reports (service_id, reported_by, reason) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $service_id, $reported_by, $reason);

if ($stmt->execute()) {
    echo "<script>alert('Report submitted successfully!'); window.location='view_service.php?id=$service_id';</script>";
} else {
    echo "<script>alert('Error submitting report.'); history.back();</script>";
}
?>
