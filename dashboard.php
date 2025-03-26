<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

session_start();
require_once "classes/User.php";
$user = new User();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if (isset($_GET['logout'])) {
    $user->logout();
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Task Manager</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet" href="assets/css/style.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>

<body>

    <?php include('includes/sidebar.php'); ?>
    <div id="content">
        <?php include('includes/header.php'); ?>
        <div class="container-fluid mt-4">
            <?php include('task/manage_task.php'); ?>
        </div>
    </div>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>

    </script>
</body>

</html>