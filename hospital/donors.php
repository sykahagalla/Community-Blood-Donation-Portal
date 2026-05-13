<?php
require_once '../config/config.php';
requireUserType('hospital');

$db = getDB();

// Search parameters
$searchBloodType = $_GET['blood_type'] ?? '';
$searchCity = $_GET['city'] ?? '';

// Build query
$query = "
    SELECT d.*, u.email, u.status
    FROM donors d
    JOIN users u ON d.user_id = u.user_id
    WHERE u.status = 'active'
";

$params = [];

if ($searchBloodType) {
    $query .= " AND d.blood_type = ?";
    $params[] = $searchBloodType;
}

if ($searchCity) {
    $query .= " AND d.city LIKE ?";
    $params[] = "%$searchCity%";
}

$query .= " ORDER BY d.donor_id DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$donors = $stmt->fetchAll();

// Get all unique cities for filter
$citiesStmt = $db->query("SELECT DISTINCT city FROM donors WHERE city IS NOT NULL ORDER BY city");
$cities = $citiesStmt->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Find Donors';
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
                <div class="bg-white rounded-lg shadow-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">
                            <i class="fas fa-search text-blue-600 mr-2"></i>Search Donors
                        </h3>
                        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Blood Type</label>
                                <select name="blood_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">All Blood Types</option>
                                    <?php
                                    $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                                    foreach ($bloodTypes as $type):
                                    ?>
                                    <option value="<?php echo $type; ?>" <?php echo $searchBloodType === $type ? 'selected' : ''; ?>>
                                        <?php echo $type; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                                <input type="text" name="city" value="<?php echo htmlspecialchars($searchCity); ?>" 
                                    placeholder="Enter city name"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <div class="flex items-end">
                                <button type="submit" class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                                    <i class="fas fa-search mr-2"></i>Search
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-users text-purple-600 mr-2"></i>Available Donors
                            <span class="text-sm text-gray-500 font-normal ml-2">(<?php echo count($donors); ?> found)</span>
                        </h3>
                    </div>
                    
                    <?php if (empty($donors)): ?>
                    <div class="p-12 text-center">
                        <i class="fas fa-user-slash text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-bold text-gray-600 mb-2">No Donors Found</h3>
                        <p class="text-gray-500">Try adjusting your search criteria.</p>
                    </div>
                    <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Blood Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">City</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gender</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Age</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Donations</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($donors as $donor): 
                                    $age = date_diff(date_create($donor['date_of_birth']), date_create('today'))->y;
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($donor['full_name']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 text-sm font-bold bg-red-100 text-red-800 rounded-full">
                                            <?php echo $donor['blood_type']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($donor['phone']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($donor['city']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo ucfirst($donor['gender']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $age; ?> years</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $donor['total_donations']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="tel:<?php echo $donor['phone']; ?>" class="text-green-600 hover:text-green-900">
                                            <i class="fas fa-phone mr-1"></i>Call
                                        </a>
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
