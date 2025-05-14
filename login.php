<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login - WatchTogether</title>
</head>
<body>
  <h2>Login</h2>
  <form action="login_process.php" method="POST">
    <label>Username:</label>
    <input type="text" name="username" required><br><br>

    <label>Password:</label>
    <input type="password" name="password" required><br><br>

    <input type="submit" value="Login">
  </form>
</body>
</html>
