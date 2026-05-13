<?php
require_once '../config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';
$validToken = false;
$email = '';

// Verify token
if (isset($_GET['token']) && isset($_GET['email'])) {
    $token = $_GET['token'];
    $email = $_GET['email'];
    
    try {
        $db = getDB();
        
        // Check if token is valid and not expired
        $stmt = $db->prepare("
            SELECT user_id, email 
            FROM users 
            WHERE email = ? 
            AND reset_token = ? 
            AND reset_token_expiry > NOW()
        ");
        $stmt->execute([$email, $token]);
        $user = $stmt->fetch();
        
        if ($user) {
            $validToken = true;
        } else {
            $error = 'Invalid or expired reset link. Please request a new password reset.';
        }
    } catch (PDOException $e) {
        $error = 'An error occurred. Please try again later.';
    }
} else {
    $error = 'Invalid reset link.';
}

// Process password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $token = $_POST['token'];
    $email = $_POST['email'];
    
    if (empty($password) || empty($confirmPassword)) {
        $error = 'Please fill in all fields';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        try {
            $db = getDB();
            
            // Verify token again
            $stmt = $db->prepare("
                SELECT user_id 
                FROM users 
                WHERE email = ? 
                AND reset_token = ? 
                AND reset_token_expiry > NOW()
            ");
            $stmt->execute([$email, $token]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Update password and clear reset token
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $db->prepare("
                    UPDATE users 
                    SET password_hash = ?, 
                        reset_token = NULL, 
                        reset_token_expiry = NULL 
                    WHERE user_id = ?
                ");
                $updateStmt->execute([$passwordHash, $user['user_id']]);
                
                $success = true;
            } else {
                $error = 'Invalid or expired reset link.';
            }
        } catch (PDOException $e) {
            $error = 'Failed to reset password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <a href="../index.php" class="flex items-center justify-center mb-6">
                    <i class="fas fa-heartbeat text-5xl text-red-600"></i>
                </a>
                <h2 class="text-center text-4xl font-bold text-gray-900">
                    Reset Your Password
                </h2>
                <p class="mt-2 text-center text-gray-600">
                    Enter your new password below
                </p>
            </div>
            
            <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                <div class="flex">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3 mt-1"></i>
                    <div>
                        <p class="text-red-700"><?php echo $error; ?></p>
                        <?php if (!$validToken): ?>
                        <a href="forgot_password.php" class="text-red-600 hover:text-red-700 font-medium text-sm mt-2 inline-block">
                            Request a new reset link →
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <!-- Success Message -->
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                <div class="flex">
                    <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                    <div>
                        <p class="text-green-700 font-semibold">Password Reset Successful!</p>
                        <p class="text-green-600 text-sm mt-1">Your password has been updated successfully.</p>
                    </div>
                </div>
            </div>
            
            <div class="text-center">
                <a href="login.php" 
                   class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white gradient-bg hover:opacity-90 transition">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Sign In Now
                </a>
            </div>
            
            <?php elseif ($validToken): ?>
            <!-- Reset Password Form -->
            <form class="mt-8 space-y-6" method="POST" action="">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        New Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input id="password" name="password" type="password" required minlength="6"
                            class="appearance-none relative block w-full pl-10 pr-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                            placeholder="Enter new password (min 6 characters)">
                    </div>
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirm New Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input id="confirm_password" name="confirm_password" type="password" required minlength="6"
                            class="appearance-none relative block w-full pl-10 pr-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                            placeholder="Confirm new password">
                    </div>
                </div>

                <div>
                    <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white gradient-bg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition">
                        <i class="fas fa-key mr-2"></i>
                        Reset Password
                    </button>
                </div>
                
                <div class="text-center">
                    <p class="text-gray-600 text-sm">
                        Remember your password? 
                        <a href="login.php" class="font-medium text-purple-600 hover:text-purple-500">
                            Sign in here
                        </a>
                    </p>
                </div>
            </form>
            
            <!-- Security Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <i class="fas fa-info-circle text-blue-500 mr-3 mt-1"></i>
                    <div>
                        <p class="text-blue-700 text-sm">
                            <strong>Password Requirements:</strong>
                        </p>
                        <ul class="text-blue-600 text-sm mt-1 ml-4 list-disc">
                            <li>Minimum 6 characters</li>
                            <li>Use a strong, unique password</li>
                            <li>Don't reuse old passwords</li>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
