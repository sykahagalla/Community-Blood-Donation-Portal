<?php
require_once 'config/config.php';

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Save Lives, Donate Blood</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .gradient-red {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .hero-pattern {
            background-color: #667eea;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .pulse-animation {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .5;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <i class="fas fa-heartbeat text-3xl text-red-600 mr-3"></i>
                    <span class="text-xl font-bold text-gray-800">BloodLife</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="#home" class="text-gray-700 hover:text-red-600 px-3 py-2 font-medium">Home</a>
                    <a href="#about" class="text-gray-700 hover:text-red-600 px-3 py-2 font-medium">About</a>
                    <a href="#how-it-works" class="text-gray-700 hover:text-red-600 px-3 py-2 font-medium">How It Works</a>
                    <a href="#contact" class="text-gray-700 hover:text-red-600 px-3 py-2 font-medium">Contact</a>
                    <a href="auth/login.php" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">
                        Sign In
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-pattern pt-24 pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div class="text-white">
                    <h1 class="text-5xl font-bold mb-6 leading-tight">
                        Save Lives by <span class="text-yellow-300">Donating Blood</span>
                    </h1>
                    <p class="text-xl mb-8 text-gray-100">
                        Connect with those in need. Join our community of life-savers and make a difference today.
                    </p>
                    <div class="flex space-x-4">
                        <a href="auth/register.php?type=donor" class="bg-white text-purple-600 px-8 py-4 rounded-lg font-semibold hover:bg-gray-100 transition shadow-lg">
                            Become a Donor
                        </a>
                        <a href="auth/register.php?type=hospital" class="bg-red-600 text-white px-8 py-4 rounded-lg font-semibold hover:bg-red-700 transition shadow-lg">
                            Register Hospital
                        </a>
                    </div>
                    <div class="mt-12 flex space-x-8">
                        <div>
                            <div class="text-4xl font-bold">10,000+</div>
                            <div class="text-gray-200">Lives Saved</div>
                        </div>
                        <div>
                            <div class="text-4xl font-bold">5,000+</div>
                            <div class="text-gray-200">Active Donors</div>
                        </div>
                        <div>
                            <div class="text-4xl font-bold">200+</div>
                            <div class="text-gray-200">Partner Hospitals</div>
                        </div>
                    </div>
                </div>
                <div class="hidden md:block">
                    <img src="assets/images/hero-illustration.svg" alt="Blood Donation" class="w-full" onerror="this.style.display='none'">
                    <div class="bg-white rounded-2xl p-8 shadow-2xl">
                        <div class="text-center">
                            <i class="fas fa-hand-holding-heart text-6xl text-red-600 mb-4"></i>
                            <h3 class="text-2xl font-bold text-gray-800 mb-2">One Donation</h3>
                            <p class="text-gray-600">Can save up to 3 lives</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="about" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Why Choose BloodLife?</h2>
                <p class="text-xl text-gray-600">A comprehensive platform connecting donors, recipients, and hospitals</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-8 rounded-xl card-hover">
                    <div class="bg-purple-600 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-bell text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Real-Time Alerts</h3>
                    <p class="text-gray-600">
                        Receive instant notifications when someone nearby needs your blood type urgently.
                    </p>
                </div>
                
                <div class="bg-gradient-to-br from-red-50 to-red-100 p-8 rounded-xl card-hover">
                    <div class="bg-red-600 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-map-marker-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Geolocation Matching</h3>
                    <p class="text-gray-600">
                        Find the nearest donors quickly using advanced location-based technology.
                    </p>
                </div>
                
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-8 rounded-xl card-hover">
                    <div class="bg-blue-600 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-chart-line text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Track Your Impact</h3>
                    <p class="text-gray-600">
                        Monitor your donation history and see how many lives you've helped save.
                    </p>
                </div>
                
                <div class="bg-gradient-to-br from-green-50 to-green-100 p-8 rounded-xl card-hover">
                    <div class="bg-green-600 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-shield-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Secure & Private</h3>
                    <p class="text-gray-600">
                        Your personal information is protected with industry-standard encryption.
                    </p>
                </div>
                
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-8 rounded-xl card-hover">
                    <div class="bg-yellow-600 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-calendar-check text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Eligibility Tracking</h3>
                    <p class="text-gray-600">
                        Automatic reminders when you're eligible to donate again based on your history.
                    </p>
                </div>
                
                <div class="bg-gradient-to-br from-pink-50 to-pink-100 p-8 rounded-xl card-hover">
                    <div class="bg-pink-600 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-users text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Community Drives</h3>
                    <p class="text-gray-600">
                        Stay informed about upcoming blood donation events in your area.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">How It Works</h2>
                <p class="text-xl text-gray-600">Simple steps to start saving lives</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="bg-purple-600 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <span class="text-white text-3xl font-bold">1</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Register</h3>
                    <p class="text-gray-600">Create your account and complete your profile with blood type and location</p>
                </div>
                
                <div class="text-center">
                    <div class="bg-red-600 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <span class="text-white text-3xl font-bold">2</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Get Notified</h3>
                    <p class="text-gray-600">Receive alerts when your blood type is needed nearby</p>
                </div>
                
                <div class="text-center">
                    <div class="bg-blue-600 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <span class="text-white text-3xl font-bold">3</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Respond</h3>
                    <p class="text-gray-600">Confirm your availability and schedule a donation</p>
                </div>
                
                <div class="text-center">
                    <div class="bg-green-600 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <span class="text-white text-3xl font-bold">4</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Save Lives</h3>
                    <p class="text-gray-600">Visit the hospital and make your life-saving donation</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Blood Types Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Blood Type Compatibility</h2>
                <p class="text-xl text-gray-600">Know your blood type and who you can help</p>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
                <?php foreach (BLOOD_TYPES as $type): ?>
                <div class="bg-gradient-to-br from-red-500 to-red-600 p-6 rounded-xl text-center text-white card-hover cursor-pointer">
                    <div class="text-4xl font-bold mb-2"><?php echo $type; ?></div>
                    <div class="text-sm">Blood Type</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Demo Login Section -->
    <section class="py-20 bg-gradient-to-br from-blue-50 to-purple-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-2xl p-8">
                <div class="text-center mb-8">
                    <i class="fas fa-rocket text-5xl text-purple-600 mb-4"></i>
                    <h2 class="text-3xl font-bold text-gray-800 mb-3">Try the Demo!</h2>
                    <p class="text-gray-600">Test drive the platform with pre-configured accounts</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="border-2 border-purple-200 rounded-lg p-6 hover:border-purple-500 transition">
                        <div class="bg-purple-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user-shield text-purple-600 text-xl"></i>
                        </div>
                        <h3 class="font-bold text-gray-800 text-center mb-3">Admin Access</h3>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p><strong>Email:</strong><br>admin@bloodportal.com</p>
                            <p><strong>Password:</strong><br>admin123</p>
                        </div>
                    </div>
                    
                    <div class="border-2 border-blue-200 rounded-lg p-6 hover:border-blue-500 transition">
                        <div class="bg-blue-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user text-blue-600 text-xl"></i>
                        </div>
                        <h3 class="font-bold text-gray-800 text-center mb-3">Donor Access</h3>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p><strong>Email:</strong><br>donors@bloodportal.com</p>
                            <p><strong>Password:</strong><br>donors123</p>
                        </div>
                    </div>
                    
                    <div class="border-2 border-red-200 rounded-lg p-6 hover:border-red-500 transition">
                        <div class="bg-red-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-hospital text-red-600 text-xl"></i>
                        </div>
                        <h3 class="font-bold text-gray-800 text-center mb-3">Hospital Access</h3>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p><strong>Email:</strong><br>hospitals@bloodportal.com</p>
                            <p><strong>Password:</strong><br>hospitals123</p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center">
                    <a href="auth/login.php" class="bg-purple-600 text-white px-10 py-4 rounded-lg font-bold hover:bg-purple-700 transition shadow-lg text-lg inline-block">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login Now
                    </a>
                </div>
                
                <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-sm text-yellow-800 text-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>First Time Setup:</strong> If demo accounts don't work, run 
                        <a href="setup_demo_accounts.php" class="underline font-semibold">setup_demo_accounts.php</a> once.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="gradient-red py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
            <h2 class="text-4xl font-bold mb-6">Ready to Make a Difference?</h2>
            <p class="text-xl mb-8">Join thousands of donors who are already saving lives in their community</p>
            <div class="flex justify-center space-x-4">
                <a href="auth/register.php" class="bg-white text-red-600 px-10 py-4 rounded-lg font-bold hover:bg-gray-100 transition shadow-lg text-lg">
                    Get Started Now
                </a>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <div>
                    <h2 class="text-4xl font-bold text-gray-800 mb-6">Get In Touch</h2>
                    <p class="text-gray-600 mb-8">Have questions? We're here to help you make a difference.</p>
                    
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="bg-purple-100 p-3 rounded-lg mr-4">
                                <i class="fas fa-map-marker-alt text-purple-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 mb-1">Address</h3>
                                <p class="text-gray-600">123 Blood Drive Lane, Colombo 00700, Sri Lanka</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="bg-red-100 p-3 rounded-lg mr-4">
                                <i class="fas fa-phone text-red-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 mb-1">Phone</h3>
                                <p class="text-gray-600">+94 11 234 5678</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="bg-blue-100 p-3 rounded-lg mr-4">
                                <i class="fas fa-envelope text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 mb-1">Email</h3>
                                <p class="text-gray-600">info@bloodlife.lk</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-8 rounded-xl shadow-lg">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Send us a message</h3>
                    <form>
                        <div class="mb-4">
                            <input type="text" placeholder="Your Name" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                        </div>
                        <div class="mb-4">
                            <input type="email" placeholder="Your Email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                        </div>
                        <div class="mb-4">
                            <textarea rows="4" placeholder="Your Message" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-purple-700 transition">
                            Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <i class="fas fa-heartbeat text-3xl text-red-600 mr-3"></i>
                        <span class="text-xl font-bold">BloodLife</span>
                    </div>
                    <p class="text-gray-400">Connecting communities, saving lives through blood donation.</p>
                </div>
                
                <div>
                    <h3 class="text-lg font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#about" class="hover:text-white transition">About Us</a></li>
                        <li><a href="#how-it-works" class="hover:text-white transition">How It Works</a></li>
                        <li><a href="auth/login.php" class="hover:text-white transition">Sign In</a></li>
                        <li><a href="auth/register.php" class="hover:text-white transition">Register</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-bold mb-4">Resources</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition">Blood Donation Facts</a></li>
                        <li><a href="#" class="hover:text-white transition">Eligibility Criteria</a></li>
                        <li><a href="#" class="hover:text-white transition">FAQs</a></li>
                        <li><a href="#" class="hover:text-white transition">Privacy Policy</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-bold mb-4">Follow Us</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="bg-gray-700 p-3 rounded-full hover:bg-purple-600 transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="bg-gray-700 p-3 rounded-full hover:bg-purple-600 transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="bg-gray-700 p-3 rounded-full hover:bg-purple-600 transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="bg-gray-700 p-3 rounded-full hover:bg-purple-600 transition">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2026 Community Blood Donation Portal. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('nav');
            if (window.scrollY > 50) {
                nav.classList.add('shadow-xl');
            } else {
                nav.classList.remove('shadow-xl');
            }
        });
    </script>
</body>
</html>
