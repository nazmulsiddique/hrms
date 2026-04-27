<?php
require_once(__DIR__ . '/../config/config.php');
require_once(BASE_PATH . '/config/db.php');
require_once(BASE_PATH . '/includes/auth.php');
date_default_timezone_set('Asia/Dhaka');
$employee_id = $_SESSION['employee_id'];
$message = '';

/*
=====================================================
ATTENDANCE SAVE PROCESS (SAME PAGE)
=====================================================
*/
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $imageData = $_POST['image'];
    $attendance_type = $_POST['attendance_type'];

    $attendance_date = date('Y-m-d');
    $current_time = date('Y-m-d H:i:s');

    // Upload folder
    $folderPath = "uploads/attendance/";

    if (!file_exists($folderPath)) {
        mkdir($folderPath, 0777, true);
    }

    // Image processing
    $image_parts = explode(";base64,", $imageData);
    $image_type_aux = explode("image/", $image_parts[0]);
    $image_type = $image_type_aux[1];

    $image_base64 = base64_decode($image_parts[1]);

    $fileName = $employee_id . "_" . $attendance_type . "_" . time() . ".png";
    $file = $folderPath . $fileName;

    file_put_contents($file, $image_base64);

    /*
    ==========================
    IN ATTENDANCE
    ==========================
    */
    if ($attendance_type == 'in') {

        $check = $conn->prepare("
            SELECT id 
            FROM attendance 
            WHERE employee_id=? 
            AND attendance_date=?
        ");

        $check->bind_param("ss", $employee_id, $attendance_date);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows == 0) {

            $stmt = $conn->prepare("
                INSERT INTO attendance
                (
                    employee_id,
                    in_time,
                    in_latitude,
                    in_longitude,
                    in_image,
                    attendance_date
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "ssssss",
                $employee_id,
                $current_time,
                $latitude,
                $longitude,
                $file,
                $attendance_date
            );

            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>IN Attendance Successful!</div>";
            }

        } else {
            $message = "<div class='alert alert-warning'>Already IN marked today!</div>";
        }
    }

    /*
    ==========================
    OUT ATTENDANCE
    ==========================
    */
    if ($attendance_type == 'out') {

        $check = $conn->prepare("
            SELECT id, out_time
            FROM attendance
            WHERE employee_id=?
            AND attendance_date=?
        ");

        $check->bind_param("ss", $employee_id, $attendance_date);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $row = $result->fetch_assoc();

            if (empty($row['out_time'])) {

                $stmt = $conn->prepare("
                    UPDATE attendance
                    SET
                        out_time=?,
                        out_latitude=?,
                        out_longitude=?,
                        out_image=?
                    WHERE id=?
                ");

                $stmt->bind_param(
                    "ssssi",
                    $current_time,
                    $latitude,
                    $longitude,
                    $file,
                    $row['id']
                );

                if ($stmt->execute()) {
                    $message = "<div class='alert alert-success'>OUT Attendance Successful!</div>";
                }

            } else {
                $message = "<div class='alert alert-warning'>Already OUT marked today!</div>";
            }

        } else {
            $message = "<div class='alert alert-danger'>Please mark IN first!</div>";
        }
    }
}

include(BASE_PATH . '/includes/header.php');
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-7">

            <div class="card shadow-lg">

                <div class="card-header bg-primary text-white text-center">
                    <h3>Employee Attendance System</h3>
                </div>

                <div class="card-body text-center">

                    <?php echo $message; ?>

                    <h5 class="mb-3">
                        Employee ID: <strong><?php echo $employee_id; ?></strong>
                    </h5>

                    <!-- Camera Preview -->
                    <video id="video" width="320" height="240" autoplay playsinline class="border rounded"></video>

                    <canvas id="canvas" width="320" height="240" style="display:none;"></canvas>

                    <!-- Attendance Form -->
                    <form method="POST" id="attendanceForm">

                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">
                        <input type="hidden" name="image" id="image">
                        <input type="hidden" name="attendance_type" id="attendance_type">

                        <div class="d-flex justify-content-center gap-3 mt-4">

                            <button type="button"
                                    class="btn btn-success px-5"
                                    onclick="submitAttendance('in')">
                                IN
                            </button>

                            <button type="button"
                                    class="btn btn-danger px-5"
                                    onclick="submitAttendance('out')">
                                OUT
                            </button>

                        </div>

                    </form>

                    <p id="status" class="mt-3 text-primary fw-bold"></p>

                </div>

            </div>

        </div>
    </div>
</div>

<script>
const video = document.getElementById('video');
const canvas = document.getElementById('canvas');

/*
==========================================
FRONT CAMERA ONLY
==========================================
*/
navigator.mediaDevices.getUserMedia({
    video: {
        facingMode: "user"
    },
    audio: false
})
.then(stream => {
    video.srcObject = stream;
})
.catch(error => {
    alert("Camera access is required!");
});

/*
==========================================
SUBMIT ATTENDANCE
==========================================
*/
function submitAttendance(type) {

    document.getElementById('attendance_type').value = type;
    document.getElementById('status').innerText = "Fetching location...";

    navigator.geolocation.getCurrentPosition(
        function(position) {

            document.getElementById('latitude').value = position.coords.latitude;
            document.getElementById('longitude').value = position.coords.longitude;

            // Capture image
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, 320, 240);

            const imageData = canvas.toDataURL('image/png');
            document.getElementById('image').value = imageData;

            document.getElementById('status').innerText = "Submitting attendance...";

            document.getElementById('attendanceForm').submit();
        },
        function(error) {
            alert("Location permission required!");
        }
    );
}
</script>

<?php include(BASE_PATH . '/includes/footer.php'); ?>