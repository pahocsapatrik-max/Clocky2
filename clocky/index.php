<?php
session_start();
include 'config.php';

// Automatikus átirányítás, ha már be van jelentkezve
if (isset($_SESSION['logged_in'], $_SESSION['role']) && $_SESSION['logged_in'] === true) {
    $role = (int)$_SESSION['role'];
    if ($role === 1) {
        header('Location: kezdolap'); // .htaccess-es szép név
        exit;
    } else {
        header('Location: pult'); // .htaccess-es szép név
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Minden mezőt töltsön ki!';
    } else {
        $stmt = $conn->prepare(
            'SELECT id, username, password, name, jogosultsag 
             FROM users 
             WHERE username = ?'
        );
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Jelszó ellenőrzés (Ha sima szövegként tárolod, akkor ez jó, 
            // de élesben password_verify javasolt)
            if ($password === trim($user['password'])) {
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = (int)$user['jogosultsag'];

                $role = (int)$user['jogosultsag'];
                if ($role === 1) {
                    header('Location: kezdolap');
                } else {
                    header('Location: pult');
                }
                exit;
            } else {
                $error = 'Hibás felhasználónév vagy jelszó!';
            }
        } else {
            $error = 'Hibás felhasználónév vagy jelszó!';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés | Clocky</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0f0f0f;
            --card-bg: #1a1a1a;
            --accent-color: #00ffe1;
            --text-secondary: #888;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at top right, #1e1e1e, var(--bg-color));
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
            overflow: hidden;
        }

        .login-card {
            background: var(--card-bg);
            padding: 50px 40px;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.8);
            width: 90%;
            max-width: 400px;
            text-align: center;
            position: relative;
            animation: cardEntrance 0.8s ease-out forwards;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: -2px; left: -2px; right: -2px; bottom: -2px;
            background: linear-gradient(45deg, var(--accent-color), transparent, var(--accent-color));
            border-radius: 26px;
            z-index: -1;
            opacity: 0.1;
            animation: glowPulse 3s infinite alternate ease-in-out;
        }

        .brand-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 30px;
        }

        .brand-header i {
            color: var(--accent-color);
            font-size: 2.2rem;
            animation: hourglassFlip 3s infinite ease-in-out;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 900;
            letter-spacing: -2px;
            color: #fff;
            display: flex;
            align-items: baseline;
        }

        h1 span { 
            display: inline-block;
            width: 10px;
            height: 10px;
            background-color: var(--accent-color);
            margin-left: 4px;
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-secondary);
            margin-bottom: 8px;
            padding-left: 5px;
        }

        input {
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 1rem;
            transition: 0.3s;
        }

        input:focus {
            outline: none;
            border-color: var(--accent-color);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 20px rgba(0, 255, 225, 0.1);
        }

        button {
            width: 100%;
            padding: 16px;
            background: var(--accent-color);
            color: #1a1a1a;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 800;
            text-transform: uppercase;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 255, 225, 0.3);
            filter: brightness(1.1);
        }

        .error {
            background: rgba(255, 71, 87, 0.1);
            color: #ff4757;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            border: 1px solid rgba(255, 71, 87, 0.2);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        @keyframes cardEntrance {
            from { opacity: 0; transform: translateY(30px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes hourglassFlip {
            0% { transform: rotate(0); }
            40% { transform: rotate(180deg); }
            50% { transform: rotate(180deg); }
            90% { transform: rotate(360deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="brand-header">
        <i class="fas fa-hourglass-half"></i>
        <h1>clocky<span></span></h1>
    </div>

    <?php if (isset($error)): ?>
        <div class="error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label><i class="fas fa-user"></i> Felhasználónév</label>
            <input type="text" name="username" placeholder="Felhasználónév" required autocomplete="username">
        </div>

        <div class="form-group">
            <label><i class="fas fa-lock"></i> Jelszó</label>
            <input type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
        </div>

        <button type="submit">Belépés <i class="fas fa-arrow-right"></i></button>
    </form>
</div>

</body>
</html>