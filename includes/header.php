<?php
require_once(__DIR__ . '/../config/config.php');
require_once(BASE_PATH . '/config/db.php');
$hideLayout = $hideLayout ?? false;
?>
<!DOCTYPE html>
<html>
<head>
    <title>HRMS Dashboard</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            overflow-x: hidden;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            background: #343a40;
            color: #fff;
        }
        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            padding: 12px 20px;
            display: block;
        }
        .sidebar a:hover {
            background: #495057;
            color: #fff;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
        }
    </style>
</head>

<body>

<?php if (!$hideLayout): ?>
    <?php include(BASE_PATH . '/includes/sidebar.php'); ?>
    <?php include(BASE_PATH . '/includes/navbar.php'); ?>

    <div class="content">
<?php else: ?>

    <div class="full-content">
<?php endif; ?>