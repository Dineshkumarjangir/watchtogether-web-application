<?php
session_start();
include 'db.php';

// Only allow logged-in users to create a room
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle file upload
    if (isset($_FILES['video'])) {
        $video = $_FILES['video'];
        $videoPath = 'uploads/' . basename($video['name']);
        move_uploaded_file($video['tmp_name'], $videoPath);

        // Generate a room code (6 characters)
        $roomCode = strtoupper(bin2hex(random_bytes(3)));

        // Insert room data into the database
        $stmt = $conn->prepare("INSERT INTO rooms (room_code, video_filename) VALUES (?, ?)");
        $stmt->bind_param("ss", $roomCode, $videoPath);
        $stmt->execute();

        echo "Room created successfully! Room code: " . $roomCode;
    }
}
?>

<!-- create_room.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Room</title>
</head>
<body>
  <h2>Create a Room</h2>
  <form action="create_room.php" method="POST" enctype="multipart/form-data">
    <label for="video">Upload Video:</label>
    <input type="file" name="video" required><br>

    <button type="submit">Create Room</button>
  </form>

  <a href="index.php">Home</a>
</body>
</html>
