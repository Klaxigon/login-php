<?php

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = "admin";
    $password = "password123";

    $inputUsername = $_POST['username'] ?? '';
    $inputPassword = $_POST['password'] ?? '';

    // Anmeldedaten validieren
    if ($inputUsername === $username && $inputPassword === $password) {
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Ungültiger Benutzername oder Passwort.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" type="text/css" href="styles.css" />
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-2"></div>
            <div class="col-lg-6 col-md-8 login-box">
                <div class="col-lg-12 login-key">
                    <i class="fa fa-key" aria-hidden="true"></i>
                </div>
                <div class="col-lg-12 login-title">
                    ADMIN PANEL
                </div>

                <div class="col-lg-12 login-form">
                    <div class="col-lg-12 login-form">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label class="form-control-label">USERNAME</label>
                                <input type="text" class="form-control" name="username" required>
                            </div>
                            <div class="form-group">
                                <label class="form-control-label">PASSWORD</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>

                            <div class="col-lg-12 loginbttm">
                                <div class="col-lg-6 login-btm login-text">
                                    <?php if ($error): ?>
                                        <p style="color: red;"><?php echo $error; ?></p>
                                    <?php endif; ?> # Ersetze mit deinem Bot-Token
                                </div>
                                <div class="col-lg-6 login-btm login-button">
                                    <button type="submit" class="btn btn-outline-primary">LOGIN</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-3 col-md-2"></div>
            </div>
        </div>
</body>
</html>