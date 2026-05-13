<?php
/**
 * Demo Accounts Setup Script
 * Run this file ONCE after database import to set up demo accounts with correct password hashes
 */

require_once 'config/database.php';

try {
    $db = getDB();
    
    echo "<h2>Setting up Demo Accounts...</h2>";
    
    // Generate fresh password hashes
    $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
    $donorHash = password_hash('donors123', PASSWORD_DEFAULT);
    $hospitalHash = password_hash('hospitals123', PASSWORD_DEFAULT);
    
    // Delete existing demo accounts if they exist
    $db->exec("DELETE FROM users WHERE email IN ('admin@bloodportal.com', 'donors@bloodportal.com', 'hospitals@bloodportal.com')");
    echo "<p>✓ Cleared existing demo accounts</p>";
    
    // Insert admin user
    $stmt = $db->prepare("INSERT INTO users (email, password_hash, user_type, status) VALUES (?, ?, 'admin', 'active')");
    $stmt->execute(['admin@bloodportal.com', $adminHash]);
    echo "<p>✓ Created admin account: admin@bloodportal.com / admin123</p>";
    
    // Insert donor user
    $stmt = $db->prepare("INSERT INTO users (email, password_hash, user_type, status) VALUES (?, ?, 'donor', 'active')");
    $stmt->execute(['donors@bloodportal.com', $donorHash]);
    $donorUserId = $db->lastInsertId();
    echo "<p>✓ Created donor account: donors@bloodportal.com / donors123</p>";
    
    // Insert donor profile
    $stmt = $db->prepare("
        INSERT INTO donors (user_id, full_name, blood_type, date_of_birth, gender, phone, address, city, is_available, total_donations, last_donation_date) 
        VALUES (?, 'John Doe (Demo Donor)', 'O+', '1990-01-15', 'male', '+94 77 123 4567', '123 Main Street, Colombo', 'Colombo', 1, 5, '2025-12-15')
    ");
    $stmt->execute([$donorUserId]);
    echo "<p>✓ Created donor profile</p>";
    
    // Insert hospital user
    $stmt = $db->prepare("INSERT INTO users (email, password_hash, user_type, status) VALUES (?, ?, 'hospital', 'active')");
    $stmt->execute(['hospitals@bloodportal.com', $hospitalHash]);
    $hospitalUserId = $db->lastInsertId();
    echo "<p>✓ Created hospital account: hospitals@bloodportal.com / hospitals123</p>";
    
    // Insert hospital profile
    $stmt = $db->prepare("
        INSERT INTO hospitals (user_id, hospital_name, registration_number, phone, address, city, contact_person, contact_email) 
        VALUES (?, 'General Hospital Colombo (Demo)', 'REG-2024-001', '+94 11 269 1111', '456 Hospital Road, Colombo 08', 'Colombo', 'Dr. Perera', 'contact@generalhospital.lk')
    ");
    $stmt->execute([$hospitalUserId]);
    echo "<p>✓ Created hospital profile</p>";
    
    // Create sample blood request
    $requestStmt = $db->prepare("
        INSERT INTO blood_requests (hospital_id, blood_type, units_needed, urgency_level, patient_name, reason, contact_number, needed_by, status) 
        VALUES (?, 'O+', 3, 'urgent', 'Sample Patient', 'Emergency surgery required', '+94 11 269 1111', DATE_ADD(NOW(), INTERVAL 2 DAY), 'active')
    ");
    $requestStmt->execute([$db->query("SELECT hospital_id FROM hospitals WHERE user_id = $hospitalUserId")->fetchColumn()]);
    echo "<p>✓ Created sample blood request</p>";
    
    // Create sample donation history for donor
    $donorId = $db->query("SELECT donor_id FROM donors WHERE user_id = $donorUserId")->fetchColumn();
    $hospitalId = $db->query("SELECT hospital_id FROM hospitals WHERE user_id = $hospitalUserId")->fetchColumn();
    
    $donationStmt = $db->prepare("
        INSERT INTO donations (donor_id, hospital_id, donation_date, units_donated, donation_type, status) 
        VALUES (?, ?, '2025-12-15', 1, 'whole_blood', 'completed')
    ");
    $donationStmt->execute([$donorId, $hospitalId]);
    echo "<p>✓ Created sample donation record</p>";
    
    echo "<hr>";
    echo "<h3 style='color: green;'>✓ Demo Accounts Setup Complete!</h3>";
    echo "<p><strong>You can now login with:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> admin@bloodportal.com / admin123</li>";
    echo "<li><strong>Donor:</strong> donors@bloodportal.com / donors123</li>";
    echo "<li><strong>Hospital:</strong> hospitals@bloodportal.com / hospitals123</li>";
    echo "</ul>";
    echo "<p><a href='index.php' style='color: blue; font-weight: bold;'>Go to Homepage</a></p>";
    echo "<p><a href='auth/login.php' style='color: blue; font-weight: bold;'>Go to Login</a></p>";
    
    echo "<hr>";
    echo "<p style='color: red; font-weight: bold;'>IMPORTANT: Delete this file (setup_demo_accounts.php) after setup for security!</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
