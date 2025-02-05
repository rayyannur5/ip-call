<?php
session_start();

if (isset($_SESSION["user"])) {
    if ($_SESSION["user"] == "admin") {
        header("location: ../admin");
    } elseif ($_SESSION["user"] == "user") {
        header("location: /");
    } elseif ($_SESSION["user"] == "teknisi") {
        header("location: ../admin");
    }
}

if (isset($_POST["username"])) {
    require_once "config.php";
    $username = $_POST["username"];
    $password = $_POST["password"];
    $user = queryArray("SELECT * FROM user WHERE username = '$username'");
    if (count($user) != 0) {
        if (password_verify($password, $user[0]["password"])) {
            $_SESSION["user"] = $user[0]["role"];

            if ($_SESSION["user"] == "admin") {
                header("location: ../admin");
            } elseif ($_SESSION["user"] == "user") {
                header("location: /");
            } elseif ($_SESSION["user"] == "teknisi") {
                header("location: ../admin");
            }
        } else {
            $wrong_password = true;
        }
    } else {
        $wrong_username = true;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
</head>
<link rel="stylesheet" href="dist/css/adminlte.css">
<link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
<link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">

<body class="hold-transition login-page">

    <div class="login-box">

        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <a href="" class="h3">NurseCall Login System</a>
            </div>
            <div class="card-body">
                <?php if (isset($wrong_username)) { ?>
                    <div class="alert alert-danger" role="alert">
                        Username yang anda masukkan salah
                    </div>
                <?php } ?>
                <?php if (isset($wrong_password)) { ?>
                    <div class="alert alert-danger" role="alert">
                        Password yang anda masukkan salah
                    </div>
                <?php } ?>
                <form action="" method="post">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Username" name="username">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" placeholder="Password" name="password">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                </form>
            </div>
        </div>
    </div>


    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="dist/js/adminlte.min.js"></script>
</body>

</html>
