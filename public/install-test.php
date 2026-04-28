<?php
require_once __DIR__.'/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;

echo "<h1>Fixit Auto Services Installation Test</h1>";
echo "<p>Testing database connection...</p>";

try {
    DB::connection()->getPdo();
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    $tables = DB::select('SHOW TABLES');
    echo "<p>Tables found: " . count($tables) . "</p>";
    
    echo "<p>Testing application key...</p>";
    $key = config('app.key');
    echo "<p>App Key: " . substr($key, 0, 20) . "...</p>";
    
    echo "<p style='color: green;'>✅ Installation successful!</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
