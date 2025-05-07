<?php
session_start();
if (!isset($_SESSION['username'])) {
    exit("Access Denied. Please log in.");
}

$csvFile = "status.csv";
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

$output = "";
$currentTime = time();
$onlineThreshold = 10; // seconds

foreach ($statusRecords as $user => $record) {
    $lastActive = $record['lastActive'];
    $typing = $record['typing'];
    if (($currentTime - $lastActive) <= $onlineThreshold) {
        if ($typing === "1") {
            $output .= "<p><strong>" . htmlspecialchars($user) . "</strong> is typing...</p>";
        } else {
            $output .= "<p><strong>" . htmlspecialchars($user) . "</strong> is online</p>";
        }
    } else {
        $output .= "<p><strong>" . htmlspecialchars($user) . "</strong> is offline</p>";
    }
}
echo $output;
?>
