<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="/images/spider-logo.svg" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spider Society Login</title>
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
        }

        .login-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 350px;
        }

        .login-container h1 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #e94560;
        }

        .login-container img {
            width: 80px;
            margin-bottom: 20px;
        }

        .login-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 1rem;
        }

        .login-container input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.3);
        }

        .login-container button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: none;
            border-radius: 5px;
            background: #e94560;
            color: #fff;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .login-container button:hover {
            background: #d1344f;
        }

        .login-container p {
            margin-top: 15px;
            font-size: 0.9rem;
            color: #aaa;
        }

        .login-container p a {
            color: #e94560;
            text-decoration: none;
        }

        .login-container p a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="/images/spider-logo-nobck.png" alt="Spider Society Logo">
        <h1>Spider Society <br>Control Panel</h1>
        <form action="index.php" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" onclick="handleLogin(event)">Login</button>
            <script>
                async function handleLogin(event) {
                    event.preventDefault(); // Prevent form submission

                    const form = event.target.closest('form');
                    const formData = new FormData(form);

                    const response = await fetch('login.php', { // Send to login.php
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        window.location.href = 'control-panel.php';
                    } else {
                        alert(result.message || 'Login failed. Please try again.');
                    }
                }
            </script>
        </form>
        <p>Forgot your password? <a href="/reset">Reset it here</a></p>
    </div>
</body>
</html></html>