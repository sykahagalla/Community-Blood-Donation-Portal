<?php
require_once '../../config/config.php';
requireUserType('admin');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$userId = intval($_POST['user_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$userId || !in_array($action, ['approve', 'reject', 'suspend', 'activate'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    $db = getDB();
    
    if ($action === 'approve') {
        $stmt = $db->prepare("UPDATE users SET status = 'active' WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Log the action
        $logStmt = $db->prepare("INSERT INTO admin_logs (admin_id, action, target_user_id, details) VALUES (?, 'User Approved', ?, ?)");
        $logStmt->execute([$_SESSION['user_id'], $userId, "User account activated"]);
        
        echo json_encode(['success' => true, 'message' => 'User approved successfully']);
    } elseif ($action === 'reject') {
        // Delete the user and related records
        $stmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Log the action
        $logStmt = $db->prepare("INSERT INTO admin_logs (admin_id, action, target_user_id, details) VALUES (?, 'User Rejected', ?, ?)");
        $logStmt->execute([$_SESSION['user_id'], $userId, "User account rejected and deleted"]);
        
        echo json_encode(['success' => true, 'message' => 'User rejected successfully']);
    } elseif ($action === 'suspend') {
        $stmt = $db->prepare("UPDATE users SET status = 'suspended' WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Log the action
        $logStmt = $db->prepare("INSERT INTO admin_logs (admin_id, action, target_user_id, details) VALUES (?, 'User Suspended', ?, ?)");
        $logStmt->execute([$_SESSION['user_id'], $userId, "User account suspended"]);
        
        echo json_encode(['success' => true, 'message' => 'User suspended successfully']);
    } elseif ($action === 'activate') {
        $stmt = $db->prepare("UPDATE users SET status = 'active' WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Log the action
        $logStmt = $db->prepare("INSERT INTO admin_logs (admin_id, action, target_user_id, details) VALUES (?, 'User Activated', ?, ?)");
        $logStmt->execute([$_SESSION['user_id'], $userId, "User account reactivated"]);
        
        echo json_encode(['success' => true, 'message' => 'User activated successfully']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
