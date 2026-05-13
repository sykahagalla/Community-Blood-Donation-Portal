<?php
require_once '../config/config.php';
requireUserType('donor');

$db = getDB();

// Get donor's blood type
$donorStmt = $db->prepare("SELECT blood_type FROM donors WHERE user_id = ?");
$donorStmt->execute([$_SESSION['user_id']]);
$donorInfo = $donorStmt->fetch();

// Get all active blood requests matching donor's blood type
$requestsStmt = $db->prepare("
    SELECT br.*, h.hospital_name, h.city as hospital_city, h.phone as hospital_phone
    FROM blood_requests br
    JOIN hospitals h ON br.hospital_id = h.hospital_id
    WHERE br.status = 'active' AND br.blood_type = ?
    ORDER BY 
        CASE br.urgency_level 
            WHEN 'critical' THEN 1 
            WHEN 'urgent' THEN 2 
            WHEN 'normal' THEN 3 
        END,
        br.needed_by ASC
");
$requestsStmt->execute([$donorInfo['blood_type']]);
$requests = $requestsStmt->fetchAll();

$pageTitle = 'Blood Requests';
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
                <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6">
                    <p class="font-medium">
                        <i class="fas fa-info-circle mr-2"></i>
                        Showing blood requests matching your blood type: <span class="font-bold"><?php echo $donorInfo['blood_type']; ?></span>
                    </p>
                </div>

                <?php if (empty($requests)): ?>
                <div class="bg-white rounded-lg shadow-lg p-12 text-center">
                    <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-600 mb-2">No Active Requests</h3>
                    <p class="text-gray-500">There are currently no blood requests matching your blood type.</p>
                </div>
                <?php else: ?>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <?php foreach ($requests as $request): ?>
                    <div class="bg-white rounded-lg shadow-lg border-l-4 
                        <?php echo $request['urgency_level'] === 'critical' ? 'border-red-600' : 
                                  ($request['urgency_level'] === 'urgent' ? 'border-orange-500' : 'border-blue-500'); ?>">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h4 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($request['hospital_name']); ?></h4>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($request['hospital_city'] ?? 'Location not specified'); ?></p>
                                </div>
                                <?php
                                $urgencyColors = [
                                    'critical' => 'bg-red-100 text-red-800',
                                    'urgent' => 'bg-orange-100 text-orange-800',
                                    'normal' => 'bg-blue-100 text-blue-800'
                                ];
                                ?>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $urgencyColors[$request['urgency_level']]; ?>">
                                    <?php echo strtoupper($request['urgency_level']); ?>
                                </span>
                            </div>
                            
                            <div class="space-y-3 mb-4">
                                <div class="flex items-center text-gray-700">
                                    <i class="fas fa-tint text-red-600 w-6"></i>
                                    <span class="font-semibold"><?php echo $request['blood_type']; ?></span>
                                    <span class="ml-2 text-gray-600">- <?php echo $request['units_needed']; ?> units needed</span>
                                </div>
                                
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-calendar text-blue-600 w-6"></i>
                                    <span>Required by: <?php echo date('M d, Y', strtotime($request['needed_by'])); ?></span>
                                </div>
                                
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-phone text-green-600 w-6"></i>
                                    <span><?php echo htmlspecialchars($request['hospital_phone'] ?? 'Contact not available'); ?></span>
                                </div>
                            </div>
                            
                            <?php if ($request['reason']): ?>
                            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                <p class="text-sm text-gray-700"><?php echo htmlspecialchars($request['reason']); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <div class="flex space-x-2">
                                <?php if (!empty($request['hospital_phone'])): ?>
                                <a href="tel:<?php echo $request['hospital_phone']; ?>" 
                                   class="flex-1 bg-green-600 text-white text-center px-4 py-2 rounded-lg hover:bg-green-700 transition">
                                    <i class="fas fa-phone mr-2"></i>Call Hospital
                                </a>
                                <?php else: ?>
                                <button disabled 
                                   class="flex-1 bg-gray-400 text-white text-center px-4 py-2 rounded-lg cursor-not-allowed">
                                    <i class="fas fa-phone mr-2"></i>No Contact Available
                                </button>
                                <?php endif; ?>
                                <button class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                                    <i class="fas fa-hand-holding-heart mr-2"></i>I Can Donate
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
