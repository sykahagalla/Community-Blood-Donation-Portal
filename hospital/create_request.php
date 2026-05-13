<?php
require_once '../config/config.php';
requireUserType('hospital');

$db = getDB();

// Get hospital information
$hospitalStmt = $db->prepare("SELECT * FROM hospitals WHERE user_id = ?");
$hospitalStmt->execute([$_SESSION['user_id']]);
$hospital = $hospitalStmt->fetch();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bloodType = $_POST['blood_type'];
    $unitsNeeded = intval($_POST['units_needed']);
    $urgencyLevel = $_POST['urgency_level'];
    $patientName = sanitizeInput($_POST['patient_name']);
    $reason = sanitizeInput($_POST['reason']);
    $contactNumber = sanitizeInput($_POST['contact_number']);
    $neededBy = $_POST['needed_by'];
    
    if (empty($bloodType) || $unitsNeeded < 1 || empty($urgencyLevel) || empty($reason) || empty($contactNumber) || empty($neededBy)) {
        $error = 'Please fill in all required fields';
    } else {
        try {
            $stmt = $db->prepare("
                INSERT INTO blood_requests (hospital_id, blood_type, units_needed, urgency_level, patient_name, reason, contact_number, needed_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$hospital['hospital_id'], $bloodType, $unitsNeeded, $urgencyLevel, $patientName, $reason, $contactNumber, $neededBy]);
            
            $success = 'Blood request created successfully! Eligible donors will be notified.';
        } catch (PDOException $e) {
            $error = 'Error creating request: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Create Blood Request';
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
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-x-hidden overflow-y-auto">
            <!-- Top Navigation -->
            <?php include 'includes/header.php'; ?>

            <!-- Form Content -->
            <main class="p-6">
                <div class="max-w-3xl mx-auto">
                    <!-- Header -->
                    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Create Blood Request</h3>
                        <p class="text-gray-600">Fill in the details to request blood from donors in your area</p>
                    </div>

                    <?php if ($error): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded mb-6">
                        <div class="flex">
                            <i class="fas fa-exclamation-circle text-red-500 mr-3 mt-1"></i>
                            <p class="text-red-700"><?php echo $error; ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded mb-6">
                        <div class="flex">
                            <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                            <div class="flex-1">
                                <p class="text-green-700 font-semibold"><?php echo $success; ?></p>
                                <a href="dashboard.php" class="text-green-600 hover:text-green-700 text-sm mt-2 inline-block">
                                    <i class="fas fa-arrow-left mr-1"></i>Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <div class="bg-white rounded-lg shadow-lg p-8">
                        <form method="POST" action="">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Blood Type Required *
                                    </label>
                                    <select name="blood_type" required 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600">
                                        <option value="">Select Blood Type</option>
                                        <?php foreach (BLOOD_TYPES as $type): ?>
                                        <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Units Needed *
                                    </label>
                                    <input type="number" name="units_needed" min="1" required 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Urgency Level *
                                    </label>
                                    <select name="urgency_level" required 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600">
                                        <option value="">Select Urgency</option>
                                        <option value="critical">Critical - Immediate Need</option>
                                        <option value="urgent">Urgent - Within 24 hours</option>
                                        <option value="normal">Normal - Within a few days</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Needed By *
                                    </label>
                                    <input type="datetime-local" name="needed_by" required 
                                        min="<?php echo date('Y-m-d\TH:i'); ?>"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Patient Name (Optional)
                                    </label>
                                    <input type="text" name="patient_name" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Contact Number *
                                    </label>
                                    <input type="tel" name="contact_number" required 
                                        value="<?php echo htmlspecialchars($hospital['phone']); ?>"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600">
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Reason for Blood Request *
                                </label>
                                <textarea name="reason" rows="4" required 
                                    placeholder="E.g., Emergency surgery, accident victim, chronic condition treatment..."
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600"></textarea>
                            </div>

                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                                <div class="flex">
                                    <i class="fas fa-info-circle text-blue-600 mr-3 mt-1"></i>
                                    <div class="text-sm text-blue-800">
                                        <p class="font-semibold mb-1">What happens next?</p>
                                        <p>Once you submit this request, all eligible donors with the matching blood type will be notified. They can contact your hospital directly to schedule a donation.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex space-x-4">
                                <button type="submit" class="flex-1 bg-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-700 transition">
                                    <i class="fas fa-paper-plane mr-2"></i>Submit Blood Request
                                </button>
                                <a href="dashboard.php" class="flex-1 bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition text-center">
                                    <i class="fas fa-times mr-2"></i>Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
