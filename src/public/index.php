<?php
require '../../vendor/autoload.php';

$results = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['urls'])) {
    // Tách URLs thành mảng
    $urls = array_filter(
        array_map('trim', explode("\n", $_POST['urls'])),
        function($url) { return filter_var($url, FILTER_VALIDATE_URL); }
    );

    if (!empty($urls)) {
        $scanner = new \Khangnhd\ScannerUrl\Scanner($urls);
        $results = $scanner->scan();
    }
}

require '../views/index.php'; 