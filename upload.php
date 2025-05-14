<?php
session_start();
include 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['video'])) {
    $file = $_FILES['video'];
    $filename = basename($file['name']);
    $target = "uploads/" . time() . "_" . $filename;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        // Generate unique room code
        $roomCode = substr(md5(uniqid()), 0, 6);
        $hostId = $_SESSION['user_id'];

        $stmt = $conn->prepare("INSERT INTO rooms (room_code, host_id, video_filename) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $roomCode, $hostId, $target);

        if ($stmt->execute()) {
            header("Location: room.php?code=$roomCode");
            exit();
        } else {
            echo "Room creation failed.";
        }
    } else {
        echo "File upload failed.";
    }
}
?>
