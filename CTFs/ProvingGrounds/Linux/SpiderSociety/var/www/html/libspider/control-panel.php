<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Function to load .env variables
function loadEnv($file) {
    if (!file_exists($file)) {
        return;
    }
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Load environment variables
loadEnv(__DIR__ . '/.fuhfjkzbdsfuybefzmdbbzdcbhjzdbcukbdvbsdvuibdvnbdvenv');

// Retrieve credentials
$ftp_user = $_ENV['FTP_BACKUP_USER'] ?? 'default_user';
$ftp_pass = $_ENV['FTP_BACKUP_PASS'] ?? 'default_pass';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/images/spider-logo.svg" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spider Society Control Panel</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }

        .control-panel {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 400px;
        }

        .control-panel h1 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #e94560;
        }

        .control-panel img {
            width: 80px;
            margin-bottom: 20px;
        }

        .menu {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .menu button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: #e94560;
            color: #fff;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .menu button:hover {
            background: #d1344f;
        }

        .logout {
            margin-top: 20px;
            font-size: 0.9rem;
            color: #aaa;
        }

        .logout a {
            color: #e94560;
            text-decoration: none;
        }

        .logout a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="control-panel">
        <img src="/images/spider-logo-nobck.png" alt="Spider Society Logo">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?>!</h1>
        <div class="menu"> 
            <button onclick="alert('No new reports for you, agent.')">View Reports</button>
            <button onclick="alert('Network Error: Unable to reach agency database')">Missions</button>
            <button onclick="fetchCredentials()">Communications</button>
            <script>
                async function fetchCredentials() {
                    try {
                        const response = await fetch('fetch-credentials.php');
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        const data = await response.json();
                        const messageBox = document.createElement('div');
                        messageBox.style.position = 'fixed';
                        messageBox.style.top = '50%';
                        messageBox.style.left = '50%';
                        messageBox.style.transform = 'translate(-50%, -50%)';
                        messageBox.style.background = '#000'; // Changed to solid black
                        messageBox.style.color = '#fff';
                        messageBox.style.padding = '20px';
                        messageBox.style.borderRadius = '10px';
                        messageBox.style.boxShadow = '0 4px 10px rgba(0, 0, 0, 0.3)';
                        messageBox.style.textAlign = 'center';
                        messageBox.innerHTML = `
                            <h2>New Message from Tech Dept</h2>
                            <p>We created your new backup user. Store these credentials safely, agent.</p>
                            <p><strong>Username:</strong> ${data.FTP_BACKUP_USER}</p>
                            <p><strong>Password:</strong> ${data.FTP_BACKUP_PASS}</p>
                            <button style="margin-top: 10px; padding: 10px; background: #e94560; color: #fff; border: none; border-radius: 5px; cursor: pointer;" onclick="this.parentElement.remove()">Close</button>
                        `;
                        document.body.appendChild(messageBox);
                    } catch (error) {
                        alert('Failed to fetch credentials: ' + error.message);
                    }
                }
            </script>
        </div>
        <p class="logout">Not you? <a href="logout.php">Log out</a></p>
    </div>
</body>
</html>
