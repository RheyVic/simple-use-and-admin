<?php
session_start();

// Redirect to login page if not logged in or not an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "website";
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle content creation (Add)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];

    // Upload image or use URL
    if (!empty($_FILES['image']['name'])) {
        $image_name = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_type = $_FILES['image']['type'];
        $image_error = $_FILES['image']['error'];
        $upload_dir = 'uploads/';
        $image_path = $upload_dir . basename($image_name);

        if (move_uploaded_file($image_tmp, $image_path)) {
            $image_url = $image_path;
        } else {
            echo "Error uploading file!";
        }
    } elseif (!empty($_POST['image_url'])) {
        $image_url = $_POST['image_url'];  // If URL is provided
    } else {
        $image_url = null;
    }

    if ($_POST['action'] == 'add') {
        $sql = "INSERT INTO content (title, description, image) VALUES ('$title', '$description', '$image_url')";
        if ($conn->query($sql) === TRUE) {
            echo "New content added successfully!";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    // Handle content update (Edit)
    if ($_POST['action'] == 'update') {
        $id = $_POST['id'];
        $sql = "UPDATE content SET title='$title', description='$description', image='$image_url' WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            echo "Content updated successfully!";
        } else {
            echo "Error: " . $conn->error;
        }
    }
}

// Handle content deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql = "DELETE FROM content WHERE id=$delete_id";
    if ($conn->query($sql) === TRUE) {
        echo "Content deleted successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}

// Fetch all content
$sql = "SELECT * FROM content";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Admin Dashboard</h1>

    <!-- Logout Button -->
    <a href="logout.php" style="padding: 10px; background-color: red; color: white; text-decoration: none;">Logout</a>

    <!-- Add Content Form -->
    <h2>Add Content</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        <label for="title">Title:</label>
        <input type="text" name="title" required><br><br>
        
        <label for="description">Description:</label><br>
        <textarea name="description" rows="4" cols="50" required></textarea><br><br>
        
        <label for="image">Upload Image:</label>
        <input type="file" name="image"><br><br>

        <label for="image_url">Or provide Image URL:</label>
        <input type="text" name="image_url"><br><br>

        <button type="submit">Add Content</button>
    </form>

    <h2>Manage Content</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Description</th>
            <th>Image</th>
            <th>Actions</th>
        </tr>

        <?php
        // Display content
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['title']}</td>
                    <td>{$row['description']}</td>
                    <td><img src='{$row['image']}' alt='Image' width='100'></td>
                    <td>
                        <a href='admin_dashboard.php?edit_id={$row['id']}'>Edit</a> | 
                        <a href='admin_dashboard.php?delete_id={$row['id']}'>Delete</a>
                    </td>
                </tr>";
        }
        ?>
    </table>

    <?php
    // Edit Content Logic
    if (isset($_GET['edit_id'])) {
        $edit_id = $_GET['edit_id'];
        $sql = "SELECT * FROM content WHERE id=$edit_id";
        $edit_result = $conn->query($sql);
        $edit_row = $edit_result->fetch_assoc();
    ?>

        <h2>Edit Content</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?php echo $edit_row['id']; ?>">
            
            <label for="title">Title:</label>
            <input type="text" name="title" value="<?php echo $edit_row['title']; ?>" required><br><br>

            <label for="description">Description:</label><br>
            <textarea name="description" rows="4" cols="50" required><?php echo $edit_row['description']; ?></textarea><br><br>

            <label for="image">Upload Image:</label>
            <input type="file" name="image"><br><br>

            <label for="image_url">Or provide Image URL:</label>
            <input type="text" name="image_url" value="<?php echo $edit_row['image']; ?>"><br><br>

            <button type="submit">Update Content</button>
        </form>

    <?php
    }
    ?>

</body>
</html>

<?php $conn->close(); ?>