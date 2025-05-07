<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("HTTP/1.1 403 Forbidden");
    echo "Access Denied. Please log in.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $csv_file = "chat.csv";
    $username = $_SESSION['username'];
    $message = trim($_POST['message']);
    if ($message !== "") {
        $time = date("Y-m-d H:i:s");
        $msgId = uniqid(); // generate a unique message ID
        $data = [$msgId, $time, $username, $message];
        if (($fp = fopen($csv_file, "a")) !== false) {
            flock($fp, LOCK_EX);
            fputcsv($fp, $data);
            flock($fp, LOCK_UN);
            fclose($fp);
            echo "Message sent.";
        } else {
            echo "Error opening CSV file.";
        }
    } else {
        echo "Message is empty.";
    }
} else {
    echo "Invalid request.";
}
?>
