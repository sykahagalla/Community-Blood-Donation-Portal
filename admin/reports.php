<?php
require_once '../config/config.php';
requireUserType('admin');

$db = getDB();

// Get statistics for reports
$stats = [
    'total_donors' => $db->query("SELECT COUNT(*) FROM donors")->fetchColumn(),
    'active_donors' => $db->query("SELECT COUNT(*) FROM donors d JOIN users u ON d.user_id = u.user_id WHERE u.status = 'active'")->fetchColumn(),
    'total_hospitals' => $db->query("SELECT COUNT(*) FROM hospitals")->fetchColumn(),
    'total_requests' => $db->query("SELECT COUNT(*) FROM blood_requests")->fetchColumn(),
    'active_requests' => $db->query("SELECT COUNT(*) FROM blood_requests WHERE status = 'active'")->fetchColumn(),
    'total_donations' => $db->query("SELECT COUNT(*) FROM donations")->fetchColumn(),
    'donations_this_month' => $db->query("SELECT COUNT(*) FROM donations WHERE MONTH(donation_date) = MONTH(CURRENT_DATE()) AND YEAR(donation_date) = YEAR(CURRENT_DATE())")->fetchColumn(),
    'donations_this_year' => $db->query("SELECT COUNT(*) FROM donations WHERE YEAR(donation_date) = YEAR(CURRENT_DATE())")->fetchColumn(),
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

// Monthly donation trend (last 12 months)
$monthlyTrendStmt = $db->query("
    SELECT DATE_FORMAT(donation_date, '%Y-%m') as month, 
           DATE_FORMAT(donation_date, '%b %Y') as month_label,
           COUNT(*) as count
    FROM donations
    WHERE donation_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 MONTH)
    GROUP BY month, month_label
    ORDER BY month
");
$monthlyTrend = $monthlyTrendStmt->fetchAll();

// Top donor cities
$citiesStmt = $db->query("
    SELECT city, COUNT(*) as count 
    FROM donors 
    WHERE city IS NOT NULL AND city != ''
    GROUP BY city 
    ORDER BY count DESC 
    LIMIT 10
");
$topCities = $citiesStmt->fetchAll();

// Request status breakdown
$requestStatusStmt = $db->query("
    SELECT status, COUNT(*) as count 
    FROM blood_requests 
    GROUP BY status
");
$requestStatus = $requestStatusStmt->fetchAll();

$pageTitle = 'Reports & Analytics';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100 text-sm">Hospitals</p>
                                <h3 class="text-3xl font-bold mt-1"><?php echo number_format($stats['total_hospitals']); ?></h3>
                                <p class="text-purple-100 text-xs mt-2">Registered facilities</p>
                            </div>
                            <div class="bg-white bg-opacity-20 p-4 rounded-lg">
                                <i class="fas fa-hospital text-3xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-red-100 text-sm">Blood Requests</p>
                                <h3 class="text-3xl font-bold mt-1"><?php echo number_format($stats['total_requests']); ?></h3>
                                <p class="text-red-100 text-xs mt-2"><?php echo number_format($stats['active_requests']); ?> active</p>
                            </div>
                            <div class="bg-white bg-opacity-20 p-4 rounded-lg">
                                <i class="fas fa-hand-holding-medical text-3xl"></i>
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
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Blood Type Distribution -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">
                            <i class="fas fa-chart-pie text-blue-600 mr-2"></i>Blood Type Distribution
                        </h3>
                        <canvas id="bloodTypeChart"></canvas>
                    </div>

                    <!-- Monthly Donations Trend -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">
                            <i class="fas fa-chart-line text-green-600 mr-2"></i>Donation Trends (Last 12 Months)
                        </h3>
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>

                <!-- Additional Stats -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Top Cities -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">
                            <i class="fas fa-map-marker-alt text-purple-600 mr-2"></i>Top Donor Cities
                        </h3>
                        <div class="space-y-3">
                            <?php foreach ($topCities as $city): ?>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700"><?php echo htmlspecialchars($city['city']); ?></span>
                                <span class="text-sm font-semibold text-gray-900"><?php echo $city['count']; ?> donors</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Request Status -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">
                            <i class="fas fa-clipboard-list text-red-600 mr-2"></i>Request Status Breakdown
                        </h3>
                        <canvas id="requestStatusChart"></canvas>
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
                        '#EF4444', '#F59E0B', '#10B981', '#3B82F6',
                        '#8B5CF6', '#EC4899', '#6366F1', '#14B8A6'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Monthly Trend Chart
        const monthlyTrendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
        new Chart(monthlyTrendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($monthlyTrend, 'month_label')); ?>,
                datasets: [{
                    label: 'Donations',
                    data: <?php echo json_encode(array_column($monthlyTrend, 'count')); ?>,
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
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

        // Request Status Chart
        const requestStatusCtx = document.getElementById('requestStatusChart').getContext('2d');
        new Chart(requestStatusCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($requestStatus, 'status')); ?>,
                datasets: [{
                    label: 'Requests',
                    data: <?php echo json_encode(array_column($requestStatus, 'count')); ?>,
                    backgroundColor: ['#10B981', '#3B82F6', '#6B7280', '#EF4444']
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
    </script>
</body>
</html>
