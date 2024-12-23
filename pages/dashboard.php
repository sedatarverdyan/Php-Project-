<?php
// Start session and include database connection
session_start();
include '../includes/db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize $result to fetch posts
$result = null;

// Handle post creation and editing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];

    if (isset($_POST['post_id']) && !empty($_POST['post_id'])) {
        // Edit existing post
        $post_id = intval($_POST['post_id']);
        $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssii", $title, $content, $post_id, $_SESSION['user_id']);
    } else {
        // Create new post
        $stmt = $conn->prepare("INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $_SESSION['user_id'], $title, $content);
    }

    if ($stmt->execute()) {
        echo "<p style='color:green;'>Post successfully saved!</p>";
    } else {
        echo "<p style='color:red;'>Error saving post.</p>";
    }
}

// Handle post deletion
if (isset($_GET['delete'])) {
    $post_id = intval($_GET['delete']);

    if ($_SESSION['role'] === 'admin') {
        $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->bind_param("i", $post_id);
    } else {
        $stmt = $conn->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $post_id, $_SESSION['user_id']);
    }

    if ($stmt->execute()) {
        echo "<p style='color:green;'>Post successfully deleted!</p>";
    } else {
        echo "<p style='color:red;'>Error deleting post.</p>";
    }
}

// Fetch posts (admin sees all, users see their own)
if ($_SESSION['role'] === 'admin') {
    $result = $conn->query("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id");
} else {
    $stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body { font-family: Arial; margin: 40px; }
        form { margin-bottom: 20px; }
        label, input, textarea, button { display: block; margin-bottom: 10px; }
        button { background-color: #4CAF50; color: white; padding: 10px; border: none; cursor: pointer; }
        button:hover { background-color: #45a049; }
    </style>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['role'] === 'admin' ? 'Admin' : 'User'); ?></h1>

    <h2>Create or Edit Post</h2>
    <form method="POST">
        <input type="hidden" name="post_id" id="post_id">
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" required>
        <label for="content">Content:</label>
        <textarea id="content" name="content" required></textarea>
        <button type="submit">Save Post</button>
    </form>

    <h2>Your Posts</h2>
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div>
                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                <p><?php echo htmlspecialchars($row['content']); ?></p>
                <?php if ($_SESSION['role'] === 'admin' || $row['user_id'] == $_SESSION['user_id']): ?>
                    <button onclick="editPost(<?php echo $row['id']; ?>, '<?php echo addslashes(htmlspecialchars($row['title'])); ?>', '<?php echo addslashes(htmlspecialchars($row['content'])); ?>')">Edit</button>
                    <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                <?php endif; ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <p><strong>Posted by:</strong> <?php echo htmlspecialchars($row['username']); ?></p>
                <?php endif; ?>
            </div>
            <hr>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No posts available.</p>
    <?php endif; ?>

    <script>
        function editPost(id, title, content) {
            document.getElementById('post_id').value = id;
            document.getElementById('title').value = title;
            document.getElementById('content').value = content;
        }
    </script>
</body>
</html>
