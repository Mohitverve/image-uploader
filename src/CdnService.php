<?php
namespace App;

class CdnService
{
    public static function upload(string $localFile, string $remotePath): string
    {
        $endpoint = rtrim($_ENV['CDN_ENDPOINT'], '/');
        $bucket   = $_ENV['CDN_BUCKET'];
        $key      = $_ENV['CDN_KEY'];
        $secret   = $_ENV['CDN_SECRET'];

        $url = "{$endpoint}/{$bucket}/{$remotePath}";
        $contentType = "image/webp";
        $date = gmdate('D, d M Y H:i:s T');

        $stringToSign = "PUT\n\n{$contentType}\n{$date}\n/{$bucket}/{$remotePath}";
        $signature = base64_encode(
            hash_hmac('sha1', $stringToSign, $secret, true)
        );

        $headers = [
            "Date: {$date}",
            "Content-Type: {$contentType}",
            "Authorization: AWS {$key}:{$signature}"
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_PUT => true,
            CURLOPT_INFILE => fopen($localFile, 'rb'),
            CURLOPT_INFILESIZE => filesize($localFile),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status !== 200 && $status !== 201) {
            throw new \Exception("CDN upload failed ({$status})");
        }

        return rtrim($_ENV['CDN_PUBLIC_URL'], '/') . '/' . $remotePath;
    }
}
