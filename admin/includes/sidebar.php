<aside class="bg-gray-800 text-white w-64 min-h-screen flex flex-col">
    <div class="p-6 border-b border-gray-700">
        <div class="flex items-center">
            <i class="fas fa-heartbeat text-3xl text-red-600 mr-3"></i>
            <div>
                <h1 class="text-xl font-bold">BloodLife</h1>
                <p class="text-xs text-gray-400">Admin Panel</p>
            </div>
        </div>
    </div>
    
    <nav class="flex-1 px-4 py-6">
        <ul class="space-y-2">
            <li>
                <a href="dashboard.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-700 transition <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'bg-gray-700' : ''; ?>">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="donors.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-700 transition <?php echo basename($_SERVER['PHP_SELF']) === 'donors.php' ? 'bg-gray-700' : ''; ?>">
                    <i class="fas fa-users mr-3"></i>
                    Donors
                </a>
            </li>
            <li>
                <a href="hospitals.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-700 transition <?php echo basename($_SERVER['PHP_SELF']) === 'hospitals.php' ? 'bg-gray-700' : ''; ?>">
                    <i class="fas fa-hospital mr-3"></i>
                    Hospitals
                </a>
            </li>
            <li>
                <a href="blood_requests.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-700 transition <?php echo basename($_SERVER['PHP_SELF']) === 'blood_requests.php' ? 'bg-gray-700' : ''; ?>">
                    <i class="fas fa-hand-holding-medical mr-3"></i>
                    Blood Requests
                </a>
            </li>
            <li>
                <a href="donations.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-700 transition <?php echo basename($_SERVER['PHP_SELF']) === 'donations.php' ? 'bg-gray-700' : ''; ?>">
                    <i class="fas fa-tint mr-3"></i>
                    Donations
                </a>
            </li>
            <li>
                <a href="blood_drives.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-700 transition <?php echo basename($_SERVER['PHP_SELF']) === 'blood_drives.php' ? 'bg-gray-700' : ''; ?>">
                    <i class="fas fa-calendar-alt mr-3"></i>
                    Blood Drives
                </a>
            </li>
            <li>
                <a href="reports.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-700 transition <?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'bg-gray-700' : ''; ?>">
                    <i class="fas fa-chart-bar mr-3"></i>
                    Reports
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="p-4 border-t border-gray-700">
        <a href="../auth/logout.php" class="flex items-center px-4 py-3 rounded-lg hover:bg-red-600 transition text-red-400 hover:text-white">
            <i class="fas fa-sign-out-alt mr-3"></i>
            Logout
        </a>
    </div>
</aside>
