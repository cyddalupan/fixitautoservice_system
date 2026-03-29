<?php
// Simple test page - no Laravel, no framework
echo "TEST PAGE WORKING!<br>";
echo "Time: " . date('Y-m-d H:i:s') . "<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";

// Test if we can include Laravel files
echo "<hr><h3>Laravel Test:</h3>";
$laravelPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($laravelPath)) {
    echo "Laravel autoloader exists.<br>";
    
    // Try to load AppointmentController
    $controllerPath = __DIR__ . '/../app/Http/Controllers/AppointmentController.php';
    if (file_exists($controllerPath)) {
        echo "AppointmentController.php exists.<br>";
        
        // Check syntax
        $output = shell_exec('php -l ' . escapeshellarg($controllerPath) . ' 2>&1');
        echo "Syntax check: " . htmlspecialchars($output) . "<br>";
    } else {
        echo "ERROR: AppointmentController.php NOT FOUND!<br>";
    }
} else {
    echo "ERROR: Laravel autoloader NOT FOUND!<br>";
}
