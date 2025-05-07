<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("HTTP/1.1 403 Forbidden");
    echo "Access Denied. Please log in.";
    exit;
}

// Ensure the necessary POST parameters are provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['new_message'])) {
    $csv_file = "chat.csv";
    $editId = $_POST['id'];
    $newMessage = trim($_POST['new_message']);

    if ($newMessage === "") {
        echo "Message cannot be empty.";
        exit;
    }

    // Read current messages from the CSV file into an array.
    $lines = [];
    if (file_exists($csv_file)) {
        if (($fp = fopen($csv_file, "r")) !== false) {
            while (($data = fgetcsv($fp)) !== false) {
                $lines[] = $data;
            }
            fclose($fp);
        }
    }

    $found = false;
    // Loop through all messages to find the one with the matching unique ID.
    for ($i = 0; $i < count($lines); $i++) {
        // The record should have 4 fields: [id, timestamp, username, message]
        if (count($lines[$i]) === 4 && $lines[$i][0] == $editId) {
            // Check if the logged-in user is the author.
            if ($lines[$i][2] == $_SESSION['username']) {
                // Check if the message was sent within the last 10 seconds.
                $messageTime = strtotime($lines[$i][1]);
                if (time() - $messageTime > 10) {
                    echo "You can only edit messages sent within the last 10 seconds.";
                    exit;
                }
                // Update the message text.
                $lines[$i][3] = $newMessage;
                $found = true;
                break;
            } else {
                echo "Cannot edit a message that is not yours.";
                exit;
            }
        }
    }

    if ($found) {
        // Re-open the CSV file for writing and update it with the modified records.
        if (($fp = fopen($csv_file, "w")) !== false) {
            // Lock the file to prevent concurrent writes.
            flock($fp, LOCK_EX);
            foreach ($lines as $line) {
                fputcsv($fp, $line);
            }
            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);
            echo "Message updated.";
        } else {
            echo "Error updating CSV file.";
        }
    } else {
        echo "Message ID not found.";
    }
} else {
    echo "Invalid request.";
}
?>
