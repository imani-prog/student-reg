<?php
session_start();

$errors = $_SESSION['login_errors'] ?? [];
$old = $_SESSION['login_old'] ?? [];
$success = $_SESSION['login_success'] ?? null;

unset($_SESSION['login_errors'], $_SESSION['login_old'], $_SESSION['login_success']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body>
    <main class="login-page">
        <section class="login-card">
            <header>
                <p class="eyebrow">MKSU Student Portal</p>
                <h1>Welcome back</h1>
                <p class="subtitle">Sign in with your admission number or student email to continue.</p>
            </header>

            <?php if (!empty($success)) : ?>
                <div class="alert alert--success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)) : ?>
                <div class="alert alert--error">
                    <p>We couldn’t sign you in:</p>
                    <ul>
                        <?php foreach ($errors as $error) : ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="handlers/AuthHandler.php" method="POST" class="login-form" novalidate>
                <input type="hidden" name="action" value="login">
                <label class="form-field">
                    <span>Admission number or email</span>
                    <input type="text" name="identifier" required autocomplete="username"
                        value="<?= htmlspecialchars($old['identifier'] ?? '') ?>" placeholder="MKSU/COM/001/2024 or student@mksu.ac.ke">
                </label>

                <label class="form-field">
                    <span>Password</span>
                    <input type="password" name="password" required minlength="8" autocomplete="current-password"
                        placeholder="Enter your password">
                </label>

                <button type="submit" class="btn btn--primary">Sign in</button>
                <p class="helper-text">New student? <a href="register.php">Create your account</a></p>
            </form>
        </section>
    </main>
</body>

</html>