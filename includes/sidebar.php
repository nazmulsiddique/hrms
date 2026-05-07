<div class="sidebar">

    <h4 class="text-center py-3 border-bottom text-white">---</h4>

    <a href="<?php echo BASE_URL; ?>">
        <i class="fa fa-home"></i> Dashboard
    </a>

    <!-- Employee Dropdown -->
    <a data-bs-toggle="collapse" href="#empMenu" role="button">
        <i class="fa fa-users"></i> Employees
        <i class="fa fa-angle-down float-end"></i>
    </a>

    <div class="collapse ps-3" id="empMenu">
        <a href="<?php echo BASE_URL; ?>employee/employee.php">
            <i class="fa fa-plus"></i> Add Employee
        </a>
        <a href="<?php echo BASE_URL; ?>employee/index.php">
            <i class="fa fa-list"></i> Employee List
        </a>
    </div>

    <!-- Attendance Dropdown -->
    <a data-bs-toggle="collapse" href="#attMenu" role="button">
        <i class="fa fa-clock"></i> Attendance
        <i class="fa fa-angle-down float-end"></i>
    </a>

    <div class="collapse ps-3" id="attMenu">
        <a href="<?php echo BASE_URL; ?>attendance/index.php">
            <i class="fa fa-check"></i> Mark Attendance
        </a>
        <a href="<?php echo BASE_URL; ?>attendance/attendance.php">
            <i class="fa fa-chart-bar"></i> Report
        </a>
    </div>

    <!-- Leave Dropdown -->
    <a data-bs-toggle="collapse" href="#leaveMenu" role="button">
        <i class="fa fa-calendar"></i> Leave
        <i class="fa fa-angle-down float-end"></i>
    </a>

    <div class="collapse ps-3" id="leaveMenu">
        <a href="<?php echo BASE_URL; ?>leave/index.php">
            <i class="fa fa-plus"></i> Apply Leave
        </a>
        <a href="<?php echo BASE_URL; ?>leave/leave.php">
            <i class="fa fa-list"></i> Leave List
        </a>
    </div>

    <!-- Settings  Dropdown -->
    <a data-bs-toggle="collapse" href="#settingsMenu" role="button">
        <i class="fa fa-cog"></i> Settings
        <i class="fa fa-angle-down float-end"></i>
    </a>
    <div class="collapse ps-3" id="settingsMenu">
        <a href="<?php echo BASE_URL; ?>settings/department.php">
            <i class="fa fa-building"></i> Departments
        </a>
        <a href="<?php echo BASE_URL; ?>settings/designation.php">
            <i class="fa fa-id-badge"></i> Designations
        </a>
        <a href="<?php echo BASE_URL; ?>settings/shift.php">
            <i class="fa fa-clock"></i> Shifts
        </a>
    </div>

       <!-- ACCOUNT   Dropdown -->
    <a data-bs-toggle="collapse" href="#accountMenu" role="button">
        <i class="fa fa-user"></i> My Profile
        <i class="fa fa-angle-down float-end"></i>
    </a>
    <div class="collapse ps-3" id="accountMenu">
        <a href="<?php echo BASE_URL; ?>profile.php">
            <i class="fa fa-user"></i> My Profile
        </a>
        <a href="<?php echo BASE_URL; ?>change-password.php">
            <i class="fa fa-key"></i> Change Password
        </a>
        <a href="<?php echo BASE_URL; ?>logout.php"
       onclick="return confirm('Are you sure you want to logout?')"
       class="text-danger">
            <i class="fa fa-sign-out-alt"></i> Logout
        </a>
    </div>

</div>