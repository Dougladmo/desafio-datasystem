<?php

require __DIR__ . '/vendor/autoload.php';

// Define constants
define('ROOTPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
define('SYSTEMPATH', __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'codeigniter4' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
define('APPPATH', __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR);
define('WRITEPATH', __DIR__ . DIRECTORY_SEPARATOR . 'writable' . DIRECTORY_SEPARATOR);

echo "ROOTPATH: " . ROOTPATH . "\n";
echo "WRITEPATH: " . WRITEPATH . "\n";

$dbPath = ROOTPATH . 'writable/database/propostas.db';
echo "Database path: " . $dbPath . "\n";
echo "File exists: " . (file_exists($dbPath) ? 'YES' : 'NO') . "\n";
echo "File readable: " . (is_readable($dbPath) ? 'YES' : 'NO') . "\n";
echo "File writable: " . (is_writable($dbPath) ? 'YES' : 'NO') . "\n";
echo "Dir writable: " . (is_writable(dirname($dbPath)) ? 'YES' : 'NO') . "\n";

// Try to connect
try {
    $db = new SQLite3($dbPath);
    echo "\nConnection: SUCCESS\n";
    $db->close();
} catch (Exception $e) {
    echo "\nConnection: FAILED\n";
    echo "Error: " . $e->getMessage() . "\n";
}
