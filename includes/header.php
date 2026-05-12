<?php
require_once(__DIR__ . '/../config/config.php');
require_once(BASE_PATH . '/config/db.php');
$hideLayout = $hideLayout ?? false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRMS Dashboard</title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            overflow-x: hidden;
        }

        /*
        ==========================================
        SIDEBAR (DESKTOP)
        ==========================================
        */
        .sidebar {
            width: 250px;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
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

        /*
        ==========================================
        CONTENT AREA
        ==========================================
        */
        .content {
            margin-left: 250px;
            padding: 20px;
        }

        /*
        ==========================================
        MOBILE SIDEBAR (OFFCANVAS)
        ==========================================
        */
        @media (max-width: 991.98px) {
            .sidebar {
                position: static;
                width: 100%;
                min-height: auto;
            }

            .content {
                margin-left: 0;
                padding: 15px;
            }
        }

        /*
        ==========================================
        MOBILE TOGGLE BUTTON
        ==========================================
        */
        .mobile-menu-btn {
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 1060;
        }

        @media (min-width: 992px) {
            .mobile-menu-btn {
                display: none;
            }
        }
    </style>
</head>

<body>

<?php if (!$hideLayout): ?>

    <!-- MOBILE MENU BUTTON -->
    <button class="btn btn-dark mobile-menu-btn d-lg-none"
            type="button"
            data-bs-toggle="offcanvas"
            data-bs-target="#mobileSidebar"
            aria-controls="mobileSidebar">
        <i class="fas fa-bars"></i>
    </button>

    <!-- DESKTOP SIDEBAR -->
    <div class="d-none d-lg-block">
        <?php include(BASE_PATH . '/includes/sidebar.php'); ?>
    </div>

    <!-- MOBILE SIDEBAR -->
    <div class="offcanvas offcanvas-start bg-dark text-white"
         tabindex="-1"
         id="mobileSidebar"
         aria-labelledby="mobileSidebarLabel">

        <div class="offcanvas-header border-bottom border-secondary">
            <h5 class="offcanvas-title ps-5 ps-md-0" id="mobileSidebarLabel">
                HRMS Menu
            </h5>

            <button type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="offcanvas"
                    aria-label="Close">
            </button>
        </div>

        <div class="offcanvas-body p-0">
            <?php include(BASE_PATH . '/includes/sidebar.php'); ?>
        </div>
    </div>

    

    <!-- MAIN CONTENT -->
    <div class="content">
    <!-- NAVBAR -->
    <?php include(BASE_PATH . '/includes/navbar.php'); ?>
<?php else: ?>

    <div class="full-content">

<?php endif; ?>