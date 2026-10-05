<?php
/**
 * MongoDB Atlas Connection Test Script
 */

require_once __DIR__ . '/vendor/autoload.php';

// Verify that the ext-mongodb extension is loaded
if (!extension_loaded('mongodb')) {
    echo "❌ Error: The 'mongodb' PHP extension (ext-mongodb) is not loaded.\n";
    echo "Please ensure php_mongodb.dll is enabled in your php.ini.\n";
    exit(1);
}

// Load credentials from atlas-credentials.env if present
$envFile = __DIR__ . '/atlas-credentials.env';
$uri = null;

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (str_starts_with($line, '#')) continue;
        if (str_starts_with($line, 'MONGODB_URI=')) {
            $uri = trim(substr($line, strlen('MONGODB_URI=')), " \"'");
        }
    }
}

// Fallback direct URI if not in .env
if (!$uri) {
    $uri = 'mongodb+srv://kunjr1104_db_user:qOtzfSh9GdVLPM1Y@cluster0.krewsjo.mongodb.net/?retryWrites=true&w=majority';
}

echo "Connecting to MongoDB Atlas...\n";

try {
    $client = new MongoDB\Client($uri);
    $admin = $client->admin;
    $command = ['ping' => 1];
    $result = $admin->command($command)->toArray();

    echo "Response: " . json_encode($result) . "\n";
    echo "✅ Pinged your deployment. You successfully connected to MongoDB Atlas!\n";
} catch (Throwable $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
    exit(1);
}
