<?php
require 'config.php';
require 'db.php';

//Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$message_color = "red";

//Fetching logged user details
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT full_name, id_number FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

//Validatation
$validation_message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hash_number'])) {
    $hash_number = $_POST['hash_number'];
    $hash_check_stmt = $pdo->prepare("SELECT * FROM certificates WHERE hash = ?");
    $hash_check_stmt->execute([$hash_number]);
    $certificate = $hash_check_stmt->fetch();

    if ($certificate) {
        $validation_message = "Hash validated successfully!";
        $message_color = "green";
    } else {
        $validation_message = "Hash not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdNU CEVAS - Validate Hash</title>
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
	.hash-checker { display: flex; gap: 10px; } 
	.hash-input { flex: 1; padding: 10px; font-size: 14px; border: 1px solid #ccc; border-radius: 4px; } 
	.check-button { padding: 10px 20px; font-size: 14px; color: white; background-color: #b38f4d; border: none; border-radius: 4px; cursor: pointer; transition: background-color 0.3s; } 
	.check-button:hover { background-color: #a27d3c; }
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
                    <h2 class="user-name"><?= htmlspecialchars($user['full_name']); ?></h2>
                    <p class="user-details">Test Course</p>
                    <p class="user-id"><?= htmlspecialchars($user['id_number']); ?></p>
                </div>
                <nav class="menu">
                    <button class="menu-item active" onclick="location.href='ValidatePHP.php'">Validate Hash</button>
                    <button class="menu-item" onclick="location.href='GeneratePHP.php'">Generate Hash</button>
                    <button class="menu-item" onclick="location.href='SearchPHP.php'">Search Certificates</button>
                    <button class="menu-item" onclick="location.href='LogoutPHP.php'">Log Out</button>
                </nav>
            </aside>
            <main class="content">
                <h2>Validate Hash</h2>
                <p>Confirm your hash by checking it here.</p>
                <form method="post" class="hash-checker">
                    <input type="text" name="hash_number" placeholder="Hash Number" class="hash-input" required>
                    <button type="submit" class="check-button">Check</button>
                </form>
                <?php if ($validation_message): ?>
                    <p class="message" style="color: <?= htmlspecialchars($message_color); ?>;">
                        <?= htmlspecialchars($validation_message); ?>
                    </p>
                <?php endif; ?>
            </main>
        </div>
    </div>
</body>
</html>

