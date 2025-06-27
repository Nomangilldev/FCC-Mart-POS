<!DOCTYPE html>
<html lang="en">
<?php

include_once 'includes/head.php';

// Get filters from GET request
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate   = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Build the SQL query for returns
$sql = "SELECT * FROM returns WHERE payment_type = 'cash_in_hand'";

if (!empty($startDate) && !empty($endDate)) {
    $sql .= " AND `timestamp` BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'";
} elseif (!empty($startDate)) {
    $sql .= " AND `timestamp` >= '{$startDate} 00:00:00'";
} elseif (!empty($endDate)) {
    $sql .= " AND `timestamp` <= '{$endDate} 23:59:59'";
}

$q = mysqli_query($dbc, $sql);
?>
<style>
    .form-inline .form-group {
        margin-right: 15px;
    }

    .form-label {
        font-weight: 500;
        margin-bottom: 0.25rem;
        display: block;
    }
</style>

<body class="horizontal light">
    <div class="wrapper">
        <?php include_once 'includes/header.php'; ?>
        <main role="main" class="main-content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header card-bg text-center">
                        <div class="row">
                            <div class="col-12 mx-auto h4">
                                <b class="text-center card-text">Return List</b>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="container-fluid pl-0 mt-4">
                            <form method="GET" class="form-row align-items-end mb-3">
                                <div class="form-group col-12 col-md-3">
                                    <label for="startDate" class="form-label">Start Date & Time</label>
                                    <input type="datetime-local" class="form-control" name="start_date" id="startDate"
                                        value="<?= !empty($startDate) ? date('Y-m-d\TH:i', strtotime($startDate)) : '' ?>">
                                </div>

                                <div class="form-group col-12 col-md-3">
                                    <label for="endDate" class="form-label">End Date & Time</label>
                                    <input type="datetime-local" class="form-control" name="end_date" id="endDate"
                                        value="<?= !empty($endDate) ? date('Y-m-d\TH:i', strtotime($endDate)) : '' ?>">
                                </div>

                                <div class="form-group col-12 col-md-2">
                                    <button type="submit" class="btn btn-primary btn-block">Apply Filter</button>
                                </div>
                            </form>


                            <?php if (!empty($startDate) || !empty($endDate)) : ?>
                                <p><strong>Showing results from:</strong>
                                    <?= !empty($startDate) ? date('d M, Y h:i A', strtotime($startDate)) : 'Beginning' ?> to
                                    <?= !empty($endDate) ? date('d M, Y h:i A', strtotime($endDate)) : 'Now' ?>
                                </p>
                            <?php endif; ?>

                        </div>

                        <hr>
                        <table class="table dataTable" id="view_returns_tb">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Customer Name</th>
                                    <th>Customer Contact</th>
                                    <th>Return Date</th>
                                    <th>Amount</th>
                                    <th>Return Type</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $c = 0;
                                while ($r = mysqli_fetch_assoc($q)) {
                                    $c++;
                                ?>
                                    <tr>
                                        <td><?= $r['return_id'] ?></td>
                                        <td><?= !empty($r['client_name']) ? ucwords($r['client_name']) : 'Not Assigned' ?></td>
                                        <td><?= !empty($r['client_contact']) ? $r['client_contact'] : 'Not Assigned' ?></td>
                                        <td><?= date('Y-m-d h:i A', strtotime($r['timestamp'])) ?></td>
                                        <td><?= $r['grand_total'] ?></td>
                                        <td><?= ucwords(str_replace('_', ' ', $r['payment_type'])) ?></td>
                                        <td>
                                            <?php if ((@$userPrivileges['nav_edit'] == 1 || $fetchedUserRole == "admin") && $r['payment_type'] == "cash_in_hand"): ?>
                                                <!-- <form action="return_form.php" method="POST" style="display:inline-block;">
                                                    <input type="hidden" name="edit_return_id" value="<?= base64_encode($r['return_id']) ?>">
                                                    <button type="submit" class="btn btn-admin btn-sm m-1">Edit</button>
                                                </form> -->
                                            <?php endif; ?>

                                            <?php if (@$userPrivileges['nav_delete'] == 1 || $fetchedUserRole == "admin"): ?>
                                                <a href="#" onclick="deleteAlert('<?= $r['return_id'] ?>','returns','return_id','view_returns_tb')" class="btn btn-danger btn-sm m-1">Delete</a>
                                            <?php endif; ?>

                                            <!-- <button class="btn btn-info btn-sm m-1" onclick="printOrder(<?= $r['return_id'] ?>)">Print</button> -->
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
<?php include_once 'includes/foot.php'; ?>