<?php
// admin/edit_account.php
// Handles editing of the currently logged-in admin account (username/password)

require_once '../config/connection.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    // Not logged in – redirect to login
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_self') {
    $adminId   = (int)$_SESSION['admin_id'];
    $newUsername = trim($_POST['username'] ?? '');
    $newPassword = $_POST['password'] ?? '';

    // Validate username
    if ($newUsername === '') {
        header('Location: ../admin/respondents.php?error=empty_username');
        exit;
    }

    // Prepare base query
    if ($newPassword !== '') {
        // Update both username and password (hash the password)
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE admin SET username = ?, password = ? WHERE id = ?');
        $stmt->execute([$newUsername, $hash, $adminId]);
    } else {
        // Update only username
        $stmt = $pdo->prepare('UPDATE admin SET username = ? WHERE id = ?');
        $stmt->execute([$newUsername, $adminId]);
    }

    // Refresh session data
    $_SESSION['username'] = $newUsername;
    // Redirect back with success flag
    header('Location: ../admin/respondents.php?success=account_updated');
    exit;
}

// If accessed directly via GET, just redirect back
header('Location: ../admin/respondents.php');
exit;
?>
