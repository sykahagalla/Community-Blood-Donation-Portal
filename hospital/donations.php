<?php
require_once '../config/config.php';
requireUserType('hospital');

$db = getDB();

// Get hospital_id
$hospitalStmt = $db->prepare("SELECT hospital_id FROM hospitals WHERE user_id = ?");
$hospitalStmt->execute([$_SESSION['user_id']]);
$hospitalInfo = $hospitalStmt->fetch();

// Get all donations for this hospital
$donationsStmt = $db->prepare("
    SELECT d.*, don.full_name as donor_name, don.blood_type, don.phone as donor_phone
    FROM donations d
    JOIN donors don ON d.donor_id = don.donor_id
    WHERE d.hospital_id = ?
    ORDER BY d.donation_date DESC
");
$donationsStmt->execute([$hospitalInfo['hospital_id']]);
$donations = $donationsStmt->fetchAll();

// Get statistics
$stats = [
    'total_donations' => count($donations),
    'total_units' => array_sum(array_column($donations, 'units_donated')),
    'this_month' => count(array_filter($donations, function($d) {
        return date('Y-m', strtotime($d['donation_date'])) === date('Y-m');
    }))
];

$pageTitle = 'Donation Records';
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
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm">Total Donations</p>
                                <h3 class="text-4xl font-bold mt-1"><?php echo $stats['total_donations']; ?></h3>
                            </div>
                            <div class="bg-white bg-opacity-20 p-4 rounded-lg">
                                <i class="fas fa-tint text-3xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-red-100 text-sm">Total Units Collected</p>
                                <h3 class="text-4xl font-bold mt-1"><?php echo $stats['total_units']; ?></h3>
                            </div>
                            <div class="bg-white bg-opacity-20 p-4 rounded-lg">
                                <i class="fas fa-heartbeat text-3xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm">This Month</p>
                                <h3 class="text-4xl font-bold mt-1"><?php echo $stats['this_month']; ?></h3>
                            </div>
                            <div class="bg-white bg-opacity-20 p-4 rounded-lg">
                                <i class="fas fa-calendar-alt text-3xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-tint text-red-600 mr-2"></i>Donation Records
                        </h3>
                    </div>
                    
                    <?php if (empty($donations)): ?>
                    <div class="p-12 text-center">
                        <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-bold text-gray-600 mb-2">No Donations Yet</h3>
                        <p class="text-gray-500">Donation records will appear here once you receive blood donations.</p>
                    </div>
                    <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Donor Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Blood Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Units</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($donations as $donation): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo date('M d, Y', strtotime($donation['donation_date'])); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo date('h:i A', strtotime($donation['donation_date'])); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($donation['donor_name']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 text-sm font-bold bg-red-100 text-red-800 rounded-full">
                                            <?php echo $donation['blood_type']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo htmlspecialchars($donation['donor_phone']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-semibold text-gray-900"><?php echo $donation['units_donated']; ?> units</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $statusColors = [
                                            'completed' => 'bg-green-100 text-green-800',
                                            'scheduled' => 'bg-blue-100 text-blue-800',
                                            'cancelled' => 'bg-red-100 text-red-800'
                                        ];
                                        ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $statusColors[$donation['status']]; ?>">
                                            <?php echo ucfirst($donation['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php echo htmlspecialchars($donation['notes'] ?? '-'); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
