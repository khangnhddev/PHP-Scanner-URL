<?php 
require '../vendor/autoload.php';

$urls = [
    'https://www.apple.com/',
    'http://php.net',
    'https://laracasts.com/'
];

$scanner = new \Khangnhd\ScannerUrl\Scanner($urls);
print_r($scanner->getInvalidUrls());