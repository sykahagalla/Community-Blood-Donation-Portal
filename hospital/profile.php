<?php
require_once '../config/config.php';
requireUserType('hospital');

$db = getDB();

// Get hospital information
$stmt = $db->prepare("
    SELECT h.*, u.email, u.status, u.created_at as registered_at
    FROM hospitals h
    JOIN users u ON h.user_id = u.user_id
    WHERE h.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$hospital = $stmt->fetch();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hospital_name = $_POST['hospital_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $registration_number = $_POST['registration_number'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $contact_person = $_POST['contact_person'] ?? '';
    $contact_email = $_POST['contact_email'] ?? '';
    
    $updateStmt = $db->prepare("
        UPDATE hospitals SET 
            hospital_name = ?, phone = ?, registration_number = ?, 
            address = ?, city = ?, contact_person = ?, contact_email = ?
        WHERE user_id = ?
    ");
    
    if ($updateStmt->execute([$hospital_name, $phone, $registration_number, $address, $city, $contact_person, $contact_email, $_SESSION['user_id']])) {
        $_SESSION['success'] = 'Profile updated successfully!';
        redirect('profile.php');
    } else {
        $error = 'Failed to update profile.';
    }
}

$pageTitle = 'Hospital Profile';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <?php include 'includes/sidebar.php'; ?>
        <div class="flex-1 overflow-x-hidden overflow-y-auto">
            <?php include 'includes/header.php'; ?>
            <main class="p-6">
                <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-hospital text-blue-600 mr-2"></i>Hospital Profile
                        </h3>
                    </div>
                    
                    <form method="POST" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Hospital Name</label>
                                <input type="text" name="hospital_name" value="<?php echo htmlspecialchars($hospital['hospital_name']); ?>" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" value="<?php echo htmlspecialchars($hospital['email']); ?>" disabled
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($hospital['phone']); ?>" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Registration Number</label>
                                <input type="text" name="registration_number" value="<?php echo htmlspecialchars($hospital['registration_number']); ?>" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                <textarea name="address" rows="3" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?php echo htmlspecialchars($hospital['address']); ?></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                                <input type="text" name="city" value="<?php echo htmlspecialchars($hospital['city']); ?>" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contact Person</label>
                                <input type="text" name="contact_person" value="<?php echo htmlspecialchars($hospital['contact_person']); ?>" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
                                <input type="email" name="contact_email" value="<?php echo htmlspecialchars($hospital['contact_email']); ?>" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-save mr-2"></i>Update Profile
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Additional Info -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Account Status</h4>
                        <?php
                        $statusColors = [
                            'active' => 'text-green-600',
                            'pending' => 'text-yellow-600',
                            'suspended' => 'text-red-600'
                        ];
                        ?>
                        <p class="text-3xl font-bold <?php echo $statusColors[$hospital['status']]; ?>"><?php echo ucfirst($hospital['status']); ?></p>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Member Since</h4>
                        <p class="text-3xl font-bold text-purple-600"><?php echo date('M Y', strtotime($hospital['registered_at'])); ?></p>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Registration Number</h4>
                        <p class="text-xl font-bold text-blue-600"><?php echo htmlspecialchars($hospital['registration_number']); ?></p>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
