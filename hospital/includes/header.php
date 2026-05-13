<?php
// Load hospital information if not already loaded
if (!isset($hospital)) {
    $hospitalStmt = $db->prepare("
        SELECT h.*, u.status, u.email 
        FROM hospitals h 
        JOIN users u ON h.user_id = u.user_id 
        WHERE h.user_id = ?
    ");
    $hospitalStmt->execute([$_SESSION['user_id']]);
    $hospital = $hospitalStmt->fetch();
}
?>
<header class="bg-white shadow-sm">
    <div class="flex items-center justify-between px-6 py-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800"><?php echo $pageTitle ?? 'Dashboard'; ?></h2>
            <p class="text-sm text-gray-600">Manage blood requests and donations</p>
        </div>
        
        <div class="flex items-center space-x-4">
            <!-- Notifications -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition">
                    <i class="fas fa-bell text-xl"></i>
                    <?php
                    // Count active requests and recent donations
                    $activeRequestsCount = $db->prepare("SELECT COUNT(*) FROM blood_requests WHERE hospital_id = ? AND status = 'active'");
                    $activeRequestsCount->execute([$hospital['hospital_id']]);
                    $activeRequests = $activeRequestsCount->fetchColumn();
                    
                    $recentDonationsCount = $db->prepare("
                        SELECT COUNT(*) 
                        FROM donations d
                        JOIN blood_requests br ON d.request_id = br.request_id
                        WHERE br.hospital_id = ? AND d.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    ");
                    $recentDonationsCount->execute([$hospital['hospital_id']]);
                    $recentDonations = $recentDonationsCount->fetchColumn();
                    
                    $notifCount = $activeRequests + $recentDonations;
                    if ($notifCount > 0):
                    ?>
                    <span class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center animate-pulse">
                        <?php echo $notifCount; ?>
                    </span>
                    <?php endif; ?>
                </button>
                
                <!-- Notification Dropdown -->
                <div x-show="open" 
                     @click.away="open = false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-xl border border-gray-200 z-50"
                     style="display: none;">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-800">Notifications</h3>
                        <p class="text-xs text-gray-500 mt-1">
                            <?php echo $activeRequests; ?> active request(s) • <?php echo $recentDonations; ?> recent donation(s)
                        </p>
                    </div>
                    
                    <div class="max-h-96 overflow-y-auto">
                        <?php
                        // Get active blood requests
                        $requestsStmt = $db->prepare("
                            SELECT * FROM blood_requests
                            WHERE hospital_id = ? AND status = 'active'
                            ORDER BY 
                                CASE urgency_level
                                    WHEN 'critical' THEN 1
                                    WHEN 'urgent' THEN 2
                                    WHEN 'normal' THEN 3
                                END,
                                created_at DESC
                            LIMIT 5
                        ");
                        $requestsStmt->execute([$hospital['hospital_id']]);
                        $requests = $requestsStmt->fetchAll();
                        
                        // Get recent donations
                        $donationsStmt = $db->prepare("
                            SELECT d.*, br.blood_type, br.patient_name, don.full_name as donor_name
                            FROM donations d
                            JOIN blood_requests br ON d.request_id = br.request_id
                            JOIN donors don ON d.donor_id = don.donor_id
                            WHERE br.hospital_id = ? AND d.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                            ORDER BY d.created_at DESC
                            LIMIT 5
                        ");
                        $donationsStmt->execute([$hospital['hospital_id']]);
                        $donations = $donationsStmt->fetchAll();
                        
                        $hasNotifications = !empty($requests) || !empty($donations);
                        
                        if (!$hasNotifications):
                        ?>
                        <div class="p-6 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2 text-gray-400"></i>
                            <p>No recent notifications</p>
                            <p class="text-xs mt-1">All caught up!</p>
                        </div>
                        <?php else: ?>
                        
                        <!-- Active Requests Section -->
                        <?php if (!empty($requests)): ?>
                        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                            <p class="text-xs font-semibold text-gray-700 uppercase">Active Blood Requests</p>
                        </div>
                        <?php foreach ($requests as $request): ?>
                        <div class="p-4 hover:bg-gray-50 border-b border-gray-100 transition">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full <?php 
                                        echo $request['urgency_level'] === 'critical' ? 'bg-red-100' : 
                                            ($request['urgency_level'] === 'urgent' ? 'bg-orange-100' : 'bg-blue-100'); 
                                    ?> flex items-center justify-center">
                                        <i class="fas fa-tint <?php 
                                            echo $request['urgency_level'] === 'critical' ? 'text-red-600' : 
                                                ($request['urgency_level'] === 'urgent' ? 'text-orange-600' : 'text-blue-600'); 
                                        ?>"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-800">
                                        <?php echo $request['blood_type']; ?> Blood Request
                                    </p>
                                    <div class="flex items-center mt-1 space-x-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?php 
                                            echo $request['urgency_level'] === 'critical' ? 'bg-red-100 text-red-800' : 
                                                ($request['urgency_level'] === 'urgent' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800'); 
                                        ?>">
                                            <?php echo ucfirst($request['urgency_level']); ?>
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            <?php echo $request['units_needed']; ?> unit(s)
                                        </span>
                                    </div>
                                    <?php if ($request['patient_name']): ?>
                                    <p class="text-xs text-gray-600 mt-1">
                                        <i class="fas fa-user-injured mr-1"></i><?php echo htmlspecialchars($request['patient_name']); ?>
                                    </p>
                                    <?php endif; ?>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="far fa-clock mr-1"></i>
                                        Needed by: <?php echo formatDate($request['needed_by']); ?>
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        Posted <?php echo timeAgo($request['created_at']); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="blood_requests.php" 
                                   class="block text-center px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition">
                                    View Details
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <!-- Recent Donations Section -->
                        <?php if (!empty($donations)): ?>
                        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                            <p class="text-xs font-semibold text-gray-700 uppercase">Recent Donations (Last 7 Days)</p>
                        </div>
                        <?php foreach ($donations as $donation): ?>
                        <div class="p-4 hover:bg-gray-50 border-b border-gray-100 transition">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                                        <i class="fas fa-check-circle text-green-600"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-800">
                                        Donation Received
                                    </p>
                                    <p class="text-xs text-gray-600 mt-1">
                                        <i class="fas fa-user mr-1"></i>
                                        Donor: <?php echo htmlspecialchars($donation['donor_name']); ?>
                                    </p>
                                    <div class="flex items-center mt-1 space-x-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                            <?php echo $donation['blood_type']; ?>
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                            <?php echo ucfirst($donation['status']); ?>
                                        </span>
                                    </div>
                                    <?php if ($donation['patient_name']): ?>
                                    <p class="text-xs text-gray-600 mt-1">
                                        <i class="fas fa-user-injured mr-1"></i>
                                        For: <?php echo htmlspecialchars($donation['patient_name']); ?>
                                    </p>
                                    <?php endif; ?>
                                    <p class="text-xs text-gray-400 mt-1">
                                        <i class="far fa-clock mr-1"></i><?php echo timeAgo($donation['created_at']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($hasNotifications): ?>
                    <div class="p-3 border-t border-gray-200 bg-gray-50">
                        <a href="blood_requests.php" class="block text-center text-sm font-medium text-purple-600 hover:text-purple-700">
                            View All Requests →
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Profile -->
            <div class="flex items-center">
                <div class="mr-3 text-right">
                    <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($hospital['hospital_name']); ?></p>
                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($hospital['city']); ?></p>
                </div>
                <div class="h-10 w-10 rounded-full bg-red-600 flex items-center justify-center text-white font-bold">
                    <i class="fas fa-hospital"></i>
                </div>
            </div>
        </div>
    </div>
</header>
