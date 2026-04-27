<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_name = $_SESSION['employee_name'] ?? 'Guest';
$user_id = $_SESSION['employee_id'] ?? 'User';
?>

<nav class="navbar navbar-light bg-light shadow-sm" style="margin-left:250px;">
    <div class="container-fluid">

        <span class="navbar-brand">Dashboard</span>

        <div class="d-flex align-items-center gap-2">
            <i class="fa fa-user"></i>
            <span>
                <?php echo htmlspecialchars($user_name); ?>
                (<?php echo htmlspecialchars($user_id); ?>)
            </span>
        </div>

    </div>
</nav>