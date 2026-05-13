<?php
require_once '../config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';
$userType = isset($_GET['type']) && in_array($_GET['type'], ['donor', 'hospital']) ? $_GET['type'] : 'donor';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $type = $_POST['user_type'];
    
    // Validation
    if (empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        try {
            $db = getDB();
            
            // Check if email already exists
            $checkStmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
            $checkStmt->execute([$email]);
            
            if ($checkStmt->fetch()) {
                $error = 'Email already registered';
            } else {
                // Start transaction
                $db->beginTransaction();
                
                // Insert into users table
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $userStmt = $db->prepare("INSERT INTO users (email, password_hash, user_type, status) VALUES (?, ?, ?, 'pending')");
                $userStmt->execute([$email, $passwordHash, $type]);
                $userId = $db->lastInsertId();
                
                if ($type === 'donor') {
                    // Insert donor details
                    $fullName = sanitizeInput($_POST['full_name']);
                    $bloodType = $_POST['blood_type'];
                    $dob = $_POST['date_of_birth'];
                    $gender = $_POST['gender'];
                    $phone = sanitizeInput($_POST['phone']);
                    $address = sanitizeInput($_POST['address']);
                    $city = sanitizeInput($_POST['city']);
                    
                    $donorStmt = $db->prepare("
                        INSERT INTO donors (user_id, full_name, blood_type, date_of_birth, gender, phone, address, city) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $donorStmt->execute([$userId, $fullName, $bloodType, $dob, $gender, $phone, $address, $city]);
                } else {
                    // Insert hospital details
                    $hospitalName = sanitizeInput($_POST['hospital_name']);
                    $regNumber = sanitizeInput($_POST['registration_number']);
                    $phone = sanitizeInput($_POST['phone']);
                    $address = sanitizeInput($_POST['address']);
                    $city = sanitizeInput($_POST['city']);
                    $contactPerson = sanitizeInput($_POST['contact_person']);
                    $contactEmail = sanitizeInput($_POST['contact_email']);
                    
                    $hospitalStmt = $db->prepare("
                        INSERT INTO hospitals (user_id, hospital_name, registration_number, phone, address, city, contact_person, contact_email) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $hospitalStmt->execute([$userId, $hospitalName, $regNumber, $phone, $address, $city, $contactPerson, $contactEmail]);
                }
                
                // Commit transaction
                $db->commit();
                
                // Redirect to success page
                redirect('auth/registration_success.php?type=' . $type);
            }
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $error = 'Registration failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - <?php echo SITE_NAME; ?></title>
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
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <a href="../index.php" class="inline-block mb-4">
                    <i class="fas fa-heartbeat text-5xl text-red-600"></i>
                </a>
                <h2 class="text-4xl font-bold text-gray-900">Create Your Account</h2>
                <p class="mt-2 text-gray-600">Join our community and start making a difference</p>
            </div>

            <!-- User Type Selector -->
            <div class="bg-white rounded-lg shadow-lg p-2 mb-8">
                <div class="grid grid-cols-2 gap-2">
                    <a href="?type=donor" class="<?php echo $userType === 'donor' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700'; ?> px-6 py-3 rounded-lg text-center font-semibold transition">
                        <i class="fas fa-user mr-2"></i>I'm a Donor
                    </a>
                    <a href="?type=hospital" class="<?php echo $userType === 'hospital' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700'; ?> px-6 py-3 rounded-lg text-center font-semibold transition">
                        <i class="fas fa-hospital mr-2"></i>I'm a Hospital
                    </a>
                </div>
            </div>

            <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded mb-6">
                <div class="flex">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3 mt-1"></i>
                    <p class="text-red-700"><?php echo $error; ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Registration Form -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <form method="POST" action="">
                    <input type="hidden" name="user_type" value="<?php echo $userType; ?>">
                    
                    <!-- Common Fields -->
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Account Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                                <input type="email" name="email" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                                <input type="password" name="password" required minlength="6"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password *</label>
                                <input type="password" name="confirm_password" required minlength="6"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                            </div>
                        </div>
                    </div>

                    <?php if ($userType === 'donor'): ?>
                    <!-- Donor Specific Fields -->
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Personal Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                                <input type="text" name="full_name" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Blood Type *</label>
                                <select name="blood_type" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                                    <option value="">Select Blood Type</option>
                                    <?php foreach (BLOOD_TYPES as $type): ?>
                                    <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth *</label>
                                <input type="date" name="date_of_birth" required max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Gender *</label>
                                <select name="gender" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                                <input type="tel" name="phone" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Address *</label>
                                <textarea name="address" required rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                                <input type="text" name="city" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Hospital Specific Fields -->
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Hospital Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Hospital Name *</label>
                                <input type="text" name="hospital_name" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Registration Number *</label>
                                <input type="text" name="registration_number" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                                <input type="tel" name="phone" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Address *</label>
                                <textarea name="address" required rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                                <input type="text" name="city" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contact Person *</label>
                                <input type="text" name="contact_person" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email *</label>
                                <input type="email" name="contact_email" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Terms and Submit -->
                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" required class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">
                                I agree to the <a href="#" class="text-purple-600 hover:text-purple-500">Terms and Conditions</a>
                            </span>
                        </label>
                    </div>

                    <button type="submit" class="w-full gradient-bg text-white py-3 px-4 rounded-lg font-semibold hover:opacity-90 transition">
                        <i class="fas fa-user-plus mr-2"></i>Create Account
                    </button>

                    <div class="mt-6 text-center">
                        <p class="text-gray-600">
                            Already have an account? 
                            <a href="login.php" class="font-medium text-purple-600 hover:text-purple-500">
                                Sign in here
                            </a>
                        </p>
                    </div>
                </form>
            </div>

            <!-- Info Note -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <i class="fas fa-info-circle text-blue-600 mr-3 mt-1"></i>
                    <div>
                        <p class="text-blue-800 font-semibold">Account Approval Required</p>
                        <p class="text-blue-700 text-sm mt-1">
                            Your account will be reviewed by our administrators for verification. You'll receive a notification once your account is approved.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
