<?php

namespace Khangnhd\ScannerUrl;

class Scanner
{
    /**
     * @var array An array of URLs
     */
    protected $urls;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    private $timeout = 10;
    private $results = [];

    /**
     * Constructor
     * @param array $urls An array of URLs to scan
     */
    public function __construct(array $urls)
    {
        $this->urls = $urls;
        $this->httpClient = new \GuzzleHttp\Client();
    }

    public function scan()
    {
        foreach ($this->urls as $url) {
            $this->results[$url] = $this->analyzeUrl($url);
        }
        return $this->results;
    }

    private function analyzeUrl($url)
    {
        $result = [
            'ssl_info' => $this->checkSSL($url),
            'response_info' => $this->checkResponse($url),
            'content_analysis' => $this->analyzeContent($url)
        ];
        return $result;
    }

    private function analyzeContent($url)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $html = curl_exec($ch);
        curl_close($ch);

        if (!$html) {
            return ['error' => 'Could not fetch content'];
        }

        // Tạo DOM Document
        $doc = new \DOMDocument();
        @$doc->loadHTML($html, LIBXML_NOERROR);
        $xpath = new \DOMXPath($doc);

        return [
            'meta_tags' => $this->analyzeMeta($xpath),
            'security_checks' => $this->checkSecurity($html, $xpath),
            'content_safety' => $this->checkContentSafety($html),
            'malware_indicators' => $this->checkMalwareIndicators($html, $xpath)
        ];
    }

    private function analyzeMeta($xpath)
    {
        $meta_info = [];
        
        // Lấy tất cả meta tags
        $meta_tags = $xpath->query('//meta');
        foreach ($meta_tags as $tag) {
            $name = $tag->getAttribute('name') ?: $tag->getAttribute('property');
            if ($name) {
                $meta_info[$name] = $tag->getAttribute('content');
            }
        }

        // Lấy title
        $title = $xpath->query('//title');
        if ($title->length > 0) {
            $meta_info['title'] = $title->item(0)->nodeValue;
        }

        return $meta_info;
    }

    private function checkSecurity($html, $xpath)
    {
        $security_issues = [];

        // Kiểm tra các forms
        $forms = $xpath->query('//form');
        foreach ($forms as $form) {
            if ($form->getAttribute('action') && !str_contains($form->getAttribute('action'), 'https://')) {
                $security_issues[] = 'Insecure form action detected (non-HTTPS)';
            }
        }

        // Kiểm tra các dấu hiệu phishing
        $phishing_keywords = ['login', 'password', 'credit card', 'bank', 'account'];
        foreach ($phishing_keywords as $keyword) {
            if (stripos($html, $keyword) !== false) {
                $security_issues[] = "Potential phishing keyword found: $keyword";
            }
        }

        // Kiểm tra external scripts
        $scripts = $xpath->query('//script[@src]');
        foreach ($scripts as $script) {
            $src = $script->getAttribute('src');
            if (!empty($src) && !str_starts_with($src, '/') && !str_starts_with($src, 'https://')) {
                $security_issues[] = 'Insecure external script detected';
            }
        }

        return $security_issues;
    }

    private function checkContentSafety($html)
    {
        $issues = [];
        
        // Danh sách từ khóa không phù hợp
        $inappropriate_keywords = [
            'violence' => ['violence', 'weapon', 'kill'],
            'adult_content' => ['xxx', 'adult', 'nsfw'],
            'hate_speech' => ['hate', 'racist', 'discrimination']
        ];

        foreach ($inappropriate_keywords as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($html, $keyword) !== false) {
                    $issues[] = "Inappropriate content detected: $category";
                    break;
                }
            }
        }

        return $issues;
    }

    private function checkMalwareIndicators($html, $xpath)
    {
        $indicators = [];

        // Kiểm tra mã độc hại trong JavaScript
        $suspicious_patterns = [
            'eval\(', 
            'document\.write\(',
            'fromCharCode',
            'escape\(',
            'unescape\(',
            'decrypt',
            'encode\(',
            'decode\('
        ];

        foreach ($suspicious_patterns as $pattern) {
            if (preg_match("/$pattern/i", $html)) {
                $indicators[] = "Suspicious JavaScript pattern found: $pattern";
            }
        }

        // Kiểm tra các liên kết đáng ngờ
        $links = $xpath->query('//a[@href]');
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            if (preg_match('/\.(exe|php\?|cgi\?)$/i', $href)) {
                $indicators[] = "Suspicious link detected: $href";
            }
        }

        // Kiểm tra iframe ẩn
        $iframes = $xpath->query('//iframe[@style]');
        foreach ($iframes as $iframe) {
            $style = $iframe->getAttribute('style');
            if (strpos($style, 'display: none') !== false || strpos($style, 'visibility: hidden') !== false) {
                $indicators[] = 'Hidden iframe detected';
            }
        }

        return $indicators;
    }

    private function checkSSL($url)
    {
        $parsed = parse_url($url);
        if (!isset($parsed['host'])) {
            return ['error' => 'Invalid URL format'];
        }

        $ssl_info = [];
        $stream = @stream_context_create([
            'ssl' => [
                'capture_peer_cert' => true,
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);

        $socket = @stream_socket_client(
            "ssl://{$parsed['host']}:443", 
            $errno, 
            $errstr, 
            $this->timeout, 
            STREAM_CLIENT_CONNECT, 
            $stream
        );

        if ($socket) {
            $cert_data = stream_context_get_params($socket);
            if (isset($cert_data['options']['ssl']['peer_certificate'])) {
                $cert = openssl_x509_parse($cert_data['options']['ssl']['peer_certificate']);
                $ssl_info = [
                    'valid' => true,
                    'expires' => date('Y-m-d H:i:s', $cert['validTo_time_t']),
                    'issuer' => $cert['issuer']['CN'] ?? 'Unknown',
                    'version' => $cert['version'] ?? 'Unknown'
                ];
            }
            fclose($socket);
        } else {
            $ssl_info = [
                'valid' => false,
                'error' => "SSL connection failed: $errstr ($errno)"
            ];
        }

        return $ssl_info;
    }

    private function checkResponse($url)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        
        $result = [
            'status_code' => $info['http_code'],
            'response_time' => $info['total_time'],
            'content_type' => $info['content_type'] ?? 'Unknown',
            'redirect_count' => $info['redirect_count'],
            'redirect_url' => $info['redirect_url'] ?: null,
        ];

        curl_close($ch);
        return $result;
    }

    public function getResults()
    {
        return $this->results;
    }

    /**
     * Get invalid URLs
     * @return array
     */
    public function getInvalidUrls()
    {
        $invalidUrls = [];
        foreach ($this->urls as $url) {
            try {
                $statusCode = $this->getStatusCodeForUrl($url);
            } catch (\Exception $e) {
                $statusCode = 500;
                print_r($e->getMessage() . PHP_EOL);
            }

            if ($statusCode >= 400) {
                array_push($invalidUrls, [
                    'url' => $url,
                    'status' => $statusCode
                ]);
            }
        }

        return $invalidUrls;
    }

    /**
     * Get HTTP status code for URL
     * @param string $url The remote URL
     * @return int The HTTP status code
     */
    protected function getStatusCodeForUrl($url)
    {
        $httpResponse = $this->httpClient->get($url);
        return $httpResponse->getStatusCode();
    }
}
