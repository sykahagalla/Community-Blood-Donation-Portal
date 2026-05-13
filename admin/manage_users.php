<?php
require_once '../config/config.php';
requireUserType('admin');

$db = getDB();

// Handle view specific user
$viewUserId = isset($_GET['view']) ? intval($_GET['view']) : null;

// Filter by status
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$statusOptions = ['all', 'pending', 'active', 'suspended'];
if (!in_array($statusFilter, $statusOptions)) {
    $statusFilter = 'all';
}

// Build query based on filter
if ($statusFilter === 'all') {
    $query = "
        SELECT u.*, 
               COALESCE(d.full_name, h.hospital_name) as name,
               COALESCE(d.phone, h.phone) as phone,
               COALESCE(d.city, h.city) as city,
               d.blood_type,
               d.address as donor_address,
               h.hospital_name,
               h.registration_number,
               h.address as hospital_address,
               h.contact_person,
               h.contact_email
        FROM users u
        LEFT JOIN donors d ON u.user_id = d.user_id
        LEFT JOIN hospitals h ON u.user_id = h.user_id
        WHERE u.user_type != 'admin'
        ORDER BY u.created_at DESC
    ";
} else {
    $query = "
        SELECT u.*, 
               COALESCE(d.full_name, h.hospital_name) as name,
               COALESCE(d.phone, h.phone) as phone,
               COALESCE(d.city, h.city) as city,
               d.blood_type,
               d.address as donor_address,
               h.hospital_name,
               h.registration_number,
               h.address as hospital_address,
               h.contact_person,
               h.contact_email
        FROM users u
        LEFT JOIN donors d ON u.user_id = d.user_id
        LEFT JOIN hospitals h ON u.user_id = h.user_id
        WHERE u.status = ? AND u.user_type != 'admin'
        ORDER BY u.created_at DESC
    ";
}

if ($statusFilter === 'all') {
    $usersStmt = $db->query($query);
} else {
    $usersStmt = $db->prepare($query);
    $usersStmt->execute([$statusFilter]);
}
$users = $usersStmt->fetchAll();

