<?php
require_once '../config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $userType = getUserType();
    switch ($userType) {
        case 'admin':
            redirect('admin/dashboard.php');
            break;
        case 'donor':
            redirect('donor/dashboard.php');
            break;
        case 'hospital':
            redirect('hospital/dashboard.php');
            break;
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                if ($user['status'] === 'pending') {
                    $error = 'Your account is pending approval. Please wait for admin verification.';
                } elseif ($user['status'] === 'suspended') {
                    $error = 'Your account has been suspended. Please contact support.';
                } else {
                    // Set session variables
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['user_type'] = $user['user_type'];
                    
                    // Update last login
                    $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
                    $updateStmt->execute([$user['user_id']]);
                    
                    // Redirect based on user type
                    switch ($user['user_type']) {
                        case 'admin':
                            redirect('admin/dashboard.php');
                            break;
                        case 'donor':
                            redirect('donor/dashboard.php');
                            break;
                        case 'hospital':
                            redirect('hospital/dashboard.php');
                            break;
                    }
                }
            } else {
                $error = 'Invalid email or password';
            }
        } catch (PDOException $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - <?php echo SITE_NAME; ?></title>
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
    <div class="min-h-screen flex">
        <!-- Left Side - Form -->
        <div class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8">
            <div class="max-w-md w-full space-y-8">
                <div>
                    <a href="../index.php" class="flex items-center justify-center mb-6">
                        <i class="fas fa-heartbeat text-5xl text-red-600"></i>
                    </a>
                    <h2 class="text-center text-4xl font-bold text-gray-900">
                        Welcome Back
                    </h2>
                    <p class="mt-2 text-center text-gray-600">
                        Sign in to your account to continue
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
                
                <?php if (isset($_GET['registered'])): ?>
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                    <div class="flex">
                        <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                        <p class="text-green-700">Registration successful! Please sign in.</p>
                    </div>
                </div>
                <?php endif; ?>
                
                <form class="mt-8 space-y-6" method="POST" action="">
                    <div class="space-y-4">
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
                                    placeholder="Enter your email">
                            </div>
                        </div>
                        
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                Password
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input id="password" name="password" type="password" required 
                                    class="appearance-none relative block w-full pl-10 pr-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                                    placeholder="Enter your password">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember-me" name="remember-me" type="checkbox" 
                                class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                            <label for="remember-me" class="ml-2 block text-sm text-gray-700">
                                Remember me
                            </label>
                        </div>
                        <div class="text-sm">
                            <a href="forgot_password.php" class="font-medium text-purple-600 hover:text-purple-500">
                                Forgot password?
                            </a>
                        </div>
                    </div>

                    <div>
                        <button type="submit" 
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white gradient-bg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Sign In
                        </button>
                    </div>
                    
                    <div class="text-center">
                        <p class="text-gray-600">
                            Don't have an account? 
                            <a href="register.php" class="font-medium text-purple-600 hover:text-purple-500">
                                Sign up here
                            </a>
                        </p>
                    </div>
                </form>
                
                <!-- Demo Credentials -->
                <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-800 mb-3 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>Demo Credentials
                    </h3>

                   
                    <div class="space-y-2 text-sm text-blue-700">
                        <div class="bg-white rounded p-2">
                            <strong>👤 Admin:</strong><br>
                            Email: admin@bloodportal.com<br>
                            Password: admin123
                        </div>
                        <div class="bg-white rounded p-2">
                            <strong>🩸 Donor:</strong><br>
                            Email: donors@bloodportal.com<br>
                            Password: donors123
                        </div>
                        <div class="bg-white rounded p-2">
                            <strong>🏥 Hospital:</strong><br>
                            Email: hospitals@bloodportal.com<br>
                            Password: hospitals123
                        </div>
                    </div>
                    <p class="text-xs text-blue-600 mt-3 italic">
                        Note: If demo accounts don't work, run setup_demo_accounts.php first
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Side - Image/Info -->
        <div class="hidden lg:block lg:flex-1 gradient-bg relative">
            <div class="h-full flex items-center justify-center p-12">
                <div class="text-white">
                    <h2 class="text-4xl font-bold mb-6">Join Our Life-Saving Community</h2>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <div class="bg-white bg-opacity-20 p-3 rounded-lg mr-4">
                                <i class="fas fa-check text-2xl"></i>
                            </div>
                            <p class="text-lg">Connect with donors instantly</p>
                        </div>
                        <div class="flex items-center">
                            <div class="bg-white bg-opacity-20 p-3 rounded-lg mr-4">
                                <i class="fas fa-check text-2xl"></i>
                            </div>
                            <p class="text-lg">Real-time emergency alerts</p>
                        </div>
                        <div class="flex items-center">
                            <div class="bg-white bg-opacity-20 p-3 rounded-lg mr-4">
                                <i class="fas fa-check text-2xl"></i>
                            </div>
                            <p class="text-lg">Track your donation history</p>
                        </div>
                        <div class="flex items-center">
                            <div class="bg-white bg-opacity-20 p-3 rounded-lg mr-4">
                                <i class="fas fa-check text-2xl"></i>
                            </div>
                            <p class="text-lg">Make a real difference</p>
                        </div>
                    </div>
                    
                    <div class="mt-12 bg-white bg-opacity-10 backdrop-blur-lg rounded-2xl p-6">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-quote-left text-3xl mr-4"></i>
                        </div>
                        <p class="text-lg italic mb-4">
                            "This platform made it so easy to find donors during an emergency. It literally saved my father's life."
                        </p>
                        <p class="font-semibold">- Dr. Perera, Colombo General Hospital</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
