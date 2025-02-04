<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $stmt = $pdo->prepare("SELECT * FROM certificates WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $certificates = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdNU CEVAS - Search</title>
    <style>
    * {
        font-family: Poppins;
    }
    body, html {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100%;
        background-color: #f9f9f9;
    }

    .headcontainer {
        display: flex;
        flex-direction: column;
        height: 100%;
        width: 100%;
    }

    .header {
        background-color: #003366;
        color: white;
        display: flex;
        align-items: center;
        padding: 10px 20px;
        gap: 15px;
        flex-shrink: 0;
    }

    .header .logo {
        width: 55px;
        height: 55px;
    }

    .header h1 {
        font-size: 20px;
        margin: 0;
    }

    .main-content {
        display: flex;
        flex: 1;
        overflow: hidden;
    }

    .sidebar {
        background-color: #333;
        color: white;
        width: 250px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        overflow-y: auto;
    }

    .user-profile {
        text-align: center;
        margin-bottom: 30px;
    }

    .avatar img {
        width: 150px;
        height: 150px;
        border-radius: 50%;
    }

    .user-name {
        font-size: 20px;
        margin: 10px 0 5px;
    }

    .user-details, .user-id {
        font-size: 13px;
        margin: 0;
    }

    .menu {
        width: 100%;
    }

    .menu-item {
        display: block;
        width: 100%;
        background: none;
        border: none;
        color: white;
        text-align: left;
        padding: 10px 20px;
        margin: 5px 0;
        font-size: 14px;
        cursor: pointer;
        border-radius: 4px;
        transition: background-color 0.3s;
    }

    .menu-item:hover, .menu-item.active {
        background-color: #b38f4d;
    }

    .content {
        flex: 1;
        padding: 40px;
        overflow-y: auto;
    }

    .content h2 {
        font-size: 25px;
        color: #333;
        margin-bottom: 10px;
    }

    .content p {
        font-size: 16px;
        color: #666;
        margin-bottom: 20px;
    }
    .search-form {
    display: flex;
    gap: 10px;
    max-width: 500px;
    align-items: center;
    }
    .input-field {
        flex: 1;
        padding: 10px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .search-button {
        padding: 10px 20px;
        font-size: 14px;
        color: white;
        background-color: #b38f4d;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .search-button:hover {
        background-color: #a27d3c;
    }

    .certificate-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .certificate-card {
        margin-top: 1rem;
        padding: 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background-color: #f9f9f9;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .certificate-card h3 {
        font-size: 16px;
        margin: 0 0 5px;
        color: #333;
    }

    .certificate-card p {
        margin: 0;
        font-size: 14px;
        color: #555;
    }
</style>
</head>
<body>
    <h2>Search Results</h2>
    <?php if (isset($certificates) && count($certificates) > 0): ?>
        <ul>
            <?php foreach ($certificates as $certificate): ?>
                <li>
                    <h3><?= $certificate['certificate_name'] ?></h3>
                    <p><strong>Date Issued:</strong> <?= $certificate['date_issued'] ?></p>
                    <p><strong>Hash:</strong> <?= $certificate['hash'] ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No results found.</p>
    <?php endif; ?>
</body>
</html>