<?php

// Function to read and parse the .env file
function loadEnv($filePath)
{
    if (!file_exists($filePath) || !is_readable($filePath)) {
        throw new RuntimeException("Error: .env file not found or not readable at path: " . $filePath);
    }

    $envVars = [];
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, "\"'");

        $envVars[$key] = $value;
        if (!isset($_ENV[$key]) && !isset($_SERVER[$key])) {
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("$key=$value");
        }
    }

    return $envVars; // Return the custom array
}

// Specify the path to your .env file (usually in the same directory as the script)
// $envFilePath = __DIR__ . '/.env';

try {
    $envFilePath = __DIR__ . '/.env';
    loadEnv($envFilePath);
    // ... rest of your code
} catch (RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
// Now you can access the environment variables using $_ENV, $_SERVER, or getenv()

// Example usage:
// echo "Example Environment Variables:\n";
// echo "APP_NAME: " . ($_ENV['APP_NAME'] ?? 'Not set') . "\n";
// echo "DATABASE_HOST: " . ($_ENV['DATABASE_HOST'] ?? 'Not set') . "\n";
// ... access other variables as needed
