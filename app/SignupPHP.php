<?php
require 'config.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $id_number = $_POST['id_number'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, id_number) VALUES (?, ?, ?, ?)");
            $stmt->execute([$full_name, $email, $password_hash, $id_number]);
            header("Location: LoginPHP.php");
            exit();
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
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
    <title>CEVAS | Sign Up</title>
    <style>
	* { font-family: Poppins; } 
	body, h1, h2, h3, p, a, input, button { margin: 0; padding: 0; box-sizing: border-box; } 
	body { background-color: #f9f9f9; display: flex; flex-direction: column; height: 100vh; margin: 0; } 
	.full-screen-container { display: flex; flex-direction: column; height: 100%; } 
	.header { background-color: #003366; color: white; display: flex; align-items: center; padding: 10px 20px; gap: 15px; flex-shrink: 0; } 
	.header .logo { width: 55px; height: 55px; } 
	.header h1 { font-size: 20px; font-weight: bold; } 
	.login-section { display: flex; align-items: center; justify-content: center; flex-grow: 1; padding: 20px; background-color: #f9f9f9; } 
	.login-box { background: hsla(0, 4%, 91%, 0.941); width: 100%; max-width: 400px; padding: 70px; text-align: center; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); border-radius: 8px; } 
	.logo-container { margin-bottom: 15px; } 
	.cevas-logo { width: 150px; height: 150px; padding-bottom: 1rem; } 
	h2 { font-size: 22px; margin-bottom: 15px; color: #003366; } 
	.welcome-text { font-size: 18px; margin-bottom: 25px; color: #333333; } 
	.login-form { width: 100%; margin-bottom: 20px; } 
	.form-group { margin-bottom: 20px; width: 100%; } 
	.form-group input { width: 100%; padding: 12px; font-size: 14px; border: 1px solid #ddd; border-radius: 4px; outline: none; transition: border-color 0.3s; } 
	.form-group input:focus { border-color: #003366; } 
	.form-actions { display: flex; justify-content: space-between; align-items: center; font-size: 12px; margin-bottom: 20px; } 
	.forgot-password { color: #003366; text-decoration: none; } 
	.forgot-password:hover { text-decoration: underline; } 
	.btn { display: inline-block; padding: 12px; font-size: 14px; border: none; border-radius: 4px; cursor: pointer; text-align: center; color: white; transition: background-color 0.3s; } 
	.signup-section { display: flex; align-items: center; justify-content: center; flex-grow: 1; padding: 20px; background-color: #f9f9f9; } 
	.signup-box { background: #ffffff; width: 100%; max-width: 400px; padding: 30px; text-align: center; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); border-radius: 8px; } 
	h3.welcome-text { font-size: 18px; margin-bottom: 25px; color: #333333; } 
	.signup-btn { background-color: #003366; width: 100%; margin-bottom: 10px; } 
	.signup-btn:hover { background-color: #002244; } 
	.login-link { font-size: 12px; color: #333333; } 
	.login-link a { color: #003366; text-decoration: none; font-weight: bold; } 
	.login-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="full-screen-container">
        <header class="header">
            <a>
                <img src="/images/adnulogo.png" alt="AdNU Logo" class="logo">
            </a>
            <h1>Ateneo Certificate Validation System</h1>
        </header>
        <main class="signup-section">
            <div class="signup-box">
                <div class="logo-container">
                    <img src="/images/adnulogo.png" alt="CEVAS Logo" class="cevas-logo">
                    <h2>AdNU CEVAS</h2>
                </div>
                <h3 class="welcome-text">Create Your Account</h3>
                <?php if (isset($error)): ?>
                    <p style="color: red;"><?= $error ?></p>
                <?php endif; ?>
                <form method="post" class="signup-form">
                    <div class="form-group">
                        <input type="text" name="full_name" placeholder="Enter your full name" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="id_number" placeholder="Enter your ID number" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" placeholder="Create a password" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="confirm_password" placeholder="Confirm your password" required>
                    </div>
                    <button type="submit" class="btn signup-btn">Sign Up</button>
                </form>
                <p class="login-link">
                    Already have an account? <a href="LoginPHP.php">Login here</a>
                </p>
            </div>
        </main>
    </div>
</body>
</html>