<?php
require_once '../../config/config.php';
requireUserType('hospital');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$requestId = intval($_POST['request_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$requestId || !in_array($action, ['fulfill', 'cancel'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    $db = getDB();
    
    // Get hospital ID
    $hospitalStmt = $db->prepare("SELECT hospital_id FROM hospitals WHERE user_id = ?");
    $hospitalStmt->execute([$_SESSION['user_id']]);
    $hospitalId = $hospitalStmt->fetchColumn();
    
    // Verify the request belongs to this hospital
    $checkStmt = $db->prepare("SELECT * FROM blood_requests WHERE request_id = ? AND hospital_id = ?");
    $checkStmt->execute([$requestId, $hospitalId]);
    $request = $checkStmt->fetch();
    
    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'Request not found or access denied']);
        exit;
    }
    
    if ($action === 'fulfill') {
        $stmt = $db->prepare("UPDATE blood_requests SET status = 'fulfilled', fulfilled_at = NOW() WHERE request_id = ?");
        $stmt->execute([$requestId]);
        echo json_encode(['success' => true, 'message' => 'Request marked as fulfilled']);
    } else {
        $stmt = $db->prepare("UPDATE blood_requests SET status = 'cancelled' WHERE request_id = ?");
        $stmt->execute([$requestId]);
        echo json_encode(['success' => true, 'message' => 'Request cancelled']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
