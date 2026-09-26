<?php
require_once 'config.php';

$fullname = "shariqafay";
$email = "shariq@marketlink.com"; // Aap apni marzi ki email bhi rakh sakte hain
$plain_password = "vivo@369";
$role = "admin";

// Password ko securely hash karna
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

try {
    // Check karein ke email pehle se mojood toh nahi
    $check = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $check->execute([$email]);
    
    if ($check->rowCount() > 0) {
        echo "Is email ke sath user pehle se mojood hai!";
    } else {
        // Database mein insert karna
        $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$fullname, $email, $hashed_password, $role]);
        echo "Admin Shariq successfully database mein save ho gaya! Ab aap login kar sakte hain.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>