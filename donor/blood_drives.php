<?php
require_once '../config/config.php';
requireUserType('donor');

$db = getDB();

// Get upcoming and ongoing blood drives
$drivesStmt = $db->query("
    SELECT bd.*, h.hospital_name, h.city as hospital_city, h.phone as hospital_phone
    FROM blood_drives bd
    LEFT JOIN hospitals h ON bd.hospital_id = h.hospital_id
    WHERE bd.status IN ('upcoming', 'ongoing')
    ORDER BY bd.drive_date ASC
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
                <div class="bg-purple-100 border-l-4 border-purple-500 text-purple-700 p-4 mb-6">
                    <p class="font-medium">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Join upcoming blood donation drives in your area and save lives!
                    </p>
                </div>

                <?php if (empty($drives)): ?>
                <div class="bg-white rounded-lg shadow-lg p-12 text-center">
                    <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-600 mb-2">No Upcoming Drives</h3>
                    <p class="text-gray-500">There are currently no blood drives scheduled. Check back later!</p>
                </div>
                <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($drives as $drive): ?>
                    <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <h4 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($drive['title']); ?></h4>
                                <?php
                                $statusColors = [
                                    'upcoming' => 'bg-blue-100 text-blue-800',
                                    'ongoing' => 'bg-green-100 text-green-800'
                                ];
                                ?>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $statusColors[$drive['status']]; ?>">
                                    <?php echo ucfirst($drive['status']); ?>
                                </span>
                            </div>
                            
                            <div class="space-y-3 mb-4">
                                <div class="flex items-center text-gray-700">
                                    <i class="fas fa-calendar text-blue-600 w-6"></i>
                                    <span class="font-semibold"><?php echo date('M d, Y', strtotime($drive['drive_date'])); ?></span>
                                </div>
                                
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-clock text-green-600 w-6"></i>
                                    <span><?php echo date('h:i A', strtotime($drive['start_time'])) . ' - ' . date('h:i A', strtotime($drive['end_time'])); ?></span>
                                </div>
                                
                                <div class="flex items-start text-gray-600">
                                    <i class="fas fa-map-marker-alt text-red-600 w-6 mt-1"></i>
                                    <span><?php echo htmlspecialchars($drive['location']); ?></span>
                                </div>
                                
                                <?php if ($drive['hospital_name']): ?>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-hospital text-purple-600 w-6"></i>
                                    <span><?php echo htmlspecialchars($drive['hospital_name']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($drive['hospital_phone']): ?>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-phone text-green-600 w-6"></i>
                                    <span><?php echo htmlspecialchars($drive['hospital_phone']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($drive['description']): ?>
                            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                <p class="text-sm text-gray-700 line-clamp-3"><?php echo htmlspecialchars($drive['description']); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <div class="flex space-x-2">
                                <?php if ($drive['hospital_phone']): ?>
                                <a href="tel:<?php echo $drive['hospital_phone']; ?>" 
                                   class="flex-1 bg-green-600 text-white text-center px-4 py-2 rounded-lg hover:bg-green-700 transition text-sm">
                                    <i class="fas fa-phone mr-1"></i>Contact
                                </a>
                                <?php endif; ?>
                                <button class="flex-1 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition text-sm">
                                    <i class="fas fa-hand-holding-heart mr-1"></i>Register
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</body>
</html>
