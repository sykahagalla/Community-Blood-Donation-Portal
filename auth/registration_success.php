<?php
require_once '../config/config.php';

// Get user type from URL
$userType = isset($_GET['type']) && in_array($_GET['type'], ['donor', 'hospital']) ? $_GET['type'] : 'donor';
$userTypeLabel = $userType === 'donor' ? 'Donor' : 'Hospital';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        @keyframes checkmark {
            0% { stroke-dashoffset: 100; }
            100% { stroke-dashoffset: 0; }
        }
        .checkmark {
            stroke-dasharray: 100;
            stroke-dashoffset: 100;
            animation: checkmark 0.6s ease-out 0.3s forwards;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full animate-fade-in">
            <!-- Success Icon -->
            <div class="flex justify-center mb-8">
                <div class="relative">
                    <div class="w-32 h-32 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-20 h-20" viewBox="0 0 52 52">
                            <circle class="checkmark" cx="26" cy="26" r="24" fill="none" stroke="#10b981" stroke-width="3"/>
                            <path class="checkmark" fill="none" stroke="#10b981" stroke-width="3" d="M14 27l8 8 16-16"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Main Content Card -->
            <div class="bg-white rounded-lg shadow-xl p-8 md:p-12">
                <div class="text-center">
                    <h1 class="text-4xl font-bold text-gray-900 mb-4">
                        Registration Successful!
                    </h1>
                    <p class="text-xl text-gray-600 mb-8">
                        Thank you for registering as a <?php echo $userTypeLabel; ?>
                    </p>
                </div>

                <!-- Pending Approval Notice -->
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-r-lg mb-8">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock text-yellow-600 text-2xl mt-1"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-yellow-900 mb-2">
                                Your account is pending approval
                            </h3>
                            <p class="text-yellow-800 leading-relaxed">
                                Please wait for admin verification. Our team will review your registration details and approve your account shortly. You will receive a notification once your account is approved.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="bg-blue-50 rounded-lg p-6 mb-8">
                    <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                        What happens next?
                    </h3>
                    <ul class="space-y-3 text-blue-800">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-600 mr-3 mt-1"></i>
                            <span>Our admin team will review your registration information</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-600 mr-3 mt-1"></i>
                            <span>You will be notified via email once your account is approved</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-600 mr-3 mt-1"></i>
                            <span>After approval, you can log in and access all features</span>
                        </li>
                        <?php if ($userType === 'donor'): ?>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-600 mr-3 mt-1"></i>
                            <span>Start donating blood and respond to blood requests</span>
                        </li>
                        <?php else: ?>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-600 mr-3 mt-1"></i>
                            <span>Start creating blood requests and organizing blood drives</span>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Approval Timeline -->
                <div class="text-center mb-8">
                    <p class="text-gray-600 text-sm">
                        <i class="fas fa-hourglass-half mr-2 text-gray-500"></i>
                        Typical approval time: <span class="font-semibold text-gray-900">24-48 hours</span>
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="../index.php" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white gradient-bg hover:opacity-90 transition">
                        <i class="fas fa-home mr-2"></i>
                        Go to Homepage
                    </a>
                    <a href="login.php" class="inline-flex items-center justify-center px-6 py-3 border-2 border-purple-600 text-base font-medium rounded-lg text-purple-600 bg-white hover:bg-purple-50 transition">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Go to Login
                    </a>
                </div>
            </div>

            <!-- Support Information -->
            <div class="mt-8 text-center">
                <p class="text-gray-600 text-sm">
                    Questions or concerns? 
                    <a href="mailto:<?php echo SITE_EMAIL ?? 'support@blooddonation.com'; ?>" class="text-purple-600 hover:text-purple-700 font-medium">
                        Contact Support
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
