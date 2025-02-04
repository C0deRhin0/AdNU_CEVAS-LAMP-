<?php
session_start();
require 'db.php';

//Check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$certificates = [];
$message = null;
$searched_id = "";

//fetch logged user details
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searched_id = $_POST['student_id'];

    //Checks for student_id, if positive, fetch certif
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id_number = ?");
    $stmt->execute([$searched_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        $stmt = $pdo->prepare("SELECT * FROM certificates WHERE student_id = ?");
        $stmt->execute([$searched_id]);
        $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($certificates)) {
            $message = "No certificates found.";
        }
    } else {
        $message = "Invalid Student ID.";
    }
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
    <title>AdNU CEVAS - Search</title>
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
        .content { flex: 1; padding: 40px; overflow-y: auto; }
        .content h2 { font-size: 25px; color: #333; margin-bottom: 10px; }
        .content p { font-size: 16px; color: #666; margin-bottom: 20px; }
        .search-form { display: flex; gap: 10px; max-width: 500px; align-items: center; }
        .input-field { flex: 1; padding: 10px; font-size: 14px; border: 1px solid #ccc; border-radius: 4px; }
        .search-button { padding: 10px 20px; font-size: 14px; color: white; background-color: #b38f4d; border: none; border-radius: 4px; cursor: pointer; transition: background-color 0.3s; }
        .search-button:hover { background-color: #a27d3c; }
        .certificate-list { display: flex; flex-direction: column; gap: 20px; }
        .certificate-card { margin-top: 1rem; padding: 15px; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); cursor: pointer; transition: transform 0.2s; text-decoration: none; color: inherit; }
        .certificate-card:hover { transform: scale(1.02); text-decoration: color: none; }
        .certificate-card h3 { font-size: 16px; margin: 0 0 5px; color: #333; }
        .certificate-card p { margin: 0; font-size: 14px; color: #555; }
    </style>
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
                <h2>Certificates</h2>
                <p>Use the ID Number of the student to search for certificates.</p>
                <form class="search-form" method="post">
                    <input type="text" class="input-field" name="student_id" value="<?= htmlspecialchars($searched_id); ?>" placeholder="Search by Student ID" required>
                    <button type="submit" class="search-button">Search</button>
                </form>
                <?php if ($message): ?>
                    <p style="color: red;"><?= htmlspecialchars($message); ?></p>
                <?php elseif ($certificates): ?>
                    <div class="certificate-list">
                        <?php foreach ($certificates as $certificate): ?>
                            <a href="ViewCertPHP.php?cert_id=<?= urlencode($certificate['id']); ?>" class="certificate-card">
                                <h3><?= htmlspecialchars($certificate['certificate_name']); ?></h3>
                                <p><strong>Date Issued:</strong> <?= htmlspecialchars($certificate['date_issued']); ?></p>
                                <p><strong>Certified Hash:</strong> <?= htmlspecialchars($certificate['hash']); ?></p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</body>
</html>

