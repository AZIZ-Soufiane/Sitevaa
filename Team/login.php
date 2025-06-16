<?php
session_start();
require_once('config/db.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];


    if ($email == '' && $password == '') {
        $errors['general'] = 'Email and Password are required';
    } 
    else {
        if ($email == '') {
            $errors['email'] = 'Email is required';
        }

        if ($password == '') {
            $errors['password'] = 'Password is required';
        }
        
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT * FROM Team_member WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                $errors['email'] = 'Email not found';
            } else {
                if ($password != $user['password']) {
                    $errors['password'] = 'Wrong password';
                } else {
                    $_SESSION['user_id'] = $user['id_member'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['role'] = $user['role'];
                    
                    header('Location: dashboard/index.php');
                    exit();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="login.css">
    <title>SITEVAA - Login</title>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="../Landing page/images/logo.png" alt="SITEVAA Logo">
        </div>

        <?php if (isset($errors['general'])): ?>
            <div class="error-message general-error">
                <?php echo $errors['general']; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <input type="email" name="email" 
                       class="form-input <?php echo isset($errors['email']) ? 'error' : ''; ?>" 
                       placeholder="Email Address">
                <?php if (isset($errors['email'])): ?>
                    <div class="error-message email-error">
                        <?php echo $errors['email']; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <input type="password" 
                       name="password" 
                       class="form-input <?php echo isset($errors['password']) ? 'error' : ''; ?>" 
                       placeholder="Password">
                <?php if (isset($errors['password'])): ?>
                    <div class="error-message password-error">
                        <?php echo $errors['password']; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="login-btn">LOGIN</button>
        </form>
        
        <div class="forgot-password">
            <a href="reset-password.php">Forgot Password?</a>
        </div>
    </div>
</body>
</html>