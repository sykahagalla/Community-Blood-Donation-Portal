<?php
require_once '../config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';
$resetToken = '';
$resetEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        try {
            $db = getDB();
            
            // Check if email exists
            $stmt = $db->prepare("SELECT user_id, email, user_type FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store token in database
                $updateStmt = $db->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
                $updateStmt->execute([$token, $expiry, $email]);
                
                // In a real application, you would send an email here
                // For now, we'll display the reset link on screen
                $resetToken = $token;
                $resetEmail = $email;
                $success = 'Password reset link generated successfully!';
            } else {
                // For security, don't reveal if email exists or not
                $success = 'If your email exists in our system, you will receive a password reset link.';
            }
        } catch (PDOException $e) {
            $error = 'An error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo SITE_NAME; ?></title>
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
                    Forgot Password?
                </h2>
                <p class="mt-2 text-center text-gray-600">
                    Enter your email address and we'll help you reset your password
                </p>
            </div>
            
            <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                <div class="flex">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3 mt-1"></i>
                    <p class="text-red-700"><?php echo $error; ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($success && !$resetToken): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                <div class="flex">
                    <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                    <p class="text-green-700"><?php echo $success; ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($resetToken): ?>
            <!-- Display Reset Link (temporary solution until email is configured) -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <div class="flex">
                    <i class="fas fa-info-circle text-blue-500 mr-3 mt-1"></i>
                    <div>
                        <p class="text-blue-700 font-semibold mb-2"><?php echo $success; ?></p>
                        <p class="text-blue-600 text-sm mb-2">
                            <strong>Note:</strong> Email functionality is not yet configured. Please use the link below:
                        </p>
                        <div class="bg-white p-3 rounded border border-blue-200 break-all">
                            <a href="reset_password.php?token=<?php echo $resetToken; ?>&email=<?php echo urlencode($resetEmail); ?>" 
                               class="text-purple-600 hover:text-purple-700 font-medium">
                                Click here to reset your password
                            </a>
                        </div>
                        <p class="text-blue-600 text-xs mt-2">This link will expire in 1 hour.</p>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- Forgot Password Form -->
            <form class="mt-8 space-y-6" method="POST" action="">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input id="email" name="email" type="email" required 
                            class="appearance-none relative block w-full pl-10 pr-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                            placeholder="Enter your registered email">
                    </div>
                </div>

                <div>
                    <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white gradient-bg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Send Reset Link
                    </button>
                </div>
                
                <div class="text-center">
                    <p class="text-gray-600">
                        Remember your password? 
                        <a href="login.php" class="font-medium text-purple-600 hover:text-purple-500">
                            Sign in here
                        </a>
                    </p>
                </div>
            </form>
            <?php endif; ?>
            
            <!-- Info Box -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="flex">
                    <i class="fas fa-shield-alt text-gray-500 mr-3 mt-1"></i>
                    <div>
                        <p class="text-gray-700 text-sm">
                            <strong>Security Note:</strong> For your protection, we'll only send password reset instructions to the email address on file.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
