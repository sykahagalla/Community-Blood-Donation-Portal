<?php
// Password Hash Generator
// Use this to generate password hashes for demo accounts

$passwords = [
    'admin123' => password_hash('admin123', PASSWORD_DEFAULT),
    'donors123' => password_hash('donors123', PASSWORD_DEFAULT),
    'hospitals123' => password_hash('hospitals123', PASSWORD_DEFAULT),
];

echo "<h2>Password Hashes Generated:</h2>";
echo "<pre>";
foreach ($passwords as $password => $hash) {
    echo "Password: $password\n";
    echo "Hash: $hash\n\n";
}
echo "</pre>";

// Test verification
echo "<h2>Verification Test:</h2>";
echo "<pre>";
foreach ($passwords as $password => $hash) {
    $verify = password_verify($password, $hash);
    echo "$password: " . ($verify ? "✓ VALID" : "✗ INVALID") . "\n";
}
echo "</pre>";
