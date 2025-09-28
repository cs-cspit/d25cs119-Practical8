<?php
// --- MySQL connection settings ---
$servername = "localhost";
$username = "root";  // change if needed
$password = "";      // change if needed
$dbname = "portal_db";

// --- Connect to MySQL server ---
$conn = new mysqli($servername, $username, "", ""); // connect without db first

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Create database if not exists ---
$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn->select_db($dbname);

// --- Create events table if not exists ---
$create_table = "
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($create_table);

// --- Handle Insert ---
$msg = "";
if (isset($_POST['add_event'])) {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $date = $_POST['event_date'];

    $stmt = $conn->prepare("INSERT INTO events (title, description, event_date) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $desc, $date);

    if ($stmt->execute()) {
        $msg = "Event added successfully!";
    } else {
        $msg = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// --- Handle Update ---
if (isset($_POST['update_event'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $date = $_POST['event_date'];

    $stmt = $conn->prepare("UPDATE events SET title=?, description=?, event_date=? WHERE id=?");
    $stmt->bind_param("sssi", $title, $desc, $date, $id);

    if ($stmt->execute()) {
        $msg = "Event updated successfully!";
    } else {
        $msg = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// --- Fetch latest 5 events ---
$events = [];
$result = $conn->query("SELECT * FROM events ORDER BY created_at DESC LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
} else {
    $msg = "Error fetching events: " . $conn->error;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Event Dashboard</title>
<style>
    body { font-family: Arial, sans-serif; background: #f0f8ff; padding: 20px; }
    h2 { color: #333; }
    .form-container, .events-container { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 20px; }
    input, textarea, button { width: 100%; padding: 10px; margin: 8px 0; border-radius: 6px; border: 1px solid #ccc; font-size: 14px; }
    button { background: #87cefa; color: #fff; border: none; cursor: pointer; transition: 0.3s; }
    button:hover { background: #00bfff; }
    .msg { color: green; margin: 10px 0; }
    .event-item { border-bottom: 1px solid #eee; padding: 10px 0; }
    .event-item:last-child { border: none; }
</style>
</head>
<body>

<h2>Event Dashboard</h2>

<?php if($msg) echo "<p class='msg'>$msg</p>"; ?>

<div class="form-container">
    <h3>Add Event</h3>
    <form method="post">
        <input type="text" name="title" placeholder="Event Title" required>
        <textarea name="description" placeholder="Event Description" rows="3"></textarea>
        <input type="date" name="event_date" required>
        <button type="submit" name="add_event">Add Event</button>
    </form>
</div>

<div class="events-container">
    <h3>Latest 5 Events</h3>
    <?php if(count($events) > 0): ?>
        <?php foreach($events as $event): ?>
            <div class="event-item">
                <strong><?= htmlspecialchars($event['title']); ?></strong> (<?= $event['event_date']; ?>)<br>
                <?= htmlspecialchars($event['description']); ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No events found.</p>
    <?php endif; ?>
</div>

</body>
</html>
