<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - WatchTogether+</title>
</head>
<body>
  <h2>Welcome, <?php echo $_SESSION['username']; ?>!</h2>

  <form action="upload.php" method="POST" enctype="multipart/form-data">
    <label>Select Movie (MP4):</label><br>
    <input type="file" name="video" accept="video/mp4" required><br><br>
    <button type="submit">Upload and Create Room</button>
  </form>

  <form action="php/logout.php" method="POST">
    <button type="submit">Logout</button>
  </form>
</body>
</html>