// If viewing a specific user
$viewUser = null;
if ($viewUserId) {
    $viewStmt = $db->prepare("
        SELECT u.*, 
               d.full_name, d.blood_type, d.date_of_birth, d.gender, d.address as donor_address, d.city as donor_city, d.phone as donor_phone,
               h.hospital_name, h.registration_number, h.address as hospital_address, h.city as hospital_city, h.phone as hospital_phone, h.contact_person, h.contact_email
        FROM users u
        LEFT JOIN donors d ON u.user_id = d.user_id
        LEFT JOIN hospitals h ON u.user_id = h.user_id
        WHERE u.user_id = ?
    ");
    $viewStmt->execute([$viewUserId]);
    $viewUser = $viewStmt->fetch();
}

$pageTitle = 'Manage Users';
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
    <div class="flex h-screen overflow-hidden">
        <?php include 'includes/sidebar.php'; ?>
        <div class="flex-1 overflow-x-hidden overflow-y-auto">
            <?php include 'includes/header.php'; ?>
            <main class="p-6">
                <!-- Filter Tabs -->
                <div class="mb-6 bg-white rounded-lg shadow-sm p-4">
                    <div class="flex flex-wrap gap-2">
                        <a href="?status=all" class="px-4 py-2 rounded-lg font-medium transition <?php echo $statusFilter === 'all' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                            All Users
                        </a>
                        <a href="?status=pending" class="px-4 py-2 rounded-lg font-medium transition <?php echo $statusFilter === 'pending' ? 'bg-yellow-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                            <i class="fas fa-clock mr-1"></i>Pending (<?php echo $db->query("SELECT COUNT(*) FROM users WHERE status = 'pending'")->fetchColumn(); ?>)
                        </a>
                        <a href="?status=active" class="px-4 py-2 rounded-lg font-medium transition <?php echo $statusFilter === 'active' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                            <i class="fas fa-check-circle mr-1"></i>Active
                        </a>
                        <a href="?status=suspended" class="px-4 py-2 rounded-lg font-medium transition <?php echo $statusFilter === 'suspended' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                            <i class="fas fa-ban mr-1"></i>Suspended
                        </a>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="bg-white rounded-lg shadow-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-users text-purple-600 mr-2"></i>
                            <?php 
                            if ($statusFilter === 'all') {
                                echo 'All Users';
                            } else {
                                echo ucfirst($statusFilter) . ' Users';
                            }
                            ?>
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">City</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registered</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fas fa-inbox text-4xl mb-2"></i>
                                        <p>No users found</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full <?php echo $user['user_type'] === 'donor' ? 'bg-purple-100' : 'bg-red-100'; ?> flex items-center justify-center">
                                                <i class="fas <?php echo $user['user_type'] === 'donor' ? 'fa-user text-purple-600' : 'fa-hospital text-red-600'; ?>"></i>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['name'] ?? 'N/A'); ?></div>
                                                <?php if ($user['user_type'] === 'donor' && $user['blood_type']): ?>
                                                <div class="text-xs text-gray-500">Blood: <?php echo $user['blood_type']; ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($user['email']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $user['user_type'] === 'donor' ? 'bg-purple-100 text-purple-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo ucfirst($user['user_type']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($user['city'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'active' => 'bg-green-100 text-green-800',
                                            'suspended' => 'bg-red-100 text-red-800'
                                        ];
                                        ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $statusColors[$user['status']]; ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo timeAgo($user['created_at']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="?view=<?php echo $user['user_id']; ?>&status=<?php echo $statusFilter; ?>" class="text-purple-600 hover:text-purple-900 mr-3">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <?php if ($user['status'] === 'pending'): ?>
                                        <button onclick="approveUser(<?php echo $user['user_id']; ?>)" class="text-green-600 hover:text-green-900 mr-3">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button onclick="rejectUser(<?php echo $user['user_id']; ?>)" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                        <?php elseif ($user['status'] === 'active'): ?>
                                        <button onclick="suspendUser(<?php echo $user['user_id']; ?>)" class="text-orange-600 hover:text-orange-900">
                                            <i class="fas fa-ban"></i> Suspend
                                        </button>
                                        <?php elseif ($user['status'] === 'suspended'): ?>
                                        <button onclick="activateUser(<?php echo $user['user_id']; ?>)" class="text-green-600 hover:text-green-900">
                                            <i class="fas fa-check"></i> Activate
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- User Detail Modal -->
    <?php if ($viewUser): ?>
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" x-data="{ open: true }" x-show="open">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" @click.away="window.location.href='?status=<?php echo $statusFilter; ?>'">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center sticky top-0 bg-white">
                <h3 class="text-xl font-bold text-gray-800">
                    <i class="fas <?php echo $viewUser['user_type'] === 'donor' ? 'fa-user' : 'fa-hospital'; ?> mr-2"></i>
                    User Details
                </h3>
                <a href="?status=<?php echo $statusFilter; ?>" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </a>
            </div>
            
            <div class="p-6">
                <!-- Status Badge -->
                <div class="mb-6 flex items-center justify-between">
                    <?php
                    $statusColors = [
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'active' => 'bg-green-100 text-green-800',
                        'suspended' => 'bg-red-100 text-red-800'
                    ];
                    ?>
                    <span class="px-4 py-2 text-sm font-semibold rounded-full <?php echo $statusColors[$viewUser['status']]; ?>">
                        Status: <?php echo ucfirst($viewUser['status']); ?>
                    </span>
                    <span class="text-sm text-gray-500">
                        <i class="far fa-calendar mr-1"></i>Registered: <?php echo formatDate($viewUser['created_at']); ?>
                    </span>
                </div>

                <?php if ($viewUser['user_type'] === 'donor'): ?>
                <!-- Donor Details -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Full Name</label>
                        <p class="text-gray-900 font-semibold"><?php echo htmlspecialchars($viewUser['full_name']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($viewUser['email']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Blood Type</label>
                        <p class="text-gray-900"><span class="px-3 py-1 bg-red-100 text-red-800 rounded-full font-bold"><?php echo $viewUser['blood_type']; ?></span></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Date of Birth</label>
                        <p class="text-gray-900"><?php echo formatDate($viewUser['date_of_birth']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Gender</label>
                        <p class="text-gray-900"><?php echo ucfirst($viewUser['gender']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Phone</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($viewUser['donor_phone']); ?></p>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Address</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($viewUser['donor_address']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">City</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($viewUser['donor_city']); ?></p>
                    </div>
                </div>
                <?php else: ?>
                <!-- Hospital Details -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Hospital Name</label>
                        <p class="text-gray-900 font-semibold"><?php echo htmlspecialchars($viewUser['hospital_name']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Registration Number</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($viewUser['registration_number']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($viewUser['email']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Phone</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($viewUser['hospital_phone']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Contact Person</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($viewUser['contact_person']); ?></p>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Contact Email</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($viewUser['contact_email']); ?></p>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Address</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($viewUser['hospital_address']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">City</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($viewUser['hospital_city']); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Actions -->
                <?php if ($viewUser['status'] === 'pending'): ?>
                <div class="flex gap-3 mt-6 pt-6 border-t border-gray-200">
                    <button onclick="approveUser(<?php echo $viewUser['user_id']; ?>)" class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-green-700 transition">
                        <i class="fas fa-check-circle mr-2"></i>Approve User
                    </button>
                    <button onclick="rejectUser(<?php echo $viewUser['user_id']; ?>)" class="flex-1 bg-red-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-red-700 transition">
                        <i class="fas fa-times-circle mr-2"></i>Reject User
                    </button>
                </div>
                <?php elseif ($viewUser['status'] === 'active'): ?>
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <button onclick="suspendUser(<?php echo $viewUser['user_id']; ?>)" class="w-full bg-orange-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-orange-700 transition">
                        <i class="fas fa-ban mr-2"></i>Suspend User
                    </button>
                </div>
                <?php elseif ($viewUser['status'] === 'suspended'): ?>
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <button onclick="activateUser(<?php echo $viewUser['user_id']; ?>)" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-green-700 transition">
                        <i class="fas fa-check-circle mr-2"></i>Activate User
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
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
                        alert('User approved successfully!');
                        location.reload();
                    } else {
                        alert(data.message || 'Error approving user');
                    }
                })
                .catch(error => {
                    alert('Error: ' + error.message);
                });
            }
        }

        function rejectUser(userId) {
            if (confirm('Are you sure you want to reject and delete this user? This action cannot be undone.')) {
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
                        alert('User rejected successfully!');
                        location.href = '?status=<?php echo $statusFilter; ?>';
                    } else {
                        alert(data.message || 'Error rejecting user');
                    }
                })
                .catch(error => {
                    alert('Error: ' + error.message);
                });
            }
        }

        function suspendUser(userId) {
            if (confirm('Are you sure you want to suspend this user?')) {
                fetch('actions/approve_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'user_id=' + userId + '&action=suspend'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('User suspended successfully!');
                        location.reload();
                    } else {
                        alert(data.message || 'Error suspending user');
                    }
                })
                .catch(error => {
                    alert('Error: ' + error.message);
                });
            }
        }

        function activateUser(userId) {
            if (confirm('Are you sure you want to activate this user?')) {
                fetch('actions/approve_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'user_id=' + userId + '&action=activate'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('User activated successfully!');
                        location.reload();
                    } else {
                        alert(data.message || 'Error activating user');
                    }
                })
                .catch(error => {
                    alert('Error: ' + error.message);
                });
            }
        }
    </script>
</body>
</html>
