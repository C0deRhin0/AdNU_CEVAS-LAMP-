<?php
require 'config.php';
require 'db.php';

//Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$message = "";

// Fetchlogged user details
$user_id = $_SESSION['user_id'] ?? null;
$user_details = [
    'full_name' => 'Guest User',
    'id_number' => 'N/A',
    'course' => 'test course'
];

if ($user_id) {
    $stmt = $pdo->prepare("SELECT full_name, id_number FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_details = $stmt->fetch(PDO::FETCH_ASSOC) ?: $user_details;
}

//fetch the cert_id from previous page (search)
$cert_id = $_GET['cert_id'] ?? null;
$certificate = null;

if ($cert_id) {
    $stmt = $pdo->prepare("SELECT * FROM certificates WHERE id = ?");
    $stmt->execute([$cert_id]);
    $certificate = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$certificate) {
        die("Certificate not found.");
    }
} else {
    die("No certificate ID provided.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['copy_hash'])) {
    $message = "Hash copied to clipboard!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdNU CEVAS - View Certificate</title>
    <style>
        * { font-family: Poppins; }
        body, html { margin: 0; padding: 0; width: 100%; height: 100%; background-color: #f9f9f9; }
        .headcontainer { display: flex; flex-direction: column; height: 100%; width: 100%; }
        .header { background-color: #003366; color: white; display: flex; align-items: center; padding: 10px 20px; gap: 15px; flex-shrink: 0; }
        .header .logo { width: 55px; height: 55px; }
        .header h1 { font-size: 20px; margin: 0; }
        .main-content { display: flex; flex: 1; overflow: hidden; }
        .sidebar { background-color: #333; color: white; width: 250px; padding: 20px; display: flex; flex-direction: column; align-items: center; overflow-y: auto; }
        .user-profile { text-align: center; margin-bottom: 30px; }
        .avatar img { width: 150px; height: 150px; border-radius: 50%; }
        .user-name { font-size: 20px; margin: 10px 0 5px; }
        .user-details, .user-id { font-size: 13px; margin: 0; }
        .menu { width: 100%; }
        .menu-item { display: block; width: 100%; background: none; border: none; color: white; text-align: left; padding: 10px 20px; margin: 5px 0; font-size: 14px; cursor: pointer; border-radius: 4px; transition: background-color 0.3s; }
        .menu-item:hover, .menu-item.active { background-color: #b38f4d; }
        .content { flex: 1; padding: 40px; overflow-y: auto; height: 100%; background-color: #ffffff; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .certificate-details { max-width: 600px; width: 100%; text-align: center; }
        .certificate-card { padding: 20px; border: 1px solid #ccc; border-radius: 10px; background-color: #f3f3f3; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); max-width: 100%; word-wrap: break-word; text-align: center; }
        .certificate-card h3 { font-size: 20px; margin: 0 0 10px; color: #333; }
        .certificate-card p { margin: 5px 0; font-size: 16px; color: #555; }
        .action-buttons { display: flex; justify-content: center; gap: 20px; }
        .btn { padding: 10px 20px; font-size: 14px; color: white; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s; }
        .back-btn { background-color: #003366; }
        .copy-btn { background-color: #b38f4d; }
        .btn:hover { opacity: 0.9; }
        .message { margin-top: 15px; padding: 10px; border-radius: 5px; background-color: #d4edda; color: #155724; text-align: center; font-size: 14px; }
    </style>
    <script>
        function copyToClipboard(hash) {
            navigator.clipboard.writeText(hash).then(() => {
                const messageDiv = document.getElementById('message');
                messageDiv.innerText = "Hash copied to clipboard!";
                messageDiv.style.display = "block";
            }).catch(err => {
                console.error('Failed to copy hash: ', err);
            });
        }
    </script>
</head>
<body>
    <div class="headcontainer">
        <header class="header">
            <a>
                <img src="/images/adnulogo.png" alt="AdNU Logo" class="logo">
            </a>
            <h1>Ateneo Certificate Validation System</h1>
        </header>
        <div class="main-content">
            <aside class="sidebar">
                <div class="user-profile">
                    <div class="avatar">
                        <img src="/images/user.png" alt="User Avatar">
                    </div>
                    <h2 class="user-name"><?= htmlspecialchars($user_details['full_name']); ?></h2>
                    <p class="user-details">Test Course</p>
                    <p class="user-id"><?= htmlspecialchars($user_details['id_number']); ?></p>
                </div>
                <nav class="menu">
                    <button class="menu-item" onclick="location.href='ValidatePHP.php'">Validate Hash</button>
                    <button class="menu-item" onclick="location.href='GeneratePHP.php'">Generate Hash</button>
                    <button class="menu-item active" onclick="location.href='SearchPHP.php'">Search Certificates</button>
                    <button class="menu-item" onclick="location.href='LogoutPHP.php'">Log Out</button>
                </nav>
            </aside>
            <main class="content">
                <div class="certificate-details">
                    <div class="certificate-card">
                        <h3><?= htmlspecialchars($certificate['certificate_name']); ?></h3>
                        <p><strong>Date Issued:</strong> <?= htmlspecialchars($certificate['date_issued']); ?></p>
                        <p><strong>Certified Hash:</strong> <?= htmlspecialchars($certificate['hash']); ?></p>
                    </div>
                    <div class="action-buttons">
                        <button class="btn back-btn" onclick="location.href='SearchPHP.php'">Back</button>
                        <button class="btn copy-btn" onclick="copyToClipboard('<?= htmlspecialchars($certificate['hash']); ?>')">Copy Hash</button>
                    </div>
                    <div id="message" class="message" style="display: none;"></div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>

