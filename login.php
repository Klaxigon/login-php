<?php
include("./conf.php");
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);

session_name('USER');
session_start();

// CSRF-Token generieren und speichern
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        isset($_POST['username']) && !empty($_POST['username']) &&
        isset($_POST['password']) && !empty($_POST['password']) &&
        isset($_POST['token']) && hash_equals($_SESSION['token'], $_POST['token']) // CSRF-Schutz
    ) {
        // Benutzereingaben bereinigen
        $myusername = htmlspecialchars($_POST['username']);
        $mypassword = $_POST['password'];

        // SQL-Abfrage mit Prepared Statements
        $stmt = $conn->prepare("SELECT uid, username, pswd, name, rang FROM users WHERE username = ?");
        $stmt->bind_param("s", $myusername);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        // Prüfung und Status-Logging basierend auf den Login-Details
        if ($row && password_verify($mypassword, $row['pswd'])) {
            if ($row['rang'] > 0) {
                // Erfolgreicher Login
                session_regenerate_id(true);
                $_SESSION['user_id'] = $row['uid'];
                $_SESSION['login_user'] = $row['username'];
                $_SESSION['user_name'] = $row['name'];
                $_SESSION['user_status'] = $row['rang'];
                
                // Login erfolgreich loggen
                $log_stmt = $conn->prepare("INSERT INTO loginlog (username, loginstat) VALUES (?, ?)");
                $log_stat = "1";
                $log_stmt->bind_param("ss", $myusername, $log_stat);
                $log_stmt->execute();
                $log_stmt->close();
                
                header("location: index.php");
                exit;
            } else {
                // Account deaktiviert loggen
                $log_stmt = $conn->prepare("INSERT INTO loginlog (username, loginstat) VALUES (?, ?)");
                $log_stat = "2";
                $log_stmt->bind_param("ss", $myusername, $log_stat);
                $log_stmt->execute();
                $log_stmt->close();
                
                header('Location:logout.php?e=f2');
                exit;
            }
        } else {
            // Falsches Passwort loggen
            $log_stmt = $conn->prepare("INSERT INTO loginlog (username, loginstat) VALUES (?, ?)");
            $log_stat = "3";
            $log_stmt->bind_param("ss", $myusername, $log_stat);
            $log_stmt->execute();
            $log_stmt->close();
            
            header('Location:logout.php?e=f3');
            exit;
        }
        
        // Schließen der Datenbankverbindung
        $stmt->close();
        $conn->close();
        
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
        echo '<div class="error">Your IP address ('.$ip.') has been reported !!</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css">
    <title>INFOLITI. Login</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            background-color: #8d8d8d;
            color: white;
        }
        label {
            font-weight: bold;
            width: 100px;
            font-size: 14px;
            float: left;
        }
        .box {
            border: 1px solid #8d8d8d;
            width: 440px;
            height: 30px;
            font-size: 20px;   
        }
        .loginbox {
            background-color: #C7AE6A;
            width: 500px;
            border: 1px solid transparent;
            box-shadow: 0px 0px 1000px 200px white;
            margin: 30px auto;
            padding: 20px;
            border-radius: 8px;
        }
        .submitbtn {
            background-color: transparent;
            border: none;
            text-align: center;
            font-size: 28px;
            cursor: pointer;
            color:white;
        }
        .theupicblock {
            text-align: center;
            width: 100%;
            position: fixed;
            bottom: 0;
        }
        .error { color: #FF0000; }
        .ok { color: green; }
    </style>
</head>
<body>
    <div class="loginbox">
        <div style="background-color: #333333; color: #FFFFFF; padding: 3px;"><b>INFOLITI. Login</b></div>
        <div style="margin: 30px">
            <form action="" method="post">
                <label for="username">User:</label><br>
                <input type="text" name="username" id="username" class="box" required><br><br>
                <label for="password">Password:</label><br>
                <input type="password" name="password" id="password" class="box" required><br><br>
                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                <input class="submitbtn" type="submit" value="Login"><br>
            </form>
            <?php
            if (isset($_GET['e'])) {
                $e = htmlspecialchars($_GET["e"]);
                switch ($e) {
                    case "f1":
                        echo '<div class="ok">! Erfolgreich abgemeldet !</div><br>';
                        break;
                    case "f2":
                        echo '<div class="error">! Account Deaktiviert !</div><br>';
                        break;
                    case "f3":
                        echo '<div class="error">! Username / Passwort falsch !</div><br>';
                        break;
                    case "f4":
                        echo '<div class="error">! Zeit abgelaufen... !</div><br>';
                        break;
                    case "f5":
                        echo '<div class="error">! ACCOUNT WURDE DEAKTIVIERT !</div><br>';
                        break;
                }
            }
            ?>
            <div style="font-size: 11px; color: #cc0000; margin-top: 10px"></div>
            <a href=".\"> Ohne Anmeldung fortfahren</a>
        </div>
    </div>
    <div class="theupicblock"></div>
</body>
</html>
