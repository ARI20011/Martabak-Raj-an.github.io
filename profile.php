<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/includes/avatar_helper.php';

$user = $_SESSION['user'];
$dataDir = __DIR__ . '/data';
$bookingsFile = $dataDir . '/bookings.json';
$usersFile = $dataDir . '/users.json';

function h($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Get current avatar and selected avatar index
$users = json_decode(file_get_contents($usersFile), true) ?: [];
$selectedAvatarIndex = 1;
foreach ($users as $u) {
    if (strtolower($u['email']) === strtolower($user['email'])) {
        $selectedAvatarIndex = $u['selected_avatar_index'] ?? 1;
        break;
    }
}

$currentAvatar = getUserAvatar($user['email']);
$hasUploadedPhoto = ($currentAvatar !== 'img/profile 1.png' && isset($_SESSION['user']['avatar_path']));

// Add cache busting based on selected avatar index
$avatarTimestamp = isset($_SESSION['user']['selected_avatar_index']) ? $_SESSION['user']['selected_avatar_index'] : (isset($_SESSION['user']['avatar_path']) ? time() : 1);

// Load or create bookings data
if (!file_exists($bookingsFile)) {
    $defaultBookings = [
        [
            'id' => 1,
            'menu_name' => 'Martabak Maramelow',
            'image' => 'img/mars.jpeg',
            'status' => 'Reserved',
            'date' => '17 December 2025',
            'time' => '12:15 PM',
            'guests' => 2,
            'days_ago' => 2
        ],
        [
            'id' => 2,
            'menu_name' => 'Coconut Green Martabak',
            'image' => 'img/cg.jpg',
            'status' => 'Cancelled',
            'date' => '17 December 2025',
            'time' => '12:15 PM',
            'guests' => 2,
            'days_ago' => 2
        ],
        [
            'id' => 3,
            'menu_name' => 'Martabak Mesir',
            'image' => 'img/mesir.jpg',
            'status' => 'Completed',
            'date' => '17 December 2025',
            'time' => '12:15 PM',
            'guests' => 2,
            'days_ago' => 18
        ],
        [
            'id' => 4,
            'menu_name' => 'Martabak Kacang',
            'image' => 'img/kacang.jpg',
            'status' => 'Completed',
            'date' => '17 December 2025',
            'time' => '12:15 PM',
            'guests' => 2,
            'days_ago' => 18
        ]
    ];
    file_put_contents($bookingsFile, json_encode($defaultBookings, JSON_PRETTY_PRINT));
}

$bookings = json_decode(file_get_contents($bookingsFile), true) ?: [];

// Determine which tab is active (history or myslots)
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'history';
$currentUserEmail = isset($user['email']) ? strtolower($user['email']) : '';
$userBookings = [];
if ($currentUserEmail) {
    foreach ($bookings as $b) {
        if (!empty($b['user_email']) && strtolower($b['user_email']) === $currentUserEmail) {
            $userBookings[] = $b;
        }
    }
}

// Parse phone number
$phoneCode = '+62';
$phoneNumber = $user['phone'] ?? '';
// Try to extract country code if present
if (preg_match('/^(\+\d{1,3})[-.\s]?(.+)$/', $phoneNumber, $matches)) {
    $phoneCode = $matches[1];
    $phoneNumber = preg_replace('/[-.\s]/', '-', $matches[2]);
} else {
    // Remove any existing formatting
    $phoneNumber = preg_replace('/[-.\s]/', '-', $phoneNumber);
}

// Handle form submission
$updateSuccess = false;
$uploadMessage = '';
$uploadError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        // Update profile data
        $users = json_decode(file_get_contents($usersFile), true) ?: [];
        foreach ($users as &$u) {
            if (strtolower($u['email']) === strtolower($user['email'])) {
                $u['full_name'] = $_POST['full_name'] ?? $u['full_name'];
                $u['phone'] = ($_POST['phone_code'] ?? '+62') . '-' . ($_POST['phone'] ?? '');
                $_SESSION['user']['full_name'] = $u['full_name'];
                $_SESSION['user']['phone'] = $u['phone'];
                
                // Save selected avatar index if provided
                if (isset($_POST['selected_avatar']) && $_POST['selected_avatar'] !== 'upload' && is_numeric($_POST['selected_avatar'])) {
                    $avatarIndex = (int)$_POST['selected_avatar'];
                    $u['selected_avatar_index'] = $avatarIndex;
                    $_SESSION['user']['selected_avatar_index'] = $avatarIndex;
                    
                    // Remove uploaded photo if switching to basic avatar
                    if (!empty($u['avatar_path']) && file_exists(__DIR__ . '/' . $u['avatar_path'])) {
                        @unlink(__DIR__ . '/' . $u['avatar_path']);
                    }
                    unset($u['avatar_path']);
                    if (isset($_SESSION['user']['avatar_path'])) {
                        unset($_SESSION['user']['avatar_path']);
                    }
                    
                    $selectedAvatarIndex = $avatarIndex;
                    $currentAvatar = 'img/converted_image.png';
                    $hasUploadedPhoto = false;
                }
                
                break;
            }
        }
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
        $updateSuccess = true;
        
        // Handle avatar upload if file is uploaded (via AJAX)
        if (isset($_POST['action']) && $_POST['action'] === 'upload_avatar' && isset($_FILES['avatar_upload']) && $_FILES['avatar_upload']['error'] === UPLOAD_ERR_OK) {
            $result = saveUserAvatar($user['email'], $_FILES['avatar_upload']);
            if ($result['success']) {
                $currentAvatar = $result['path'];
                $hasUploadedPhoto = true;
                // Update session
                $_SESSION['user']['avatar_path'] = $result['path'];
                // Remove selected_avatar_index when uploading photo
                if (isset($_SESSION['user']['selected_avatar_index'])) {
                    unset($_SESSION['user']['selected_avatar_index']);
                }
                // Also update in users.json
                $users = json_decode(file_get_contents($usersFile), true) ?: [];
                foreach ($users as &$u) {
                    if (strtolower($u['email']) === strtolower($user['email'])) {
                        if (isset($u['selected_avatar_index'])) {
                            unset($u['selected_avatar_index']);
                        }
                        break;
                    }
                }
                file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'avatar' => $result['path']]);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $result['message']]);
                exit;
            }
        }
        
        // Handle avatar upload if file is uploaded (via form submit)
        if (isset($_FILES['avatar_upload']) && $_FILES['avatar_upload']['error'] === UPLOAD_ERR_OK && (!isset($_POST['action']) || $_POST['action'] !== 'upload_avatar')) {
            $result = saveUserAvatar($user['email'], $_FILES['avatar_upload']);
            if ($result['success']) {
                $uploadMessage = 'Foto profil berhasil diupload!';
                $currentAvatar = $result['path'];
                $hasUploadedPhoto = true;
                // Update session
                $_SESSION['user']['avatar_path'] = $result['path'];
                // Remove selected_avatar_index when uploading photo
                if (isset($_SESSION['user']['selected_avatar_index'])) {
                    unset($_SESSION['user']['selected_avatar_index']);
                }
                // Also update in users.json
                $users = json_decode(file_get_contents($usersFile), true) ?: [];
                foreach ($users as &$u) {
                    if (strtolower($u['email']) === strtolower($user['email'])) {
                        if (isset($u['selected_avatar_index'])) {
                            unset($u['selected_avatar_index']);
                        }
                        break;
                    }
                }
                file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
            } else {
                $uploadError = $result['message'];
            }
        }
        
        // Handle basic avatar selection
        if (isset($_POST['selected_avatar']) && $_POST['selected_avatar'] !== 'upload') {
            $avatarIndex = (int)$_POST['selected_avatar'];
            // Remove uploaded photo if switching to basic avatar
            if ($hasUploadedPhoto) {
                $users = json_decode(file_get_contents($usersFile), true) ?: [];
                foreach ($users as &$u) {
                    if (strtolower($u['email']) === strtolower($user['email'])) {
                        if (!empty($u['avatar_path']) && file_exists(__DIR__ . '/' . $u['avatar_path'])) {
                            @unlink(__DIR__ . '/' . $u['avatar_path']);
                        }
                        unset($u['avatar_path']);
                        break;
                    }
                }
                file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
            }
            
            // Save selected avatar index
            $users = json_decode(file_get_contents($usersFile), true) ?: [];
            foreach ($users as &$u) {
                if (strtolower($u['email']) === strtolower($user['email'])) {
                    $u['selected_avatar_index'] = $avatarIndex;
                    break;
                }
            }
            file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
            $selectedAvatarIndex = $avatarIndex;
            $currentAvatar = 'img/converted_image.png';
            $hasUploadedPhoto = false;
            if (isset($_SESSION['user']['avatar_path'])) {
                unset($_SESSION['user']['avatar_path']);
            }
            $_SESSION['user']['selected_avatar_index'] = $avatarIndex;
            $selectedAvatarIndex = $avatarIndex;
        }
    } elseif ($_POST['action'] === 'save_avatar_selection' && isset($_POST['avatar_index'])) {
        // Save avatar selection immediately via AJAX
        $avatarIndex = (int)$_POST['avatar_index'];
        $users = json_decode(file_get_contents($usersFile), true) ?: [];
        
        foreach ($users as &$u) {
            if (strtolower($u['email']) === strtolower($user['email'])) {
                // Remove uploaded photo if switching to basic avatar
                if (!empty($u['avatar_path']) && file_exists(__DIR__ . '/' . $u['avatar_path'])) {
                    @unlink(__DIR__ . '/' . $u['avatar_path']);
                }
                unset($u['avatar_path']);
                $u['selected_avatar_index'] = $avatarIndex;
                break;
            }
        }
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
        
        // Update session - CRITICAL for persistence across pages
        if (isset($_SESSION['user']['avatar_path'])) {
            unset($_SESSION['user']['avatar_path']);
        }
        $_SESSION['user']['selected_avatar_index'] = $avatarIndex;
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'avatar' => 'img/converted_image.png']);
        exit;
    } elseif ($_POST['action'] === 'cancel_booking' && isset($_POST['booking_id'])) {
        $bookingId = (int)$_POST['booking_id'];
        foreach ($bookings as &$booking) {
            if ($booking['id'] === $bookingId) {
                $booking['status'] = 'Cancelled';
                break;
            }
        }
        file_put_contents($bookingsFile, json_encode($bookings, JSON_PRETTY_PRINT));
        header('Location: profile.php');
        exit;
    } elseif ($_POST['action'] === 'delete_booking' && isset($_POST['booking_id'])) {
        $bookingId = (int)$_POST['booking_id'];
        $newBookings = [];
        foreach ($bookings as $bk) {
            if ((int)($bk['id'] ?? 0) === $bookingId) {
                // allow deletion if booking belongs to current user
                $canDelete = false;
                if (!empty($bk['user_email']) && !empty($currentUserEmail) && strtolower($bk['user_email']) === $currentUserEmail) {
                    $canDelete = true;
                }
                // fallback: match by customer name if user_email not present
                if (!$canDelete && !empty($bk['customer']) && !empty($_SESSION['user']['full_name']) && strtolower(trim($bk['customer'])) === strtolower(trim($_SESSION['user']['full_name']))) {
                    $canDelete = true;
                }
                if ($canDelete) {
                    // skip adding this booking -> delete it
                    continue;
                }
            }
            $newBookings[] = $bk;
        }
        file_put_contents($bookingsFile, json_encode($newBookings, JSON_PRETTY_PRINT));
        header('Location: profile.php?tab=myslots');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Martabak Rajan</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: #F9F9F9;
            margin: 0;
            min-height: 100vh;
        }
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .profile-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
        }
        .profile-header a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
        }
        .profile-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        .profile-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 25px;
            color: #333;
        }
        .avatar-selection {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        .avatar-option {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid #ddd;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f5f5;
            font-size: 1.5rem;
            overflow: hidden;
            position: relative;
        }
        .avatar-option img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .avatar-option.upload-option {
            background: linear-gradient(135deg, #FF8C00, #ffa64d);
            border-color: #FF8C00;
        }
        .avatar-option.upload-option i {
            color: white;
            font-size: 1.2rem;
        }
        .avatar-option:hover {
            border-color: #FF8C00;
            transform: scale(1.1);
        }
        .avatar-option.selected {
            border-color: #FF8C00;
            background: #fff5eb;
            box-shadow: 0 0 0 3px rgba(255, 140, 0, 0.2);
        }
        .avatar-option.upload-option.selected {
            background: linear-gradient(135deg, #FF8C00, #ffa64d);
            box-shadow: 0 0 0 3px rgba(255, 140, 0, 0.3);
        }
        .profile-mode-toggle {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 8px;
        }
        .mode-btn {
            flex: 1;
            padding: 10px;
            border: 2px solid transparent;
            border-radius: 6px;
            background: white;
            cursor: pointer;
            font-weight: 500;
            text-align: center;
            transition: all 0.3s;
        }
        .mode-btn.active {
            border-color: #FF8C00;
            background: #fff5eb;
            color: #FF8C00;
        }
        .upload-section {
            display: none;
        }
        .upload-section.active {
            display: block;
        }
        .avatar-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #FF8C00;
            margin: 0 auto 20px;
            display: block;
        }
        .file-input-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        .file-input-wrapper input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        .file-input-label {
            display: block;
            padding: 12px;
            background: #333;
            color: white;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            font-weight: 600;
        }
        .file-input-label:hover {
            background: #555;
        }
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .alert-success {
            background: #e6fff2;
            color: #0f8c3a;
            border: 1px solid #a4e0bd;
        }
        .alert-error {
            background: #ffecec;
            color: #b10f1b;
            border: 1px solid #f5a2a8;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 0.9rem;
            font-weight: 500;
            color: #555;
            margin-bottom: 8px;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: #FF8C00;
            box-shadow: 0 0 0 3px rgba(255,140,0,0.1);
        }
        .phone-input-group {
            display: flex;
            gap: 10px;
        }
        .phone-input-group select {
            width: 100px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
        }
        .phone-input-group input {
            flex: 1;
        }
        .otp-button {
            background: #333;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-bottom: 15px;
        }
        .otp-button:hover {
            background: #555;
        }
        .otp-inputs {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        .otp-input {
            width: 50px;
            height: 50px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1.2rem;
            font-weight: 600;
        }
        .otp-timer {
            font-size: 0.85rem;
            color: #666;
            text-align: center;
        }
        .profile-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-size: 0.95rem;
        }
        .btn-secondary {
            background: white;
            color: #333;
            border: 1px solid #ddd;
        }
        .btn-primary {
            background: #333;
            color: white;
        }
        .btn-primary:hover {
            background: #555;
        }
        .booking-card {
            display: flex;
            gap: 15px;
            padding: 18px;
            border: 1px solid #e5e5e5;
            border-radius: 10px;
            margin-bottom: 18px;
            transition: all 0.3s ease;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .booking-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            transform: translateY(-2px);
            border-color: #d0d0d0;
        }
        .booking-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            flex-shrink: 0;
        }
        .booking-details {
            flex: 1;
            min-width: 0;
        }
        .booking-name {
            font-size: 1.15rem;
            font-weight: 700;
            margin: 0 0 12px 0;
            color: #222;
            line-height: 1.3;
        }
        .booking-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 12px;
            text-transform: capitalize;
        }
        .status-reserved {
            color: #d32f2f;
        }
        .status-cancelled {
            color: #d32f2f;
        }
        .status-completed {
            color: #2e7d32;
        }
        .booking-info {
            font-size: 0.9rem;
            color: #555;
            margin: 5px 0;
            line-height: 1.6;
        }
        .booking-info strong {
            font-weight: 600;
            color: #333;
        }
        .booking-meta {
            text-align: right;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-end;
            min-width: 120px;
        }
        .booking-days {
            font-size: 0.85rem;
            color: #999;
            margin-bottom: 8px;
        }
        .cancel-link {
            color: #d32f2f;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.2s;
        }
        .cancel-link:hover {
            background: rgba(211, 47, 47, 0.1);
            text-decoration: none;
        }
        @media (max-width: 968px) {
            .profile-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <script>
        function goToPayment(){window.location.href="payment.php";}
        function goToOrder(menuSlug){
            let target = "order.php";
            if (menuSlug){
                target += "?menu=" + encodeURIComponent(menuSlug);
            }
            window.location.href = target;
        }
        function gotomenu(){window.location.href="menu.php";}
        function gotoregister(){window.location.href="registasi.php";}
        function gotologin(){window.location.href="login.php";}
        function gotohome(){window.location.href="index.php";}
        function gotocontact(){window.location.href="contact.php";}
        function signout(){window.location.href="logout.php";}
        
        // OTP Timer
        let otpTimer = 319; // 5:19 in seconds
        function updateOTPTimer() {
            const minutes = Math.floor(otpTimer / 60);
            const seconds = otpTimer % 60;
            const timerEl = document.getElementById('otp-timer');
            if (timerEl) {
                timerEl.textContent = `Resend One Time Password in ${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }
            if (otpTimer > 0) {
                otpTimer--;
                setTimeout(updateOTPTimer, 1000);
            }
        }
        
        // Start timer on page load
        window.addEventListener('DOMContentLoaded', function() {
            updateOTPTimer();
        });
        
        // Avatar selection
        function selectAvatar(index) {
            // Remove selected class from all avatars (including upload option)
            document.querySelectorAll('.avatar-option').forEach(el => {
                if (!el.classList.contains('upload-option')) {
                    el.classList.remove('selected');
                } else {
                    // Remove selected from upload option when selecting basic avatar
                    el.classList.remove('selected');
                }
            });
            
            // Get the clicked avatar option
            const selectedOption = event.target.closest('.avatar-option');
            if (selectedOption) {
                selectedOption.classList.add('selected');
                
                // Update hidden input for form submission - CRITICAL for Save Changes
                const selectedAvatarInput = document.getElementById('selected_avatar');
                if (selectedAvatarInput) {
                    selectedAvatarInput.value = index;
                }
                
                // Update navbar avatar IMMEDIATELY (before AJAX) - INSTANT UPDATE
                const selectedImg = selectedOption.querySelector('img');
                const navbarAvatar = document.querySelector('.navbar-rajan .avatar');
                
                if (selectedImg && navbarAvatar) {
                    // Get the image source and update navbar immediately
                    let avatarSrc = selectedImg.src;
                    // Remove any existing query parameters
                    avatarSrc = avatarSrc.split('?')[0];
                    // Add timestamp for cache busting
                    navbarAvatar.src = avatarSrc + '?t=' + new Date().getTime();
                    
                    // Also update by ID if exists
                    const navbarAvatarById = document.getElementById('user-avatar-navbar');
                    if (navbarAvatarById) {
                        navbarAvatarById.src = avatarSrc + '?t=' + new Date().getTime();
                    }
                }
                
                // Save avatar selection via AJAX (for persistence across pages)
                saveAvatarSelection(index);
            }
        }
        
        // Save avatar selection via AJAX
        function saveAvatarSelection(avatarIndex) {
            const formData = new FormData();
            formData.append('action', 'save_avatar_selection');
            formData.append('avatar_index', avatarIndex);
            
            fetch('<?php echo h($_SERVER['PHP_SELF']); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Avatar already updated in navbar by selectAvatar()
                    // This AJAX call saves to database and session for persistence
                    console.log('Avatar selection saved successfully');
                    
                    // Store in localStorage as backup for immediate access
                    localStorage.setItem('selected_avatar_index', avatarIndex);
                    localStorage.setItem('avatar_timestamp', new Date().getTime());
                }
            })
            .catch(error => {
                console.error('Error saving avatar:', error);
            });
        }
        
        // Update navbar avatar
        function updateNavbarAvatar(avatarSrc = null) {
            const navbarAvatar = document.querySelector('.navbar-rajan .avatar');
            if (navbarAvatar) {
                if (avatarSrc) {
                    navbarAvatar.src = avatarSrc;
                    // Add cache busting to force reload
                    navbarAvatar.src = avatarSrc + '?t=' + new Date().getTime();
                } else {
                    // Get current selected avatar
                    const selectedAvatar = document.querySelector('.avatar-option.selected:not(.upload-option)');
                    if (selectedAvatar) {
                        const img = selectedAvatar.querySelector('img');
                        if (img) {
                            navbarAvatar.src = img.src + '?t=' + new Date().getTime();
                        }
                    }
                }
            }
        }
        
        // Handle avatar upload from device
        function handleAvatarUpload(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Validate file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar. Maksimal 5MB.');
                    input.value = '';
                    return;
                }
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Format file tidak didukung. Gunakan JPG, PNG, atau GIF.');
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Update navbar avatar IMMEDIATELY
                    const navbarAvatar = document.querySelector('.navbar-rajan .avatar');
                    const navbarAvatarById = document.getElementById('user-avatar-navbar');
                    const previewSrc = e.target.result;
                    
                    if (navbarAvatar) {
                        navbarAvatar.src = previewSrc + '?t=' + new Date().getTime();
                    }
                    if (navbarAvatarById) {
                        navbarAvatarById.src = previewSrc + '?t=' + new Date().getTime();
                    }
                    
                    // Highlight upload option and remove selected from basic avatars
                    document.querySelectorAll('.avatar-option').forEach(el => {
                        if (!el.classList.contains('upload-option')) {
                            el.classList.remove('selected');
                        }
                    });
                    document.querySelectorAll('.avatar-option.upload-option').forEach(el => el.classList.add('selected'));
                    
                    // Update hidden input for form submission
                    const selectedAvatarInput = document.getElementById('selected_avatar');
                    if (selectedAvatarInput) {
                        selectedAvatarInput.value = 'upload';
                    }
                    
                    // Upload file via AJAX
                    uploadAvatarFile(file);
                };
                reader.readAsDataURL(file);
            }
        }
        
        // Upload avatar file via AJAX
        function uploadAvatarFile(file) {
            const formData = new FormData();
            formData.append('action', 'upload_avatar');
            formData.append('avatar_upload', file);
            
            fetch('<?php echo h($_SERVER['PHP_SELF']); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update navbar with server path
                    const navbarAvatar = document.querySelector('.navbar-rajan .avatar');
                    const navbarAvatarById = document.getElementById('user-avatar-navbar');
                    const avatarPath = data.avatar + '?t=' + new Date().getTime();
                    
                    if (navbarAvatar) {
                        navbarAvatar.src = avatarPath;
                    }
                    if (navbarAvatarById) {
                        navbarAvatarById.src = avatarPath;
                    }
                    
                    console.log('Avatar uploaded successfully');
                } else {
                    alert(data.message || 'Gagal mengupload avatar');
                }
            })
            .catch(error => {
                console.error('Error uploading avatar:', error);
                alert('Terjadi kesalahan saat mengupload avatar');
            });
        }
        
        // OTP input focus
        function handleOTPInput(e) {
            if (e.target.value.length === 1 && e.target.nextElementSibling) {
                e.target.nextElementSibling.focus();
            }
        }
    </script>

    <nav class="navbar-rajan">
        <div class="navbar-left">
            <img src="img/Cokelat Krem Ilustrasi Imut Logo Martabak Manis (3).png" alt="Martabak Rajan Logo" class="logo-img">
            <span class="welcome-text">
                <b>Welcome to</b> <span class="brand-name">Martabak Raj'an</span>
            </span>
        </div>
        <div class="navbar-right">
            <a href="index.php" class="nav-link">Home</a>
            <a href="contact.php" class="nav-link">Contact Us</a>
            <a href="javascript:void(0)" class="nav-link" onclick="signout()">Sign Out</a>
            <span class="vertical-divider"></span>
            <div class="user-profile">
                <span class="user-name"><?php echo h($user['full_name']); ?></span>
                <img src="<?php echo h($currentAvatar); ?>?v=<?php echo $avatarTimestamp; ?>" alt="User Avatar" class="avatar" id="user-avatar-navbar">
            </div>
        </div>
    </nav>

    <div class="profile-container">
        <div class="profile-header">
            <a href="javascript:void(0)" onclick="gotohome()"><i class="fas fa-chevron-left"></i> My Profile</a>
        </div>

        <div class="profile-content">
            <!-- Left Section: My Profile -->
            <div class="profile-section">
                <h2 class="section-title">< My Profile</h2>
                
                <?php if ($updateSuccess): ?>
                    <div class="alert alert-success">Profil berhasil diperbarui!</div>
                <?php endif; ?>
                <?php if ($uploadMessage): ?>
                    <div class="alert alert-success"><?php echo h($uploadMessage); ?></div>
                <?php endif; ?>
                <?php if ($uploadError): ?>
                    <div class="alert alert-error"><?php echo h($uploadError); ?></div>
                <?php endif; ?>
                
                <!-- Avatar Selection -->
                <div class="avatar-selection">
                    <!-- Upload Option (Paling Kiri) - Hidden File Input -->
                    <input type="file" id="avatar_upload" name="avatar_upload" accept="image/jpeg,image/jpg,image/png,image/gif" style="display: none;" onchange="handleAvatarUpload(this)">
                    <div class="avatar-option upload-option <?php echo $hasUploadedPhoto ? 'selected' : ''; ?>" onclick="document.getElementById('avatar_upload').click()" title="Upload Foto dari Device">
                        <i class="fas fa-camera"></i>
                    </div>
                    <!-- Avatar PNG Options -->
                    <div class="avatar-option <?php echo !$hasUploadedPhoto && $selectedAvatarIndex == 1 ? 'selected' : ''; ?>" onclick="selectAvatar(1)" title="Avatar 1">
                        <img src="img/profile 1.png" alt="Avatar 1" onerror="this.src='img/converted_image.png'">
                    </div>
                    <div class="avatar-option <?php echo !$hasUploadedPhoto && $selectedAvatarIndex == 2 ? 'selected' : ''; ?>" onclick="selectAvatar(2)" title="Avatar 2">
                        <img src="img/profile 2.png" alt="Avatar 2" onerror="this.src='img/converted_image.png'">
                    </div>
                    <div class="avatar-option <?php echo !$hasUploadedPhoto && $selectedAvatarIndex == 3 ? 'selected' : ''; ?>" onclick="selectAvatar(3)" title="Avatar 3">
                        <img src="img/profile 3.png" alt="Avatar 3" onerror="this.src='img/converted_image.png'">
                    </div>
                    <div class="avatar-option <?php echo !$hasUploadedPhoto && $selectedAvatarIndex == 4 ? 'selected' : ''; ?>" onclick="selectAvatar(4)" title="Avatar 4">
                        <img src="img/converted_image.png" alt="Avatar 4" onerror="this.src='img/converted_image.png'">
                    </div>
                    <div class="avatar-option <?php echo !$hasUploadedPhoto && $selectedAvatarIndex == 5 ? 'selected' : ''; ?>" onclick="selectAvatar(5)" title="Avatar 5">
                        <img src="img/profile 4.png" alt="Avatar 5" onerror="this.src='img/converted_image.png'">
                    </div>
                    <div class="avatar-option <?php echo !$hasUploadedPhoto && $selectedAvatarIndex == 6 ? 'selected' : ''; ?>" onclick="selectAvatar(6)" title="Avatar 6">
                        <img src="img/profile 6.png" alt="Avatar 6" onerror="this.src='img/converted_image.png'">
                    </div>
                </div>

                <form method="POST" action="<?php echo h($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data" onsubmit="updateNavbarAvatar(); return true;">
                    <input type="hidden" name="action" value="update_profile">
                    <input type="hidden" name="selected_avatar" id="selected_avatar" value="<?php echo $hasUploadedPhoto ? 'upload' : $selectedAvatarIndex; ?>">
                    
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" value="<?php echo h($user['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" value="<?php echo h($user['full_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Mobile Number</label>
                        <div class="phone-input-group">
                            <select name="phone_code">
                                <option value="+62" <?php echo $phoneCode === '+62' ? 'selected' : ''; ?>>+62</option>
                                <option value="+60" <?php echo $phoneCode === '+60' ? 'selected' : ''; ?>>+60</option>
                                <option value="+65" <?php echo $phoneCode === '+65' ? 'selected' : ''; ?>>+65</option>
                                <option value="+1" <?php echo $phoneCode === '+1' ? 'selected' : ''; ?>>+1</option>
                            </select>
                            <input type="tel" name="phone" value="<?php echo h($phoneNumber); ?>" placeholder="0950-0637-3633" required>
                        </div>
                    </div>

                    <button type="button" class="otp-button" onclick="alert('OTP akan dikirim ke email Anda')">Get One Time Password</button>

                    <div class="otp-inputs">
                        <input type="text" class="otp-input" maxlength="1" oninput="handleOTPInput(event)" pattern="[0-9]">
                        <input type="text" class="otp-input" maxlength="1" oninput="handleOTPInput(event)" pattern="[0-9]">
                        <input type="text" class="otp-input" maxlength="1" oninput="handleOTPInput(event)" pattern="[0-9]">
                        <input type="text" class="otp-input" maxlength="1" oninput="handleOTPInput(event)" pattern="[0-9]">
                    </div>
                    <p class="otp-timer" id="otp-timer">Resend One Time Password in 05:19</p>

                    <div class="profile-actions">
                        <button type="button" class="btn btn-secondary" onclick="gotohome()">Go Back</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>

            <!-- Right Section: History and Recent Bookings / My Slots -->
            <div class="profile-section">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <h2 class="section-title">History and Recent Bookings</h2>
                    <div>
                        <a href="profile.php?tab=history" class="mode-btn" style="margin-right:8px; <?php echo $activeTab === 'history' ? 'border-color:#FF8C00;background:#fff5eb;color:#FF8C00;' : ''; ?>">All</a>
                        <a href="profile.php?tab=myslots" class="mode-btn" style="<?php echo $activeTab === 'myslots' ? 'border-color:#FF8C00;background:#fff5eb;color:#FF8C00;' : ''; ?>">My Slot</a>
                    </div>
                </div>

                <?php
                $listToShow = ($activeTab === 'myslots' && !empty($userBookings)) ? $userBookings : ($activeTab === 'myslots' ? [] : $bookings);
                if (empty($listToShow)) {
                    echo '<p class="small">No bookings to display.</p>';
                }
                foreach ($listToShow as $booking): ?>
                    <div class="booking-card">
                        <?php
                        // Prefer explicit stored menu_slug if available
                        $menuSlug = '';
                        if (!empty($booking['menu_slug'])) {
                            $menuSlug = $booking['menu_slug'];
                        } elseif (!empty($booking['menu_name'])) {
                            // if menu_name looks like a slug (contains '-') use it
                            if (strpos($booking['menu_name'], '-') !== false) {
                                $menuSlug = $booking['menu_name'];
                            } else {
                                // Fallback: derive probable slug by lowercasing and replacing spaces
                                $derived = strtolower(trim($booking['menu_name']));
                                $derived = preg_replace('/[^a-z0-9\s-]/', '', $derived);
                                $derived = preg_replace('/\s+/', '-', $derived);
                                $menuSlug = $derived;
                            }
                        }
                        $orderLink = 'order.php';
                        $params = [];
                        if ($menuSlug) {
                            $params[] = 'menu=' . urlencode($menuSlug);
                        } else {
                            // try explicit common-name mapping (case-insensitive)
                            $nameMap = [
                                'martabak maramelow' => 'martabak-marsmelow',
                                'martabak marsmelow' => 'martabak-marsmelow',
                                'coconut green martabak' => 'martabak-3-rasa',
                                'martabak kacang' => 'martabak-3-rasa'
                            ];
                            $bn = strtolower(trim($booking['menu_name'] ?? ''));
                            if (isset($nameMap[$bn])) {
                                $params[] = 'menu=' . urlencode($nameMap[$bn]);
                                $menuSlug = $nameMap[$bn];
                            }
                        }
                        // always include menu_image and menu_name so order.php can override display
                        if (!empty($booking['image'])) {
                            $params[] = 'menu_image=' . urlencode($booking['image']);
                        }
                        if (!empty($booking['menu_name'])) {
                            $params[] = 'menu_name=' . urlencode($booking['menu_name']);
                        }
                        if (!empty($params)) {
                            $orderLink .= '?' . implode('&', $params);
                        }
                        ?>
                        <a href="<?php echo h($orderLink); ?>">
                            <img src="<?php echo h($booking['image'] ?? 'img/placeholder.jpg'); ?>" alt="<?php echo h($booking['menu_name'] ?? 'Reservation'); ?>" class="booking-image">
                        </a>
                        <div class="booking-details">
                            <h3 class="booking-name"><a href="<?php echo h($orderLink); ?>" style="color:inherit;text-decoration:none"><?php echo h($booking['menu_name'] ?? 'Reservation'); ?></a></h3>
                            <span class="booking-status status-<?php echo strtolower($booking['status'] ?? 'reserved'); ?>">
                                <?php echo h($booking['status'] ?? 'Reserved'); ?>
                            </span>
                            <p class="booking-info"><strong><?php echo h($booking['date'] ?? ''); ?> | <?php echo h($booking['time'] ?? ''); ?></strong></p>
                            <p class="booking-info"><?php echo h($booking['guests'] ?? 1); ?> Guests</p>
                        </div>
                        <div class="booking-meta">
                            <span class="booking-days"><?php echo h($booking['days_ago'] ?? 0); ?> days ago</span>
                            <?php if (($booking['status'] ?? 'Reserved') === 'Reserved'): ?>
                                <form method="POST" action="<?php echo h($_SERVER['PHP_SELF']); ?>" style="display: inline; margin-top: 8px;">
                                    <input type="hidden" name="action" value="cancel_booking">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <button type="submit" class="cancel-link" style="background: none; border: none; cursor: pointer; padding: 0;">Cancel Booking</button>
                                </form>
                            <?php endif; ?>

                            <?php // show delete (trash) button for bookings that belong to current user (match by email or by customer name as fallback) ?>
                            <?php
                                $ownsBooking = false;
                                if (!empty($currentUserEmail) && !empty($booking['user_email']) && strtolower($booking['user_email']) === $currentUserEmail) {
                                    $ownsBooking = true;
                                } elseif (!empty($booking['customer']) && !empty($_SESSION['user']['full_name']) && strtolower(trim($booking['customer'])) === strtolower(trim($_SESSION['user']['full_name']))) {
                                    $ownsBooking = true;
                                }
                            ?>
                            <?php if ($ownsBooking): ?>
                                <form method="POST" action="<?php echo h($_SERVER['PHP_SELF']); ?>" style="display:inline;margin-top:6px;">
                                    <input type="hidden" name="action" value="delete_booking">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <button type="submit" title="Delete booking" style="background:none;border:none;cursor:pointer;padding:0;margin-top:6px;color:#d32f2f;font-size:18px">🗑️</button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($menuSlug): ?>
                                <div style="margin-top:8px">
                                    <a href="<?php echo h($orderLink); ?>" class="btn btn-secondary" style="padding:8px 12px;font-size:0.9rem;text-decoration:none">Book Again</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <section class="download-app-section">
        <div class="download-content">
            <h2 class="download-title">DOWNLOAD THE APP</h2>
            <div class="app-buttons">
                <a href="https://play.google.com/store/games?hl=en" class="app-btn android-btn">
                    <i class="fab fa-google-play"></i> Get It On Android
                </a>
                <a href="https://www.apple.com/id/app-store/" class="app-btn ios-btn">
                    <i class="fab fa-apple"></i> Get It On iOS
                </a>
            </div>
        </div>
    </section>

    <section class="register-promo">
        <div class="register-overlay">
            <h3 class="register-title">REGISTER FOR FREE</h3>
            <p class="register-subtitle">Register with us and win amazing discount points on <span>Martabak Raj'an</span></p>
            <button class="register-btn" onclick="gotoregister()">Register</button>
        </div>
    </section>

    <footer class="footer-rajan">
        <div class="footer-details-wrapper">
            <span class="footer-brand">Martabak Raj'an</span>
            <nav class="footer-links">
                <a href="menu.php">Service</a>
                <a href="#">About Us</a>
                <a href="contact.php">Contact Us</a>
                <a href="#">FAQs</a>
                <a href="login.php">Sign In</a>
            </nav>
            <div class="footer-social-icons">
                <span class="social-icon-circle"><i class="fab fa-facebook-f"></i></span>
                <span class="social-icon-circle"><i class="fab fa-twitter"></i></span>
                <span class="social-icon-circle"><i class="fab fa-instagram"></i></span>
            </div>
        </div>
        <p class="footer-note">Copyright by Arif Firmansyah</p>
    </footer>
</body>
</html>
