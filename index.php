<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>WatchTogether - Home</title>
</head>
<body>
  <h1>Welcome to WatchTogether</h1>

  <?php if (isset($_SESSION['username'])): ?>
    <p>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
    <a href="create_room.php">Create a Watch Room</a><br>
    <a href="logout.php">Logout</a>
  <?php else: ?>
    <a href="login.php">Login</a><br>
    <a href="register.php">Register</a>
  <?php endif; ?>
</body>
</html>
