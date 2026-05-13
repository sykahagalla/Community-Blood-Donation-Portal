<?php
require_once '../config/config.php';
requireUserType('donor');

$db = getDB();

// Get donor information
$donorStmt = $db->prepare("
    SELECT d.*, u.status, u.email 
    FROM donors d 
    JOIN users u ON d.user_id = u.user_id 
    WHERE d.user_id = ?
");
$donorStmt->execute([$_SESSION['user_id']]);
$donor = $donorStmt->fetch();

if (!$donor) {
    redirect('auth/logout.php');
}

// Check if account is approved
if ($donor['status'] === 'pending') {
    $isPending = true;
} else {
    $isPending = false;
    
    // Get donation statistics
    $donationCountStmt = $db->prepare("SELECT COUNT(*) FROM donations WHERE donor_id = ?");
    $donationCountStmt->execute([$donor['donor_id']]);
    $totalDonations = $donationCountStmt->fetchColumn();
    
    // Get active blood requests matching donor's blood type
    $activeRequestsStmt = $db->prepare("
        SELECT br.*, h.hospital_name, h.city, h.phone 
        FROM blood_requests br 
        JOIN hospitals h ON br.hospital_id = h.hospital_id 
        WHERE br.blood_type = ? AND br.status = 'active'
        ORDER BY 
            CASE br.urgency_level 
                WHEN 'critical' THEN 1
                WHEN 'urgent' THEN 2
                WHEN 'normal' THEN 3
            END,
            br.created_at DESC
        LIMIT 10
    ");
    $activeRequestsStmt->execute([$donor['blood_type']]);
    $activeRequests = $activeRequestsStmt->fetchAll();
    
    // Get donation history
    $donationHistoryStmt = $db->prepare("
        SELECT d.*, h.hospital_name 
        FROM donations d 
        JOIN hospitals h ON d.hospital_id = h.hospital_id 
        WHERE d.donor_id = ? 
        ORDER BY d.donation_date DESC 
        LIMIT 10
    ");
    $donationHistoryStmt->execute([$donor['donor_id']]);
    $donationHistory = $donationHistoryStmt->fetchAll();
    
    // Get upcoming blood drives
    $bloodDrivesStmt = $db->query("
        SELECT * FROM blood_drives 
        WHERE status = 'upcoming' AND drive_date >= CURRENT_DATE()
        ORDER BY drive_date ASC 
        LIMIT 5
    ");
    $bloodDrives = $bloodDrivesStmt->fetchAll();
    
    // Check eligibility
    $isEligible = canDonate($donor['last_donation_date']);
    $nextDonationDate = getNextDonationDate($donor['last_donation_date']);
}

$pageTitle = 'Donor Dashboard';
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
                Thank you for registering as a blood donor! Your account is currently being reviewed by our administrators. 
                You'll receive an email notification once your account is approved.
            </p>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-blue-800">
                    <strong>What's next?</strong><br>
                    Our team typically reviews new registrations within 24-48 hours. 
                    We verify all donor information to ensure the safety and reliability of our platform.
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
                <div class="bg-gradient-to-r from-purple-600 to-purple-800 rounded-lg shadow-lg p-6 mb-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold mb-2">Welcome back, <?php echo htmlspecialchars($donor['full_name']); ?>! 👋</h2>
                            <p class="text-purple-100">Thank you for being a life-saver in our community.</p>
                        </div>
                        <div class="text-center">
                            <div class="bg-white bg-opacity-20 rounded-lg px-6 py-4">
                                <div class="text-4xl font-bold"><?php echo $donor['blood_type']; ?></div>
                                <div class="text-sm text-purple-100">Your Blood Type</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Total Donations</p>
                                <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo $totalDonations; ?></h3>
                            </div>
                            <div class="bg-red-100 p-3 rounded-lg">
                                <i class="fas fa-tint text-red-600 text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Lives Saved</p>
                                <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo $totalDonations * 3; ?></h3>
                            </div>
                            <div class="bg-green-100 p-3 rounded-lg">
                                <i class="fas fa-heart text-green-600 text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Active Requests</p>
                                <h3 class="text-3xl font-bold text-gray-800 mt-1"><?php echo count($activeRequests); ?></h3>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-lg">
                                <i class="fas fa-hand-holding-medical text-blue-600 text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Donation Status</p>
                                <?php if ($isEligible): ?>
                                <h3 class="text-lg font-bold text-green-600 mt-1">Eligible</h3>
                                <?php else: ?>
                                <h3 class="text-lg font-bold text-orange-600 mt-1">Not Yet</h3>
                                <?php endif; ?>
                                <p class="text-xs text-gray-500 mt-1"><?php echo $nextDonationDate; ?></p>
                            </div>
                            <div class="bg-<?php echo $isEligible ? 'green' : 'orange'; ?>-100 p-3 rounded-lg">
                                <i class="fas fa-<?php echo $isEligible ? 'check' : 'clock'; ?>-circle text-<?php echo $isEligible ? 'green' : 'orange'; ?>-600 text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Blood Requests -->
                <?php if (count($activeRequests) > 0): ?>
                <div class="bg-white rounded-lg shadow-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-exclamation-circle text-red-600 mr-2"></i>
                            Active Blood Requests for <?php echo $donor['blood_type']; ?>
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">People near you need your help!</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <?php foreach ($activeRequests as $request): ?>
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-red-500 transition">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2">
                                            <h4 class="font-bold text-gray-800 mr-3"><?php echo htmlspecialchars($request['hospital_name']); ?></h4>
                                            <?php
                                            $urgencyColors = [
                                                'critical' => 'bg-red-500',
                                                'urgent' => 'bg-orange-500',
                                                'normal' => 'bg-blue-500'
                                            ];
                                            ?>
                                            <span class="px-3 py-1 text-xs font-bold text-white rounded-full <?php echo $urgencyColors[$request['urgency_level']]; ?>">
                                                <?php echo strtoupper($request['urgency_level']); ?>
                                            </span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                                            <div><i class="fas fa-tint mr-2 text-red-600"></i>Blood Type: <strong><?php echo $request['blood_type']; ?></strong></div>
                                            <div><i class="fas fa-flask mr-2 text-blue-600"></i>Units Needed: <strong><?php echo $request['units_needed']; ?></strong></div>
                                            <div><i class="fas fa-map-marker-alt mr-2 text-green-600"></i><?php echo htmlspecialchars($request['city']); ?></div>
                                            <div><i class="fas fa-phone mr-2 text-purple-600"></i><?php echo htmlspecialchars($request['phone']); ?></div>
                                        </div>
                                        <p class="text-sm text-gray-700 mt-3"><strong>Reason:</strong> <?php echo htmlspecialchars($request['reason']); ?></p>
                                        <p class="text-xs text-gray-500 mt-2">
                                            <i class="fas fa-clock mr-1"></i>Needed by: <?php echo formatDateTime($request['needed_by']); ?>
                                        </p>
                                    </div>
                                    <div class="ml-4">
                                        <?php if ($isEligible): ?>
                                        <a href="tel:<?php echo $request['contact_number']; ?>" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition inline-block text-center">
                                            <i class="fas fa-phone mr-2"></i>Respond
                                        </a>
                                        <?php else: ?>
                                        <button disabled class="bg-gray-400 text-white px-6 py-2 rounded-lg cursor-not-allowed" title="Not eligible to donate yet">
                                            Not Eligible
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-blue-600 text-2xl mr-4"></i>
                        <div>
                            <h4 class="font-bold text-blue-800">No Active Requests</h4>
                            <p class="text-blue-700 text-sm">There are currently no active blood requests for your blood type. We'll notify you when someone needs your help!</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Donation History -->
                    <div class="bg-white rounded-lg shadow-lg">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800">
                                <i class="fas fa-history text-purple-600 mr-2"></i>
                                Donation History
                            </h3>
                        </div>
                        <?php if (count($donationHistory) > 0): ?>
                        <div class="p-6">
                            <div class="space-y-3">
                                <?php foreach ($donationHistory as $donation): ?>
                                <div class="border-l-4 border-red-500 pl-4 py-2">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($donation['hospital_name']); ?></h4>
                                            <p class="text-sm text-gray-600"><?php echo formatDate($donation['donation_date']); ?></p>
                                            <p class="text-xs text-gray-500 mt-1">Units: <?php echo $donation['units_donated']; ?></p>
                                        </div>
                                        <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded">
                                            <?php echo ucfirst($donation['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="p-6 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-3"></i>
                            <p>No donation history yet. Make your first donation today!</p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Upcoming Blood Drives -->
                    <div class="bg-white rounded-lg shadow-lg">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800">
                                <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>
                                Upcoming Blood Drives
                            </h3>
                        </div>
                        <?php if (count($bloodDrives) > 0): ?>
                        <div class="p-6">
                            <div class="space-y-3">
                                <?php foreach ($bloodDrives as $drive): ?>
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-500 transition">
                                    <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($drive['title']); ?></h4>
                                    <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($drive['location']); ?></p>
                                    <div class="flex items-center text-sm text-gray-500 mt-2">
                                        <i class="fas fa-calendar mr-2 text-blue-600"></i>
                                        <?php echo formatDate($drive['drive_date']); ?>
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-clock mr-2 text-green-600"></i>
                                        <?php echo date('g:i A', strtotime($drive['start_time'])); ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="p-6 text-center text-gray-500">
                            <i class="fas fa-calendar-times text-4xl mb-3"></i>
                            <p>No upcoming blood drives scheduled.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>
