<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdNU CEVAS</title>
    <style>
    	* { font-family: Poppins; } 
	body, html { margin: 0; padding: 0; width: 100%; height: 100%; background-color: #f9f9f9; display: flex; justify-content: center; align-items: center; } 
	.headcontainer { display: flex; flex-direction: column; height: 100%; width: 100%; } 
	.header { background-color: #003366; color: white; display: flex; align-items: center; padding: 10px 20px; gap: 15px; flex-shrink: 0; } 
	.header .logo { width: 55px; height: 55px; } 
	.header h1 { font-size: 20px; margin: 0; } 
	.content { flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; padding: 20px; box-sizing: border-box; } 
	.welcome-section { max-width: 600px; padding: 20px; } 
	.logo { width: 150px; height: auto; margin-bottom: 20px; } 
	h2 { font-size: 24px; color: #333; margin-bottom: 15px; } 
	.description { font-size: 16px; color: #666; line-height: 1.5; margin-bottom: 30px; } 
	.continue-button { background-color: #b38f4d; color: white; font-size: 16px; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s; } 
	.continue-button:hover { background-color: #a27d3c; }
    </style>
</head>
<body>
    <div class="headcontainer">
        <header class="header">
            <img src="/images/adnulogo.png" alt="AdNU Logo" class="logo">
            <h1>Ateneo Certificate Validation System</h1>
        </header>
        <main class="content">
            <div class="welcome-section">
                <img src="/images/adnulogo.png" alt="AdNU Logo" class="logo">
                <h2>Welcome to AdNU CEVAS, User!</h2>
                <p class="description">
                    A certificate validation system that uses cryptographic hashing to create unique digital fingerprints for certificates issued by Ateneo de Naga University.
                </p>
                <form method="get" action="LoginPHP.php">
                    <button class="continue-button">Continue</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
