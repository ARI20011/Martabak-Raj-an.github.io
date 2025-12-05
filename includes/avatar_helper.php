<?php
/**
 * Helper function to get user avatar
 * Returns uploaded photo if exists, otherwise returns default avatar
 */
function getUserAvatar($userEmail = null) {
    $dataDir = __DIR__ . '/../data';
    $usersFile = $dataDir . '/users.json';
    $defaultAvatar = 'img/profile 1.png';
    
    // If no email provided, check session (only if session is started)
    if ($userEmail === null && session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user'])) {
        // Priority 1: Check session for uploaded avatar
        if (!empty($_SESSION['user']['avatar_path']) && file_exists(__DIR__ . '/../' . $_SESSION['user']['avatar_path'])) {
            return $_SESSION['user']['avatar_path'];
        }
        // Priority 2: Check session for selected avatar index
        if (isset($_SESSION['user']['selected_avatar_index']) && $_SESSION['user']['selected_avatar_index'] > 0) {
            return $defaultAvatar;
        }
        $userEmail = $_SESSION['user']['email'] ?? null;
    }
    
    if (!$userEmail) {
        return $defaultAvatar;
    }
    
    // Load users data
    if (!file_exists($usersFile)) {
        return $defaultAvatar;
    }
    
    $users = json_decode(file_get_contents($usersFile), true) ?: [];
    
    // Find user by email
    foreach ($users as $user) {
        if (strtolower($user['email']) === strtolower($userEmail)) {
            // Check if user has uploaded photo
            if (!empty($user['avatar_path']) && file_exists(__DIR__ . '/../' . $user['avatar_path'])) {
                // Update session if active
                if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user']) && strtolower($_SESSION['user']['email']) === strtolower($userEmail)) {
                    $_SESSION['user']['avatar_path'] = $user['avatar_path'];
                }
                return $user['avatar_path'];
            }
            // Check if user has selected basic avatar
            if (isset($user['selected_avatar_index']) && $user['selected_avatar_index'] > 0) {
                // Update session if active
                if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user']) && strtolower($_SESSION['user']['email']) === strtolower($userEmail)) {
                    $_SESSION['user']['selected_avatar_index'] = $user['selected_avatar_index'];
                    if (isset($_SESSION['user']['avatar_path'])) {
                        unset($_SESSION['user']['avatar_path']);
                    }
                }
                // Return default avatar (all basic avatars use same image for now)
                return $defaultAvatar;
            }
            break;
        }
    }
    
    return $defaultAvatar;
}

/**
 * Save uploaded avatar and update user data
 */
function saveUserAvatar($userEmail, $uploadedFile) {
    $dataDir = __DIR__ . '/../data';
    $profilesDir = __DIR__ . '/../img/profiles';
    $usersFile = $dataDir . '/users.json';
    
    // Create profiles directory if not exists
    if (!is_dir($profilesDir)) {
        mkdir($profilesDir, 0777, true);
    }
    
    // Validate file
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($uploadedFile['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Format file tidak didukung. Gunakan JPG, PNG, atau GIF.'];
    }
    
    if ($uploadedFile['size'] > $maxSize) {
        return ['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 5MB.'];
    }
    
    // Generate unique filename
    $extension = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
    $filename = md5($userEmail . time()) . '.' . $extension;
    $targetPath = $profilesDir . '/' . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($uploadedFile['tmp_name'], $targetPath)) {
        // Update users.json
        $users = json_decode(file_get_contents($usersFile), true) ?: [];
        
        foreach ($users as &$user) {
            if (strtolower($user['email']) === strtolower($userEmail)) {
                // Delete old avatar if exists
                if (!empty($user['avatar_path']) && file_exists(__DIR__ . '/../' . $user['avatar_path'])) {
                    @unlink(__DIR__ . '/../' . $user['avatar_path']);
                }
                
                // Save new avatar path
                $user['avatar_path'] = 'img/profiles/' . $filename;
                break;
            }
        }
        
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
        
        // Update session if current user (only if session is started)
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user']) && strtolower($_SESSION['user']['email']) === strtolower($userEmail)) {
            $_SESSION['user']['avatar_path'] = 'img/profiles/' . $filename;
        }
        
        return ['success' => true, 'path' => 'img/profiles/' . $filename];
    }
    
    return ['success' => false, 'message' => 'Gagal mengupload foto. Silakan coba lagi.'];
}

