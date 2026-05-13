<?php
/**
 * Database Update Script
 * Adds password reset functionality columns to the users table
 * Run this script once to update your database
 */

require_once 'config/config.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Update - " . SITE_NAME . "</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-gray-50'>
    <div class='min-h-screen flex items-center justify-center p-6'>
        <div class='max-w-2xl w-full bg-white rounded-lg shadow-lg p-8'>";

echo "<h1 class='text-3xl font-bold text-gray-800 mb-6'>
        <i class='fas fa-database text-purple-600'></i> Database Update
      </h1>";

try {
    $db = getDB();
    
    echo "<div class='space-y-4'>";
    
    // Check if columns already exist
    echo "<p class='text-gray-700'>Checking database structure...</p>";
    
    $checkStmt = $db->query("SHOW COLUMNS FROM users LIKE 'reset_token'");
    $hasResetToken = $checkStmt->fetch();
    
    $checkStmt2 = $db->query("SHOW COLUMNS FROM users LIKE 'reset_token_expiry'");
    $hasResetTokenExpiry = $checkStmt2->fetch();
    
    if ($hasResetToken && $hasResetTokenExpiry) {
        echo "<div class='bg-blue-50 border-l-4 border-blue-500 p-4 rounded'>
                <p class='text-blue-700'>
                    <i class='fas fa-info-circle mr-2'></i>
                    Database is already up to date! Password reset columns exist.
                </p>
              </div>";
    } else {
        echo "<p class='text-gray-700 mb-4'>Adding password reset columns...</p>";
        
        // Add reset_token column if it doesn't exist
        if (!$hasResetToken) {
            $db->exec("ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL DEFAULT NULL AFTER last_login");
            echo "<div class='bg-green-50 border-l-4 border-green-500 p-4 rounded mb-3'>
                    <p class='text-green-700'>
                        <i class='fas fa-check-circle mr-2'></i>
                        Added 'reset_token' column to users table
                    </p>
                  </div>";
        }
        
        // Add reset_token_expiry column if it doesn't exist
        if (!$hasResetTokenExpiry) {
            $db->exec("ALTER TABLE users ADD COLUMN reset_token_expiry DATETIME NULL DEFAULT NULL AFTER reset_token");
            echo "<div class='bg-green-50 border-l-4 border-green-500 p-4 rounded mb-3'>
                    <p class='text-green-700'>
                        <i class='fas fa-check-circle mr-2'></i>
                        Added 'reset_token_expiry' column to users table
                    </p>
                  </div>";
        }
        
        // Add index for better performance
        try {
            $db->exec("ALTER TABLE users ADD INDEX idx_reset_token (reset_token)");
            echo "<div class='bg-green-50 border-l-4 border-green-500 p-4 rounded mb-3'>
                    <p class='text-green-700'>
                        <i class='fas fa-check-circle mr-2'></i>
                        Added index on 'reset_token' column
                    </p>
                  </div>";
        } catch (PDOException $e) {
            // Index might already exist, ignore error
            if (strpos($e->getMessage(), 'Duplicate key name') === false) {
                throw $e;
            }
        }
        
        echo "<div class='bg-green-100 border border-green-500 rounded-lg p-6 mt-6'>
                <h3 class='text-xl font-bold text-green-800 mb-2'>
                    <i class='fas fa-check-double mr-2'></i>
                    Update Completed Successfully!
                </h3>
                <p class='text-green-700'>
                    The database has been updated with password reset functionality.
                    You can now use the forgot password feature.
                </p>
              </div>";
    }
    
    echo "</div>";
    
    echo "<div class='mt-8 flex gap-4'>
            <a href='index.php' class='flex-1 bg-purple-600 text-white text-center px-6 py-3 rounded-lg font-medium hover:bg-purple-700 transition'>
                <i class='fas fa-home mr-2'></i>Go to Homepage
            </a>
            <a href='auth/login.php' class='flex-1 bg-gray-200 text-gray-800 text-center px-6 py-3 rounded-lg font-medium hover:bg-gray-300 transition'>
                <i class='fas fa-sign-in-alt mr-2'></i>Go to Login
            </a>
          </div>";
    
} catch (PDOException $e) {
    echo "<div class='bg-red-50 border-l-4 border-red-500 p-4 rounded'>
            <h3 class='text-lg font-bold text-red-800 mb-2'>
                <i class='fas fa-exclamation-circle mr-2'></i>Error
            </h3>
            <p class='text-red-700'>" . htmlspecialchars($e->getMessage()) . "</p>
          </div>";
    
    echo "<div class='mt-6'>
            <a href='index.php' class='inline-block bg-purple-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-purple-700 transition'>
                <i class='fas fa-arrow-left mr-2'></i>Go Back
            </a>
          </div>";
}

echo "    </div>
    </div>
</body>
</html>";
?>
