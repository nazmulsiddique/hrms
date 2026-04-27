<?php 
require_once(__DIR__ . '/config/config.php');
require_once(BASE_PATH . '/config/db.php');
require_once(BASE_PATH . '/includes/auth.php');
include("includes/header.php"); 
?>

<h3 class="mb-4">Dashboard</h3>

<div class="row">

<?php
$total_emp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM employees"));
?>

<!-- Card 1 -->
<div class="col-md-4">
    <div class="card shadow border-0">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h6 class="text-muted">Total Employees</h6>
                <h3><?php echo $total_emp['total']; ?></h3>
            </div>
            <i class="fa fa-users fa-2x text-primary"></i>
        </div>
    </div>
</div>

<!-- Card 2 -->
<div class="col-md-4">
    <div class="card shadow border-0">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h6 class="text-muted">Today Attendance</h6>
                <h3>
                    <?php
                    // $today = date('Y-m-d');
                    // $att = mysqli_fetch_assoc(mysqli_query($conn,
                    //     "SELECT COUNT(*) as total FROM attendance WHERE date='$today'"
                    // ));
                    // echo $att['total'];
                    ?>
                </h3>
            </div>
            <i class="fa fa-clock fa-2x text-success"></i>
        </div>
    </div>
</div>

<!-- Card 3 -->
<div class="col-md-4">
    <div class="card shadow border-0">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h6 class="text-muted">Absent</h6>
                <h3>
                    <?php
                    // $absent = $total_emp['total'] - $att['total'];
                    // echo $absent;
                    ?>
                </h3>
            </div>
            <i class="fa fa-user-times fa-2x text-danger"></i>
        </div>
    </div>
</div>

</div>

<?php include("includes/footer.php"); ?>