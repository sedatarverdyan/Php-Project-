<?php
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $checkUser = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $checkUser->bind_param("s", $username);
    $checkUser->execute();
    $result = $checkUser->get_result();

    if ($result->num_rows > 0) {
        echo "Username already exists.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        echo "Registration successful!";
    }
}
?>
<form method="POST">
    <label>Username:</label>
    <input type="text" name="username" required>
    <label>Password:</label>
    <input type="password" name="password" required>
    <button type="submit">Register</button>
</form>
