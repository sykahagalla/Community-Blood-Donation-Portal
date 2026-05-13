<?php
require_once '../config/config.php';
requireUserType('hospital');

$db = getDB();

// Get hospital information
$hospitalStmt = $db->prepare("
    SELECT h.*, u.status, u.email 
    FROM hospitals h 
    JOIN users u ON h.user_id = u.user_id 
    WHERE h.user_id = ?
");
$hospitalStmt->execute([$_SESSION['user_id']]);
$hospital = $hospitalStmt->fetch();

if (!$hospital) {
    redirect('auth/logout.php');
}

// Check if account is approved
if ($hospital['status'] === 'pending') {
    $isPending = true;
} else {
    $isPending = false;
    
    // Get statistics
    $statsStmt = $db->prepare("
        SELECT 
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active_requests,
            COUNT(CASE WHEN status = 'fulfilled' THEN 1 END) as fulfilled_requests,
            COUNT(*) as total_requests
        FROM blood_requests 
        WHERE hospital_id = ?
    ");
    $statsStmt->execute([$hospital['hospital_id']]);
    $stats = $statsStmt->fetch();
    
    // Get total donations received
    $donationCountStmt = $db->prepare("SELECT COUNT(*) FROM donations WHERE hospital_id = ?");
    $donationCountStmt->execute([$hospital['hospital_id']]);
    $totalDonations = $donationCountStmt->fetchColumn();
    
    // Get recent blood requests
    $recentRequestsStmt = $db->prepare("
        SELECT * FROM blood_requests 
        WHERE hospital_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $recentRequestsStmt->execute([$hospital['hospital_id']]);
    $recentRequests = $recentRequestsStmt->fetchAll();
    
    // Ensure it's always an array
    if (!is_array($recentRequests)) {
        $recentRequests = [];
    }
    
    // Get recent donations
    $recentDonationsStmt = $db->prepare("
        SELECT d.*, don.full_name, don.blood_type 
        FROM donations d 
        JOIN donors don ON d.donor_id = don.donor_id 
        WHERE d.hospital_id = ? 
        ORDER BY d.donation_date DESC 
        LIMIT 10
    ");
    $recentDonationsStmt->execute([$hospital['hospital_id']]);
    $recentDonations = $recentDonationsStmt->fetchAll();
    
    // Ensure it's always an array
    if (!is_array($recentDonations)) {
        $recentDonations = [];
    }
    
    // Get available donors count by blood type
    $availableDonorsStmt = $db->query("
        SELECT blood_type, COUNT(*) as count 
        FROM donors d 
        JOIN users u ON d.user_id = u.user_id 
        WHERE u.status = 'active' AND d.is_available = 1
        GROUP BY blood_type
    ");
    $availableDonors = $availableDonorsStmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

$pageTitle = 'Hospital Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <?php if ($isPending): ?>
    <!-- Pending Approval Screen -->
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="bg-yellow-100 rounded-full h-20 w-20 mx-auto mb-6 flex items-center justify-center">
                <i class="fas fa-clock text-yellow-600 text-4xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Account Pending Approval</h2>
            <p class="text-gray-600 mb-6">
                Thank you for registering your hospital! Your account is currently being reviewed by our administrators. 
                You'll receive an email notification once your account is approved.
            </p>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-blue-800">
                    <strong>What's next?</strong><br>
                    Our team typically reviews new hospital registrations within 24-48 hours. 
                    We verify all information to ensure the credibility of our partner hospitals.
                </p>
            </div>
            <a href="../auth/logout.php" class="inline-block bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition">
                <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </a>
        </div>
    </div>
    <?php else: ?>
    <!-- Main Dashboard -->
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-x-hidden overflow-y-auto">
            <!-- Top Navigation -->
            <?php include 'includes/header.php'; ?>

            <!-- Dashboard Content -->
            <main class="p-6">
                <!-- Welcome Banner -->
                <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-lg shadow-lg p-6 mb-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($hospital['hospital_name']); ?></h2>
                            <p class="text-red-100">Welcome to your hospital management dashboard</p>
                        </div>
                        <div>
                            <a href="create_request.php" class="bg-white text-red-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                                <i class="fas fa-plus mr-2"></i>Create Blood Request
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Active Requests</p>
                                <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo $stats['active_requests']; ?></h3>
                            </div>
                            <div class="bg-red-100 p-3 rounded-lg">
                                <i class="fas fa-exclamation-circle text-red-600 text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Fulfilled Requests</p>
                                <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo $stats['fulfilled_requests']; ?></h3>
                            </div>
                            <div class="bg-green-100 p-3 rounded-lg">
                                <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Total Donations</p>
                                <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo $totalDonations; ?></h3>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-lg">
                                <i class="fas fa-tint text-blue-600 text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Total Requests</p>
                                <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo $stats['total_requests']; ?></h3>
                            </div>
                            <div class="bg-purple-100 p-3 rounded-lg">
                                <i class="fas fa-list text-purple-600 text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available Donors by Blood Type -->
                <div class="bg-white rounded-lg shadow-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-users text-blue-600 mr-2"></i>
                            Available Donors by Blood Type
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
                            <?php foreach (BLOOD_TYPES as $type): ?>
                            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg p-4 text-center text-white">
                                <div class="text-2xl font-bold"><?php echo $type; ?></div>
                                <div class="text-3xl font-bold mt-2"><?php echo $availableDonors[$type] ?? 0; ?></div>
                                <div class="text-xs mt-1">Available</div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Blood Requests -->
                <div class="bg-white rounded-lg shadow-lg mb-6">
                    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-list text-red-600 mr-2"></i>
                            Your Blood Requests
                        </h3>
                        <a href="blood_requests.php" class="text-blue-600 hover:text-blue-700 text-sm font-semibold">
                            View All <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    <?php if (!empty($recentRequests) && is_array($recentRequests) && count($recentRequests) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Blood Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Units Needed</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Urgency</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Needed By</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($recentRequests as $request): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 text-sm font-bold bg-red-100 text-red-800 rounded-full">
                                            <?php echo $request['blood_type']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $request['units_needed']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $urgencyColors = [
                                            'critical' => 'bg-red-100 text-red-800',
                                            'urgent' => 'bg-orange-100 text-orange-800',
                                            'normal' => 'bg-blue-100 text-blue-800'
                                        ];
                                        ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $urgencyColors[$request['urgency_level']]; ?>">
                                            <?php echo ucfirst($request['urgency_level']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $statusColors = [
                                            'active' => 'bg-green-100 text-green-800',
                                            'fulfilled' => 'bg-gray-100 text-gray-800',
                                            'cancelled' => 'bg-red-100 text-red-800'
                                        ];
                                        ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $statusColors[$request['status']]; ?>">
                                            <?php echo ucfirst($request['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo formatDateTime($request['needed_by']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php if ($request['status'] === 'active'): ?>
                                        <button onclick="fulfillRequest(<?php echo $request['request_id']; ?>)" class="text-green-600 hover:text-green-900 mr-2">
                                            <i class="fas fa-check"></i> Fulfill
                                        </button>
                                        <button onclick="cancelRequest(<?php echo $request['request_id']; ?>)" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="p-6 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3"></i>
                        <p>No blood requests yet. Create your first request to get started.</p>
                        <a href="create_request.php" class="inline-block mt-4 bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">
                            <i class="fas fa-plus mr-2"></i>Create Request
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Donations -->
                <div class="bg-white rounded-lg shadow-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-history text-green-600 mr-2"></i>
                            Recent Donations
                        </h3>
                    </div>
                    <?php if (!empty($recentDonations) && is_array($recentDonations) && count($recentDonations) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Donor Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Blood Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Units</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Donation Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($recentDonations as $donation): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($donation['full_name']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 text-sm font-bold bg-red-100 text-red-800 rounded-full">
                                            <?php echo $donation['blood_type']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $donation['units_donated']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo formatDate($donation['donation_date']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">
                                            <?php echo ucfirst($donation['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="p-6 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3"></i>
                        <p>No donations recorded yet.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    <?php endif; ?>

    <script>
        function fulfillRequest(requestId) {
            if (confirm('Mark this blood request as fulfilled?')) {
                fetch('actions/update_request.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'request_id=' + requestId + '&action=fulfill'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Error updating request');
                    }
                });
            }
        }

        function cancelRequest(requestId) {
            if (confirm('Cancel this blood request?')) {
                fetch('actions/update_request.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'request_id=' + requestId + '&action=cancel'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Error cancelling request');
                    }
                });
            }
        }
    </script>
</body>
</html>
