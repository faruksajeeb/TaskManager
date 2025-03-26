<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

require_once "classes/User.php";
$user = new User();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    if ($user->login($username, $password)) {
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid credentials!";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="container mt-5 pt-5">
        <div class="row mt-5 pt-5">
            <div
                class="col-lg-4 col-md-6 col-sm-12 offset-lg-4 offset-md-3 offset-sm-0 mt-5 pt-5 rounded p-4 login-form">

                <h1 class="text-center py-3">Login</h1>
                <?php if (isset($error))
                    echo "<p class='text-danger'>$error</p>";
                ?>
                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text" id="basic-addon1">@</span>
                            <input type="text" name="username" placeholder="Username" required
                                class="form-control form-control-lg" id="username" aria-describedby="basic-addon1">
                            <div class="invalid-feedback">
                                Please enter your username.
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text" id="basic-addon2">🔒</span>
                            <input type="password" name="password" placeholder="Password" required
                                class="form-control form-control-lg" id="password" aria-describedby="basic-addon2">
                            <span class="input-group-text password-toggle" id="password-toggle">
                                <i class="fa fa-eye"></i>
                            </span>
                            <div class="invalid-feedback">
                                Please enter your password.
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg form-control">Login</button>
                </form>
                <div class="text-center mt-3">
                    Don't have an account? <a href="register.php">Register</a>
                </div>
            </div>
        </div>

    </div>
    <script>
    (function() {
        'use strict'

        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll('.needs-validation')

        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }

                    form.classList.add('was-validated')
                }, false)
            })
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('password-toggle');

        passwordToggle.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.innerHTML = '<i class="fa fa-eye-slash"></i>';
            } else {
                passwordInput.type = 'password';
                this.innerHTML = '<i class="fa fa-eye"></i>';
            }
        });
    })()
    </script>
</body>

</html>