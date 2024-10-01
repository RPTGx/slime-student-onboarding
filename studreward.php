<?php
session_start(); // Start the session

// Function to check if the user is using a mobile device
function isMobile() {
    $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $mobileAgents = [
        'iphone', 'ipod', 'ipad', 'android', 'blackberry', 'webos', 'windows phone', 'opera mini', 'iemobile', 'mobile'
    ];

    foreach ($mobileAgents as $agent) {
        if (strpos($userAgent, $agent) !== false) {
            return true;
        }
    }
    return false;
}

// Function to load URLs from a file
function loadUrlsFromFile($filename) {
    $urls = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return $urls ? array_map('trim', $urls) : [];
}

// Function to read CSV file
function readCsv($filename) {
    $rows = [];
    if (($handle = fopen($filename, 'r')) !== false) {
        while (($data = fgetcsv($handle)) !== false) {
            $rows[] = $data[0];
        }
        fclose($handle);
    }
    return $rows;
}

// Function to write to CSV file
function writeCsv($filename, $data) {
    if (($handle = fopen($filename, 'a')) !== false) {
        foreach ($data as $row) {
            fputcsv($handle, [$row]);
        }
        fclose($handle);
    }
}

// Define file paths for URLs
$desktopUrlsFile = 'student_desktop_urls.txt';
$mobileUrlsFile = 'student_mobile_urls.txt';

// Determine which set of URLs to use based on device type
$urls = isMobile() ? loadUrlsFromFile($mobileUrlsFile) : loadUrlsFromFile($desktopUrlsFile);

// File to keep track of the current index
$indexFile = 'index_student.txt';

// File to keep track of visited URLs (CSV)
$visitedUrlsFile = 'visited_urls_student.csv';

// File to keep track of visited devices
$devicesFile = 'devicesv_student.txt';

// Initialize or read the current index
if (!file_exists($indexFile)) {
    file_put_contents($indexFile, '0'); // Start from the first URL
}
$index = (int)file_get_contents($indexFile);

// Check for cookies to identify unique devices
$deviceId = isset($_COOKIE['visited']) ? $_COOKIE['visited'] : null;

// Read visited URLs from CSV
$visitedUrls = readCsv($visitedUrlsFile);

// Read visited devices
$visitedDevices = file_exists($devicesFile) ? json_decode(file_get_contents($devicesFile), true) : [];

// Determine the previously visited URL
$previousUrl = end($visitedUrls) ?: 'None';

// If the device has already visited, show a message with the previous URL
if ($deviceId && in_array($deviceId, $visitedDevices)) {
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reward already claimed</title>
	<link rel="stylesheet" href="alclaim.css">
</head>
<body>
    <div class="message">
        <h1>You already claimed the reward </h1>
        <span class="claim-link">Claim link: <a href="' . htmlspecialchars($previousUrl) . '" target="_blank">' . htmlspecialchars($previousUrl) . '</a></span>
        <p>if you have not claim the $slime yet just click the link below. Make sure to open it using chrome browser.</p>
        <div class="button">
            <a href="https://t.me/+zAE0b42PP3MzZTZl" target="_blank">Join our Telegram</a>
        </div>
    </div>
</body>
</html>';
    exit();
}

// Check if the current URL has already been visited
$currentUrl = $urls[$index];
if (in_array($currentUrl, $visitedUrls)) {
    // Move to the next URL if the current one is already visited
    $index = ($index + 1) % count($urls);
    $currentUrl = $urls[$index];
    if (in_array($currentUrl, $visitedUrls)) {
        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>No More Claims</title>
    <link rel="stylesheet" href="noclaim.css">
</head>
<body>
    <div class="container">
        <div class="message">All rewards have been claimed</div>
        <a href="https://t.me/+zAE0b42PP3MzZTZl" class="cta-button">Join SLIME Telegram</a>
    </div>
</body>
</html>';
        exit();
    }
}

// Redirect to the current URL
header("Location: " . $currentUrl);

// Update the visited URLs list
writeCsv($visitedUrlsFile, [$currentUrl]);

// Update the index for the next visit
$index = ($index + 1) % count($urls);
file_put_contents($indexFile, $index);

// Mark the device as visited
if (!$deviceId) {
    // Generate a unique ID for the device
    $deviceId = uniqid();
    setcookie('visited', $deviceId, time() + 3600 * 24 * 30); // Cookie lasts for 30 days
}
$visitedDevices[] = $deviceId;
file_put_contents($devicesFile, json_encode($visitedDevices));

exit();
?>
