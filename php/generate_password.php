<?php
// Generate new password hash
$new_password = 'Admin@1234';
$hash = password_hash($new_password, PASSWORD_BCRYPT);

echo "New Password: $new_password<br>";
echo "Hash to use: $hash<br><br>";

// Update admin
require_once 'db_connect.php';
$query = "UPDATE admin SET password = ? WHERE username = 'admin'";
if (executeUpdate($query, "s", array($hash))) {
    echo "<br>✅ Password updated successfully!<br>";
    echo "Login with:<br>";
    echo "Username: admin<br>";
    echo "Password: Admin@123";
} else {
    echo "❌ Failed to update password";
}
?>
// visit this site ->> 