<?php
require_once '../config/config.php';
requireUserType('admin');

$db = getDB();

// Get all blood drives
$drivesStmt = $db->query("
    SELECT bd.*, h.hospital_name
    FROM blood_drives bd
    LEFT JOIN hospitals h ON bd.hospital_id = h.hospital_id
    ORDER BY bd.drive_date DESC
");
$drives = $drivesStmt->fetchAll();

$pageTitle = 'Blood Drives';
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
                <div class="bg-white rounded-lg shadow-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-calendar-alt text-purple-600 mr-2"></i>All Blood Drives
                        </h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                        <?php foreach ($drives as $drive): ?>
                        <div class="bg-white border border-gray-200 rounded-lg shadow-md hover:shadow-lg transition">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <h4 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($drive['title']); ?></h4>
                                    <?php
                                    $statusColors = [
                                        'upcoming' => 'bg-blue-100 text-blue-800',
                                        'ongoing' => 'bg-green-100 text-green-800',
                                        'completed' => 'bg-gray-100 text-gray-800',
                                        'cancelled' => 'bg-red-100 text-red-800'
                                    ];
                                    ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $statusColors[$drive['status']]; ?>">
                                        <?php echo ucfirst($drive['status']); ?>
                                    </span>
                                </div>
                                
                                <div class="space-y-2 text-sm text-gray-600">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar text-blue-600 w-5"></i>
                                        <span><?php echo date('M d, Y', strtotime($drive['drive_date'])); ?></span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-clock text-blue-600 w-5"></i>
                                        <span><?php echo date('h:i A', strtotime($drive['start_time'])) . ' - ' . date('h:i A', strtotime($drive['end_time'])); ?></span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-map-marker-alt text-blue-600 w-5"></i>
                                        <span><?php echo htmlspecialchars($drive['location']); ?></span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-hospital text-blue-600 w-5"></i>
                                        <span><?php echo htmlspecialchars($drive['hospital_name'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                
                                <?php if ($drive['description']): ?>
                                <p class="mt-4 text-sm text-gray-500 line-clamp-2"><?php echo htmlspecialchars($drive['description']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
