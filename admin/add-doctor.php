<?php
session_start();
include "../connection.php";
// Check cookies exists or not
if (!isset($_SESSION['user'])) {
    $_SESSION['success'] = false;
    $_SESSION['message'] = "Authentication failed";
    header("location: $appUrl/login.php");
} else if (isset($_SESSION["role"]) && $_SESSION["role"] != "admins") {
    setcookie('user', '', time() - 3600, '/');
    $_SESSION['success'] = false;
    $_SESSION['message'] = "You are not authorized to access the admin site.";
    header("location: $appUrl/login.php");
    exit;
}
$pageTitle = "Doctors";
require_once "../components/header.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $email = $_POST["email"];
    $phone_no = $_POST["phone_no"];
    $diagnosis_id = $_POST["diagnosis_id"];
    $designation = $_POST["designation"];
    $qualification = $_POST["qualification"];
    $gender = $_POST["gender"];
    $status = isset($_POST["status"]) ? 1 : 0;
    $specialist = $_POST["specialist"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $roles = "d";

    // Insert into doctors table
    $user_query = "INSERT INTO users (email, roles) VALUES ('$email','$roles')";
    $result = mysqli_query($connection, $user_query);

    $stmt = $connection->prepare("INSERT INTO doctors (first_name, last_name, email,phone_no, diagnosis_id, designation, qualification, gender, status, specialist, password) VALUES (?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssisssiss", $first_name, $last_name, $email, $phone_no, $diagnosis_id, $designation, $qualification, $gender, $status, $specialist, $password);
    $stmt->execute();

    // Close the statement
    $stmt->close();

    // Redirect or show a success message
    $_SESSION['success'] = true;
    $_SESSION['message'] = "Doctor added successfully";
    header("location: $appUrl/admin/doctors.php");
    exit();
}


?>

<!--  Body Wrapper -->
<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    <aside class="left-sidebar">
        <div>
            <div class="brand-logo d-flex align-items-center justify-content-between">
                <a href="<?php echo $appUrl; ?>" class="navbar-brand" style="font-size:30px">
                    <!-- <div class="d-flex align-items-center"><img src="../uploads/settings/<?php echo $_SESSION['logo'] ?>" class="img-fluid" alt="logo" width="50" height="50"><span class="mx-2 my-1" style="font-size:20px"><?php echo $_SESSION['site_name'] ?></span></div> -->
                    Hospital
                </a>
                <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
                    <i class="fa fa-times"></i>
                </div>
            </div>
            <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
                <div class="sidebar">
                    <ul id="sideNav">
                    </ul>
                </div>
            </nav>
        </div>
    </aside>

    <!--  Main wrapper -->
    <div class="body-wrapper">

        <!--  Header Start -->
        <?php require_once "../components/profileHeader.php" ?>
        <!--  Header End -->

        <!-- Doctor Table start-->
        <div class="p-5">
            <?php
            if (isset($_SESSION['message']) && isset($_SESSION['success'])) {
                $message = $_SESSION['message'];
                $success = $_SESSION['success'];
                $toastType = $success ? 'success' : 'error';
                echo "<script>
          toastr.options = {
            positionClass: 'toast-top-right',
            timeOut: 2000,
            progressBar: true,
          };
          toastr.$toastType('$message');
        </script>";
                unset($_SESSION['message']);
                unset($_SESSION['success']);
            }
            ?>
            <div class="d-flex justify-content-between align-items-center">
                <h3>New Doctor</h3>
                <a class="btn btn-outline-secondary" href="./doctors.php">Back</a>
            </div>
            <div class="card mt-3">
                <div class="card-body">
                    <form method="POST" action="" accept-charset="UTF-8" id="createDoctorForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-5">
                                    <label for="first_name" class="form-label">First Name:</label>
                                    <span class="required"></span>
                                    <input class="form-control" required="" placeholder="First Name" name="first_name" type="text" id="first_name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-5">
                                    <label for="last_name" class="form-label">Last Name:</label>
                                    <span class="required"></span>
                                    <input class="form-control" required="" placeholder="Last Name" name="last_name" type="text" id="last_name">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-5">
                                    <label for="email" class="form-label">Email:</label>
                                    <span class="required"></span>
                                    <input class="form-control" required="" placeholder="Email" name="email" type="email">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-5">
                                    <label for="email" class="form-label">Phone No.:</label>
                                    <span class="required"></span>
                                    <input class="form-control" required="" placeholder="Phone No." name="phone_no" type="tel">
                                </div>
                            </div>
                            <div class="form-group col-sm-6 mb-5">
                                <label for="diagnosis_name" class="form-label">Doctor Diagnosis:</label>
                                <span class="required"></span>
                                <select class="form-select select2-hidden-accessible" id="doctorsDepartmentId" data-control="select2" required="" name="diagnosis_id" tabindex="-1" aria-hidden="true" data-select2-id="select2-data-doctorsDepartmentId">
                                    <option selected="selected" value="" data-select2-id="select2-data-399-w4d5">Select Diagnosis</option>
                                    <?php
                                    // Assuming $connection is your database connection
                                    $diagnosis_query = "SELECT id, diagnosis_name FROM diagnosis";
                                    $department_result = mysqli_query($connection, $diagnosis_query);

                                    while ($row = mysqli_fetch_assoc($department_result)) {
                                        $id = $row['id'];
                                        $diagnosisName = $row['diagnosis_name'];
                                        echo "<option value=\"$id\">$diagnosisName</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-5">
                                    <label for="designation" class="form-label">Designation:</label>
                                    <span class="required"></span>
                                    <input class="form-control" required="" placeholder="Designation" name="designation" type="text" id="designation">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-5">
                                    <label for="qualification" class="form-label">Qualification:</label>
                                    <span class="required"></span>
                                    <input class="form-control" required="" placeholder="Qualification" name="qualification" type="text" id="qualification">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-5">
                                    <label for="gender" class="form-label">Gender:</label>
                                    <span class="required"></span> &nbsp;<br>
                                    <div class="d-flex align-items-center">
                                        <div class="form-check">
                                            <label class="form-label" for="doctorMale">Male</label>
                                            <input class="form-check-input" id="doctorMale" checked="checked" name="gender" type="radio" value="0">
                                        </div>
                                        <div class="form-check mx-2">
                                            <label class="form-label" for="doctorFemale">Female</label>&nbsp;
                                            <input class="form-check-input" id="doctorFemale" name="gender" type="radio" value="1">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-5">
                                    <label for="status" class="form-label">Status:</label>
                                    <br>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input is-active" name="status" type="checkbox" value="1" tabindex="8" checked="">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-5">
                                    <label for="specialist" class="form-label">Specialist:</label>
                                    <span class="required"></span>
                                    <input class="form-control" required="" placeholder="Specialist" name="specialist" type="text" value="" id="specialist">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-5">
                                    <label for="password" class="form-label">Password:</label>
                                    <span class="required"></span>
                                    <input class="form-control" required="" min="6" max="10" placeholder="Password" name="password" type="password" value="" id="password">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-5">
                                    <label for="password_confirmation" class="form-label">Confirm Password:</label>
                                    <span class="required"></span>
                                    <input class="form-control" required="" min="6" max="10" placeholder="Confirm Password" name="password_confirmation" type="password" value="" id="password_confirmation">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <input class="btn btn-primary me-2" type="submit" value="Save">
                            <a href="./doctors.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="./admin.js"></script>
<?php
require_once("../components/footer.php");
?>