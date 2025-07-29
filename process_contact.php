<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data and sanitize it
    $name = htmlspecialchars(strip_tags(trim($_POST['name'])));
    $email = htmlspecialchars(strip_tags(trim($_POST['email'])));
    $subject = htmlspecialchars(strip_tags(trim($_POST['subject'])));
    $message_content = htmlspecialchars(strip_tags(trim($_POST['message']))); // Renamed to avoid conflict with $message object if using PHPMailer later

    // Basic validation
    if (empty($name) || empty($email) || empty($message_content) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Please fill all required fields and provide a valid email address.";
        exit;
    }

    // --- SQLite Database Setup ---
    $db_file = 'contacts.sqlite'; // Name of your SQLite database file
    $table_name = 'messages';

    try {
        // Create a new SQLite3 database or open an existing one
        $db = new SQLite3($db_file);

        // Create the messages table if it doesn't exist
        // Note: Using IF NOT EXISTS makes this script safe to run multiple times
        $db->exec("CREATE TABLE IF NOT EXISTS $table_name (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            subject TEXT,
            message_content TEXT NOT NULL,
            submission_date DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Prepare the INSERT statement
        $stmt = $db->prepare("INSERT INTO $table_name (name, email, subject, message_content) VALUES (:name, :email, :subject, :message_content)");

        // Bind parameters to prevent SQL injection
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':subject', $subject, SQLITE3_TEXT);
        $stmt->bindValue(':message_content', $message_content, SQLITE3_TEXT);

        // Execute the statement
        $result = $stmt->execute();

        if ($result) {
            echo "Thank you! Your message has been saved successfully.";
            // Optionally, redirect to a success page
            // header("Location: contact.php?status=success");
            // exit;
        } else {
            echo "Oops! Something went wrong and we couldn't save your message.";
            // You might want to log $db->lastErrorMsg() for debugging
        }

        // Close the database connection
        $db->close();

    } catch (Exception $e) {
        echo "Error connecting to or interacting with the database: " . $e->getMessage();
        // For debugging, you might log the full exception: error_log($e->getMessage());
    }

} else {
    // Not a POST request, redirect back to the contact form or show an error
    header("Location: contact.php"); // Redirect to the contact page
    exit;
}
?>