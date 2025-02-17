<?php 
require '../vendor/autoload.php';

$urls = [
    'https://www.apple.com/',
    'http://php.net',
    'https://laracasts.com/'
];

$scanner = new \Khangnhd\ScannerUrl\Scanner($urls);
$results = $scanner->scan();

// In kết quả đẹp hơn
echo "URL Scanning Results:\n";
foreach ($results as $url => $data) {
    echo "\n=== $url ===\n";
    
    echo "\nSSL Information:\n";
    print_r($data['ssl_info']);
    
    echo "\nResponse Information:\n";
    print_r($data['response_info']);
    
    echo "\nContent Analysis:\n";
    echo "\n- Meta Tags:\n";
    print_r($data['content_analysis']['meta_tags']);
    
    echo "\n- Security Issues:\n";
    print_r($data['content_analysis']['security_checks']);
    
    echo "\n- Content Safety Issues:\n";
    print_r($data['content_analysis']['content_safety']);
    
    echo "\n- Malware Indicators:\n";
    print_r($data['content_analysis']['malware_indicators']);
    
    echo "\n-------------------\n";
}