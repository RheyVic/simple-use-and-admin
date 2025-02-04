<?php
session_start();
if ($_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "website";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM content";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Welcome to the User Dashboard</h1>
    
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="content">
            <h2><?php echo $row['title']; ?></h2>
            <p><?php echo $row['description']; ?></p>
            <?php if (filter_var($row['image'], FILTER_VALIDATE_URL)): ?>
                <img src="<?php echo $row['image']; ?>" alt="Content Image">
            <?php else: ?>
                <img src="<?php echo $row['image']; ?>" alt="Content Image" width="200px">
            <?php endif; ?>
        </div>
    <?php endwhile; ?>

    <a href="logout.php">Logout</a>
</body>
</html>

<?php
$conn->close();
?>