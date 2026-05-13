<?php
require_once '../config/config.php';
requireUserType('admin');

$db = getDB();

// Fetch statistics
$stats = [
    'total_donors' => $db->query("SELECT COUNT(*) FROM donors")->fetchColumn(),
    'active_donors' => $db->query("SELECT COUNT(*) FROM donors d JOIN users u ON d.user_id = u.user_id WHERE u.status = 'active'")->fetchColumn(),
    'pending_users' => $db->query("SELECT COUNT(*) FROM users WHERE status = 'pending'")->fetchColumn(),
    'total_hospitals' => $db->query("SELECT COUNT(*) FROM hospitals")->fetchColumn(),
    'active_requests' => $db->query("SELECT COUNT(*) FROM blood_requests WHERE status = 'active'")->fetchColumn(),
    'total_donations' => $db->query("SELECT COUNT(*) FROM donations")->fetchColumn(),
    'donations_this_month' => $db->query("SELECT COUNT(*) FROM donations WHERE MONTH(donation_date) = MONTH(CURRENT_DATE()) AND YEAR(donation_date) = YEAR(CURRENT_DATE())")->fetchColumn(),
];

// Blood type distribution
$bloodTypeStmt = $db->query("
    SELECT blood_type, COUNT(*) as count 
    FROM donors d 
    JOIN users u ON d.user_id = u.user_id 
    WHERE u.status = 'active' 
    GROUP BY blood_type 
    ORDER BY blood_type
");
$bloodTypeData = $bloodTypeStmt->fetchAll();

// Recent blood requests
$recentRequestsStmt = $db->query("
    SELECT br.*, h.hospital_name 
    FROM blood_requests br 
    JOIN hospitals h ON br.hospital_id = h.hospital_id 
    ORDER BY br.created_at DESC 
    LIMIT 10
");
$recentRequests = $recentRequestsStmt->fetchAll();

// Pending user approvals
$pendingUsersStmt = $db->query("
    SELECT u.*, 
           COALESCE(d.full_name, h.hospital_name) as name,
           COALESCE(d.phone, h.phone) as phone
    FROM users u
    LEFT JOIN donors d ON u.user_id = d.user_id
    LEFT JOIN hospitals h ON u.user_id = h.user_id
    WHERE u.status = 'pending'
    ORDER BY u.created_at DESC
");
$pendingUsers = $pendingUsersStmt->fetchAll();

// Monthly donation trend (last 6 months)
$monthlyTrendStmt = $db->query("
    SELECT DATE_FORMAT(donation_date, '%Y-%m') as month, COUNT(*) as count
    FROM donations
    WHERE donation_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
    GROUP BY month
    ORDER BY month
");
$monthlyTrend = $monthlyTrendStmt->fetchAll();

$pageTitle = 'Admin Dashboard';
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

            <!-- Dashboard Content -->
            <main class="p-6">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm">Total Donors</p>
                                <h3 class="text-3xl font-bold mt-1"><?php echo number_format($stats['total_donors']); ?></h3>
                                <p class="text-blue-100 text-xs mt-2"><?php echo number_format($stats['active_donors']); ?> active</p>
                            </div>
                            <div class="bg-white bg-opacity-20 p-4 rounded-lg">
                                <i class="fas fa-users text-3xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-red-100 text-sm">Active Requests</p>
                                <h3 class="text-3xl font-bold mt-1"><?php echo number_format($stats['active_requests']); ?></h3>
                                <p class="text-red-100 text-xs mt-2">Need urgent attention</p>
                            </div>
                            <div class="bg-white bg-opacity-20 p-4 rounded-lg">
                                <i class="fas fa-exclamation-circle text-3xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm">Total Donations</p>
                                <h3 class="text-3xl font-bold mt-1"><?php echo number_format($stats['total_donations']); ?></h3>
                                <p class="text-green-100 text-xs mt-2"><?php echo number_format($stats['donations_this_month']); ?> this month</p>
                            </div>
                            <div class="bg-white bg-opacity-20 p-4 rounded-lg">
                                <i class="fas fa-tint text-3xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-yellow-100 text-sm">Pending Approvals</p>
                                <h3 class="text-3xl font-bold mt-1"><?php echo number_format($stats['pending_users']); ?></h3>
                                <p class="text-yellow-100 text-xs mt-2">Awaiting review</p>
                            </div>
                            <div class="bg-white bg-opacity-20 p-4 rounded-lg">
                                <i class="fas fa-clock text-3xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Blood Type Distribution -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">
                            <i class="fas fa-chart-pie text-purple-600 mr-2"></i>
                            Blood Type Distribution
                        </h3>
                        <canvas id="bloodTypeChart"></canvas>
                    </div>

                    <!-- Monthly Donation Trend -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">
                            <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                            Donation Trend (Last 6 Months)
                        </h3>
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                <!-- Pending Approvals -->
                <?php if (count($pendingUsers) > 0): ?>
                <div class="bg-white rounded-lg shadow-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-user-check text-yellow-600 mr-2"></i>
                            Pending User Approvals
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registered</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($pendingUsers as $user): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['name'] ?? 'N/A'); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $user['user_type'] === 'donor' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo ucfirst($user['user_type']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo timeAgo($user['created_at']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <button onclick="approveUser(<?php echo $user['user_id']; ?>)" class="text-green-600 hover:text-green-900 mr-3">
                                            <i class="fas fa-check-circle"></i> Approve
                                        </button>
                                        <button onclick="rejectUser(<?php echo $user['user_id']; ?>)" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-times-circle"></i> Reject
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Recent Blood Requests -->
                <div class="bg-white rounded-lg shadow-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-list text-red-600 mr-2"></i>
                            Recent Blood Requests
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hospital</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Blood Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Units</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Urgency</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($recentRequests as $request): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($request['hospital_name']); ?></div>
                                    </td>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo timeAgo($request['created_at']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Blood Type Distribution Chart
        const bloodTypeCtx = document.getElementById('bloodTypeChart').getContext('2d');
        new Chart(bloodTypeCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($bloodTypeData, 'blood_type')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($bloodTypeData, 'count')); ?>,
                    backgroundColor: [
                        '#ef4444', '#f59e0b', '#10b981', '#3b82f6',
                        '#8b5cf6', '#ec4899', '#6366f1', '#14b8a6'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });

        // Monthly Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($monthlyTrend, 'month')); ?>,
                datasets: [{
                    label: 'Donations',
                    data: <?php echo json_encode(array_column($monthlyTrend, 'count')); ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // User approval functions
        function approveUser(userId) {
            if (confirm('Are you sure you want to approve this user?')) {
                fetch('actions/approve_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'user_id=' + userId + '&action=approve'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Error approving user');
                    }
                });
            }
        }

        function rejectUser(userId) {
            if (confirm('Are you sure you want to reject this user?')) {
                fetch('actions/approve_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'user_id=' + userId + '&action=reject'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Error rejecting user');
                    }
                });
            }
        }
    </script>
</body>
</html>
