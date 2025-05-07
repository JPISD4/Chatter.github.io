<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("HTTP/1.1 403 Forbidden");
    echo "Access Denied. Please log in.";
    exit;
}

$csvFile = "status.csv";

// Get the typing parameter; default to "0" (not typing)
$typing = (isset($_POST['typing']) && $_POST['typing'] === "1") ? "1" : "0";
$lastActive = time();
$username = $_SESSION['username'];

// Read existing status records, keyed by username.
$statusRecords = [];
if (file_exists($csvFile)) {
    if (($fp = fopen($csvFile, "r")) !== false) {
        while (($data = fgetcsv($fp)) !== false) {
            if (count($data) >= 3) {
                $statusRecords[$data[0]] = [
                    'lastActive' => $data[1],
                    'typing'     => $data[2]
                ];
            }
        }
        fclose($fp);
    }
}

// Update current user's record.
$statusRecords[$username] = [
    'lastActive' => $lastActive,
    'typing'     => $typing
];

// Write all records back to the CSV.
if (($fp = fopen($csvFile, "w")) !== false) {
    flock($fp, LOCK_EX);
    foreach ($statusRecords as $user => $record) {
        fputcsv($fp, [$user, $record['lastActive'], $record['typing']]);
    }
    flock($fp, LOCK_UN);
    fclose($fp);
}

echo "Status updated";
?>
