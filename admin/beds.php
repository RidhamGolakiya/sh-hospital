<?php
session_start();
include "../connection.php";

// Check authentication
if (!isset($_SESSION['user'])) {
    $_SESSION['success'] = false;
    $_SESSION['message'] = "Authentication failed";
    header("location: $appUrl/login.php");
    exit;
} else if (isset($_SESSION["role"]) && $_SESSION["role"] != "admins") {
    setcookie('user', '', time() - 3600, '/');
    $_SESSION['success'] = false;
    $_SESSION['message'] = "You are not authorized to access the admin site.";
    header("location: $appUrl/login.php");
    exit;
}

$pageTitle = "Bed Management";
require_once "../components/header.php";

// Fetch all beds with their status and patient details if assigned
$beds_query = "
    SELECT beds.id, beds.bed_name, beds.status, 
           IFNULL(CONCAT(patients.first_name, ' ', patients.last_name), '') AS patient_name,
           bed_assignments.assign_date, beds.bed_charge
    FROM beds
    LEFT JOIN bed_assignments ON beds.id = bed_assignments.bed_id
    LEFT JOIN patients ON bed_assignments.ipd_patient_department_id = patients.id
";
$beds_result = mysqli_query($connection, $beds_query);
?>

<!-- Body Wrapper -->
<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    <aside class="left-sidebar">
        <div>
            <div class="brand-logo d-flex align-items-center justify-content-between">
                <a href="<?php echo $appUrl; ?>" class="navbar-brand" style="font-size:30px">
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

    <!-- Main wrapper -->
    <div class="body-wrapper">
        <?php require_once "../components/profileHeader.php"; ?>

        <div class="p-5">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Bed Management</h3>
                <a class="btn btn-outline-secondary" href="./dashboard.php">Back</a>
            </div>
            <div class="bed-grid mt-3">
                <?php while ($bed = mysqli_fetch_assoc($beds_result)) : ?>
                    <?php
                    $patient_name = !is_null($bed['patient_name']) ? htmlspecialchars($bed['patient_name']) : '';
                    $assign_date = !is_null($bed['assign_date']) ? htmlspecialchars($bed['assign_date']) : '';
                    $bed_charge = !is_null($bed['bed_charge']) ? htmlspecialchars($bed['bed_charge']) : '';
                    ?>
                    <div class="bed-item <?php echo $bed['status'] === '0' ? 'occupied' : 'available'; ?>" data-patient="<?php echo $patient_name; ?>" data-assign-date="<?php echo $assign_date; ?>" data-bed-charge="<?php echo $bed_charge; ?>" data-bed-id="<?php echo $bed['id']; ?>">
                        <i class="fa fa-bed"></i>
                        <p><?php echo htmlspecialchars($bed['bed_name']); ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <script>
        // Event listeners for bed items
        document.querySelectorAll('.bed-item').forEach(item => {
            item.addEventListener('click', () => {
                const patientName = item.dataset.patient;
                const assignDate = item.dataset.assignDate;
                const bedCharge = item.dataset.bedCharge;

                if (patientName) {
                    // If the bed is occupied, show modal
                    showBedDetails(patientName, assignDate, bedCharge);
                } else {
                    // If the bed is available, redirect to assign-bed page
                    const bedId = item.dataset.bedId;
                    window.location.href = `./assign-bed.php?bed_id=${bedId}`;
                }
            });
        });
    </script>


    <style>
        .bed-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .bed-item {
            width: 120px;
            height: 120px;
            border: 1px solid #ccc;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
        }

        .bed-item.available {
            background-color: #d4edda;
            color: #155724;
        }

        .bed-item.occupied {
            background-color: #f8d7da;
            color: #721c24;
        }

        .bed-item i {
            font-size: 2em;
        }

        .bed-item p {
            margin: 0;
            font-size: 1em;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            padding-top: 100px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fff;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
        }
    </style>
</div>
<script src="./admin.js"></script>
<?php require_once("../components/footer.php"); ?>