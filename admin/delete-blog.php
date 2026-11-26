<?php
include 'config.php';

if (isset($_GET['id'])) {
    $postId = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM blogs WHERE id = ?");
    $stmt->bind_param("i", $postId);

    if ($stmt->execute()) {
      echo "<script>
      alert('Post Deleted successfully.');
      window.location.href = 'blog.php';
    </script>";

    } else {
        echo "<script>
                alert('Failed to Deleted post.');
              </script>";
    }
    $stmt->close();
}

// header("Location: index.php");
// exit();
?>
