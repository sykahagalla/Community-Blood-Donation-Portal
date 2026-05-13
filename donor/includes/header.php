<?php
// Load donor information if not already loaded
if (!isset($donor)) {
    $donorStmt = $db->prepare("
        SELECT d.*, u.status, u.email 
        FROM donors d 
        JOIN users u ON d.user_id = u.user_id 
        WHERE d.user_id = ?
    ");
    $donorStmt->execute([$_SESSION['user_id']]);
    $donor = $donorStmt->fetch();
}
?>
<header class="bg-white shadow-sm">
    <div class="flex items-center justify-between px-6 py-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800"><?php echo $pageTitle ?? 'Dashboard'; ?></h2>
            <p class="text-sm text-gray-600">Manage your donations and save lives</p>
        </div>
        
        <div class="flex items-center space-x-4">
            <!-- Notifications -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition">
                    <i class="fas fa-bell text-xl"></i>
                    <?php
                    $requestCount = $db->prepare("SELECT COUNT(*) FROM blood_requests WHERE blood_type = ? AND status = 'active'");
                    $requestCount->execute([$donor['blood_type']]);
                    $notifCount = $requestCount->fetchColumn();
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
                        <h3 class="font-semibold text-gray-800">Blood Requests</h3>
                        <p class="text-xs text-gray-500 mt-1"><?php echo $notifCount; ?> active request(s) for <?php echo $donor['blood_type']; ?></p>
                    </div>
                    
                    <div class="max-h-96 overflow-y-auto">
                        <?php
                        $bloodRequests = $db->prepare("
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
                        $bloodRequests->execute([$donor['blood_type']]);
                        $requests = $bloodRequests->fetchAll();
                        
                        if (empty($requests)):
                        ?>
                        <div class="p-6 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2 text-gray-400"></i>
                            <p>No active blood requests</p>
                            <p class="text-xs mt-1">You'll be notified when your blood type is needed</p>
                        </div>
                        <?php else: ?>
                        <?php foreach ($requests as $request): ?>
                        <div class="p-4 hover:bg-gray-50 border-b border-gray-100 transition">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex items-start space-x-3 flex-1">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-full <?php 
                                            echo $request['urgency_level'] === 'critical' ? 'bg-red-100' : 
                                                ($request['urgency_level'] === 'urgent' ? 'bg-orange-100' : 'bg-blue-100'); 
                                        ?> flex items-center justify-center">
                                            <i class="fas fa-hospital <?php 
                                                echo $request['urgency_level'] === 'critical' ? 'text-red-600' : 
                                                    ($request['urgency_level'] === 'urgent' ? 'text-orange-600' : 'text-blue-600'); 
                                            ?>"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-800">
                                            <?php echo htmlspecialchars($request['hospital_name']); ?>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <i class="fas fa-map-marker-alt mr-1"></i><?php echo htmlspecialchars($request['city']); ?>
                                        </p>
                                        <div class="flex items-center mt-1 space-x-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                                <?php echo $request['blood_type']; ?>
                                            </span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?php 
                                                echo $request['urgency_level'] === 'critical' ? 'bg-red-100 text-red-800' : 
                                                    ($request['urgency_level'] === 'urgent' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800'); 
                                            ?>">
                                                <i class="fas fa-exclamation-circle mr-1"></i><?php echo ucfirst($request['urgency_level']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="ml-13 space-y-1">
                                <p class="text-xs text-gray-600">
                                    <i class="fas fa-tint mr-1 text-red-500"></i>
                                    <strong><?php echo $request['units_needed']; ?> unit(s)</strong> needed
                                </p>
                                <?php if ($request['patient_name']): ?>
                                <p class="text-xs text-gray-600">
                                    <i class="fas fa-user-injured mr-1"></i>
                                    Patient: <?php echo htmlspecialchars($request['patient_name']); ?>
                                </p>
                                <?php endif; ?>
                                <p class="text-xs text-gray-600">
                                    <i class="fas fa-notes-medical mr-1"></i>
                                    <?php echo htmlspecialchars(substr($request['reason'], 0, 60)) . (strlen($request['reason']) > 60 ? '...' : ''); ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    <i class="far fa-clock mr-1"></i>
                                    Needed by: <?php echo formatDateTime($request['needed_by']); ?>
                                </p>
                                <p class="text-xs text-gray-400">
                                    Posted <?php echo timeAgo($request['created_at']); ?>
                                </p>
                            </div>
                            <div class="mt-3 flex space-x-2">
                                <a href="blood_requests.php?id=<?php echo $request['request_id']; ?>" 
                                   class="flex-1 text-center px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition">
                                    <i class="fas fa-hand-holding-heart mr-1"></i>View Details
                                </a>
                                <a href="tel:<?php echo $request['contact_number']; ?>" 
                                   class="px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-medium rounded hover:bg-gray-200 transition">
                                    <i class="fas fa-phone"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($requests)): ?>
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
                    <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($donor['full_name']); ?></p>
                    <p class="text-xs text-gray-500">Blood Type: <?php echo $donor['blood_type']; ?></p>
                </div>
                <div class="h-10 w-10 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold">
                    <?php echo strtoupper(substr($donor['full_name'], 0, 1)); ?>
                </div>
            </div>
        </div>
    </div>
</header>
