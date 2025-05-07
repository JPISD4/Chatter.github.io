<?php
session_start();
if (!isset($_SESSION['username'])) {
    exit("Access Denied. Please log in.");
}

$csv_file = "chat.csv";
$messages = [];

if (file_exists($csv_file)) {
    if (($fp = fopen($csv_file, "r")) !== false) {
        while (($data = fgetcsv($fp)) !== false) {
            $messages[] = $data;
        }
        fclose($fp);
    }
}

if (count($messages) > 0) {
    foreach ($messages as $msg) {
        // Check if message has 4 fields (id, time, user, text)
        if (count($msg) === 4) {
            list($msgId, $time, $user, $text) = $msg;
        } else {
            $msgId = "";
            list($time, $user, $text) = $msg;
        }
        echo '<div class="message">';
        echo '<div class="timestamp">' . htmlspecialchars($time) . '</div>';
        echo '<strong>' . htmlspecialchars($user) . ':</strong> ';
        echo '<span>' . nl2br(htmlspecialchars($text)) . '</span>';
        // If the message belongs to the current user, add an Edit link.
        if ($user == $_SESSION['username'] && $msgId != "") {
            echo ' <a href="javascript:void(0);" onclick=\'editMessage("' . $msgId . '", ' . json_encode($text) . ')\'>Edit</a>';
        }
        echo '</div>';
    }
} else {
    echo '<p class="text-muted">No messages yet. Start chatting!</p>';
}
?>
