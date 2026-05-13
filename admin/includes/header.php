<header class="bg-white shadow-sm">
    <div class="flex items-center justify-between px-6 py-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800"><?php echo $pageTitle ?? 'Dashboard'; ?></h2>
            <p class="text-sm text-gray-600">Welcome back, Administrator</p>
        </div>
        
        <div class="flex items-center space-x-4">
            <!-- Notifications -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition">
                    <i class="fas fa-bell text-xl"></i>
                    <?php
                    $notifCount = $db->query("SELECT COUNT(*) FROM users WHERE status = 'pending'")->fetchColumn();
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
                        <p class="text-xs text-gray-500 mt-1"><?php echo $notifCount; ?> pending approval(s)</p>
                    </div>
                    
                    <div class="max-h-96 overflow-y-auto">
                        <?php
                        $pendingUsers = $db->query("
                            SELECT u.user_id, u.email, u.user_type, u.created_at,
                                   d.full_name as donor_name, d.blood_type,
                                   h.hospital_name, h.contact_person
                            FROM users u
                            LEFT JOIN donors d ON u.user_id = d.user_id
                            LEFT JOIN hospitals h ON u.user_id = h.user_id
                            WHERE u.status = 'pending'
                            ORDER BY u.created_at DESC
                            LIMIT 10
                        ")->fetchAll();
                        
                        if (empty($pendingUsers)):
                        ?>
                        <div class="p-6 text-center text-gray-500">
                            <i class="fas fa-check-circle text-4xl mb-2 text-green-500"></i>
                            <p>No pending approvals</p>
                        </div>
                        <?php else: ?>
                        <?php foreach ($pendingUsers as $user): ?>
                        <div class="p-4 hover:bg-gray-50 border-b border-gray-100 transition">
                            <div class="flex items-start justify-between">
                                <div class="flex items-start space-x-3 flex-1">
                                    <div class="flex-shrink-0">
                                        <?php if ($user['user_type'] === 'donor'): ?>
                                        <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                                            <i class="fas fa-user text-purple-600"></i>
                                        </div>
                                        <?php else: ?>
                                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                                            <i class="fas fa-hospital text-red-600"></i>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-800">
                                            <?php 
                                            if ($user['user_type'] === 'donor') {
                                                echo htmlspecialchars($user['donor_name'] ?? 'New Donor');
                                            } else {
                                                echo htmlspecialchars($user['hospital_name'] ?? 'New Hospital');
                                            }
                                            ?>
                                        </p>
                                        <p class="text-xs text-gray-500 truncate"><?php echo htmlspecialchars($user['email']); ?></p>
                                        <div class="flex items-center mt-1 space-x-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?php echo $user['user_type'] === 'donor' ? 'bg-purple-100 text-purple-800' : 'bg-red-100 text-red-800'; ?>">
                                                <?php echo ucfirst($user['user_type']); ?>
                                            </span>
                                            <?php if ($user['user_type'] === 'donor' && $user['blood_type']): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                <?php echo $user['blood_type']; ?>
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-xs text-gray-400 mt-1">
                                            <i class="far fa-clock mr-1"></i><?php echo timeAgo($user['created_at']); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 flex space-x-2">
                                <a href="manage_users.php?view=<?php echo $user['user_id']; ?>" 
                                   class="flex-1 text-center px-3 py-1.5 bg-purple-600 text-white text-xs font-medium rounded hover:bg-purple-700 transition">
                                    Review
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($pendingUsers)): ?>
                    <div class="p-3 border-t border-gray-200 bg-gray-50">
                        <a href="manage_users.php" class="block text-center text-sm font-medium text-purple-600 hover:text-purple-700">
                            View All Pending Users →
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Profile -->
            <div class="flex items-center">
                <div class="mr-3 text-right">
                    <p class="text-sm font-semibold text-gray-800"><?php echo $_SESSION['email']; ?></p>
                    <p class="text-xs text-gray-500">Administrator</p>
                </div>
                <div class="h-10 w-10 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold">
                    <i class="fas fa-user-shield"></i>
                </div>
            </div>
        </div>
    </div>
</header>
