<?php
require 'config.php';
require 'db.php';

//Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$generatedHash = "";
$message = "";
$message_color = "red";

try {
    //Fetch logged user details
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT full_name, id_number FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    }

    //Submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $studentId = $_POST['student_id'];
        $certificateName = $_POST['certificate_name'];
        $dateIssued = $_POST['date_issued'];

        //checks student_id from certifs table if input id exists in id_number users table
        $stmt = $pdo->prepare("SELECT id_number FROM users WHERE id_number = ?");
        $stmt->execute([$studentId]);
        $student = $stmt->fetch();

        if ($student) {
            //Hash algorithm
            $generatedHash = hash('sha256', $studentId . $certificateName . $dateIssued . time());

            //Save
            $stmt = $pdo->prepare("INSERT INTO certificates (student_id, certificate_name, date_issued, hash) VALUES (?, ?, ?, ?)");
            $stmt->execute([$studentId, $certificateName, $dateIssued, $generatedHash]);

            $message = "Certificate successfully generated!";
            $message_color = "green";
        } else {
            $message = "Invalid Student ID. Please make sure the Student ID exists.";
        }
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
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
    <title>AdNU CEVAS - Generate Hash</title>
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
	.hash-form { display: flex; gap: 10px; max-width: 400px; } 
	.input-field { padding: 10px; font-size: 14px; border: 1px solid #ccc; border-radius: 4px; } 
	.generate-button { padding: 10px; font-size: 14px; color: white; background-color: #b38f4d; border: none; border-radius: 4px; cursor: pointer; transition: background-color 0.3s; } 
	.generate-button:hover { background-color: #a27d3c; } 
	.hash-output { margin-top: 20px; } 
	.hash-output label { display: block; font-size: 14px; color: #333; margin-bottom: 5px; } 
	.output-box { padding: 10px; background-color: #f0f0f0; border: 1px solid #ccc; border-radius: 4px; height: 50px; font-size: 14px; color: #333; overflow-wrap: break-word; } 
	.message { font-size: 16px; margin-top: 20px; }
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
                    <button class="menu-item" onclick="location.href='ValidatePHP.php'">Validate Hash</button>
                    <button class="menu-item active" onclick="location.href='GeneratePHP.php'">Generate Hash</button>
                    <button class="menu-item" onclick="location.href='SearchPHP.php'">Search Certificates</button>
                    <button class="menu-item" onclick="location.href='LogoutPHP.php'">Log Out</button>
                </nav>
            </aside>
            <main class="content">
                <h2>Generate Hash</h2>
                <p>Enter the ID Number, Certificate Name, and Date to generate a hash.</p>
                <form class="hash-form" method="post">
                    <input type="text" name="student_id" class="input-field" placeholder="Student ID" required>
                    <input type="text" name="certificate_name" class="input-field" placeholder="Certificate Name" required>
                    <input type="date" name="date_issued" class="input-field" required>
                    <button type="submit" class="generate-button">Generate</button>
                </form>
                <div class="hash-output">
                    <label>Generated Hash:</label>
                    <div class="output-box"><?= htmlspecialchars($generatedHash); ?></div>
                </div>
                <?php if ($message): ?>
                    <p class="message" style="color: <?= htmlspecialchars($message_color); ?>;">
                        <?= htmlspecialchars($message); ?>
                    </p>
                <?php endif; ?>
            </main>
        </div>
    </div>
</body>
</html>

