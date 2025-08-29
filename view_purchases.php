<!DOCTYPE html>
<html lang="en">
<?php include_once 'includes/head.php';
// Get filters from GET request
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Build the SQL query for purchase
$sql = "SELECT * FROM purchase WHERE 1=1";

if (empty($startDate) && empty($endDate)) {
  $today = date('Y-m-d');
  $sql .= " AND DATE(`timestamp`) = '{$today}'";
} elseif (!empty($startDate) && !empty($endDate)) {
  $sql .= " AND `timestamp` BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'";
} elseif (!empty($startDate)) {
  $sql .= " AND `timestamp` >= '{$startDate} 00:00:00'";
} elseif (!empty($endDate)) {
  $sql .= " AND `timestamp` <= '{$endDate} 23:59:59'";
}
$sql .= " ORDER BY purchase_id DESC";
$q = mysqli_query($dbc, $sql);
?>

<style>
  .item-list {
    padding: 5px 0;
  }

  .item-row {
    display: flex;
    align-items: center;
    margin-bottom: 5px;
    padding: 2px 0;
    border-bottom: 1px dashed #ddd;
  }

  .item-code,
  .item-name,
  .item-qty,
  .item-total {
    display: inline-block;
    padding: 2px 5px;
    text-align: left;
  }

  .item-name strong {
    color: #333;
  }

  .item-total strong {
    color: #28a745;
  }
</style>

<body class="horizontal light">
  <div class="wrapper">
    <?php include_once 'includes/header.php'; ?>

    <main role="main" class="main-content">
      <div class="container-fluid">
        <div class="card">
          <div class="card-header card-bg text-center">
            <h4 class="card-text"><b>Purchase List</b></h4>
          </div>

          <div class="card-body">
            <div class="">
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


              <?php if (!empty($startDate) || !empty($endDate)): ?>
                <p><strong>Showing results from:</strong>
                  <?= !empty($startDate) ? date('d M, Y h:i A', strtotime($startDate)) : 'Beginning' ?> to
                  <?= !empty($endDate) ? date('d M, Y h:i A', strtotime($endDate)) : 'Now' ?>
                </p>
              <?php endif; ?>

            </div>

            <hr>
            <table class="table dataTable" id="view_purchase_tb">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Supplier Detail</th>
                  <th>Date</th>
                  <th>Purchase Type</th>
                  <th>Items</th>
                  <th>Amount</th>
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
                    <td><?= $r['purchase_id'] ?></td>
                    <td><?= !empty($r['client_name']) ? ucwords($r['client_name']) : 'Not Assigned' ?><br>
                      <small
                        class="text-muted"><?= !empty($r['client_contact']) ? $r['client_contact'] : 'Not Assigned' ?></small>
                    </td>
                    <td>
                      <?= date('h:i A', strtotime($r['timestamp'])) ?><br>
                      <small class="text-muted"><?= date('Y-m-d', strtotime($r['timestamp'])) ?></small>
                    </td>

                    <td><?= ucwords(str_replace('_', ' ', $r['payment_type'])) ?></td>
                    <td>
                      <?php
                      $sql_item = "SELECT pi.*, p.product_name, p.product_code FROM purchase_item pi 
                                     JOIN product p ON pi.product_id = p.product_id 
                                     WHERE pi.purchase_id = {$r['purchase_id']}
                                     ORDER BY pi.purchase_item_id DESC";

                      $item = mysqli_query($dbc, $sql_item);
                      if (mysqli_num_rows($item) > 0) {
                        echo '<div class="item-list">';
                        while ($i = mysqli_fetch_assoc($item)) {
                          $code = strtoupper($i['product_code']);
                          $name = ucwords($i['product_name']);
                          $qty = $i['quantity'];
                          $rate = $i['rate'];
                          $total = $qty * $rate;
                          echo "
                                    <div class='item-row'>
                                      <span class='item-code' style='width: 120px;'>{$code}</span>
                                      <span class='item-name' style='width: 200px;'><strong>{$name}</strong></span>
                                      <span class='item-qty' style='width: 100px;'>{$qty} × {$rate}</span>
                                      <span class='item-total' style='width: 60px;'><strong>{$total}</strong></span>
                                    </div>
                                  ";
                        }
                        echo '</div>';
                      } else {
                        echo "No items";
                      }
                      ?>
                    </td>
                    <td><?= $r['grand_total'] ?></td>
                    <td class="d-flex">
                      <?php if (@$userPrivileges['nav_edit'] == 1 || $fetchedUserRole == "admin"): ?>
                        <?php if ($r['payment_type'] == "cash_purchase"): ?>
                          <form action="cash_purchase.php" method="POST" class="mr-1 mb-1">
                            <input type="hidden" name="edit_purchase_id" value="<?= base64_encode($r['purchase_id']) ?>">
                            <button type="submit" class="btn btn-admin btn-sm">Edit</button>
                          </form>
                        <?php endif; ?>

                        <?php if ($r['payment_type'] == "credit_purchase"): ?>
                          <form action="credit_purchase.php" method="POST" class="mr-1 mb-1">
                            <input type="hidden" name="edit_purchase_id" value="<?= base64_encode($r['purchase_id']) ?>">
                            <button type="submit" class="btn btn-admin btn-sm">Edit</button>
                          </form>
                        <?php endif; ?>
                      <?php endif; ?>

                      <?php if (@$userPrivileges['nav_delete'] == 1 || $fetchedUserRole == "admin"): ?>
                        <a href="#"
                          onclick="deleteAlert('<?= $r['purchase_id'] ?>','purchase','purchase_id','view_purchase_tb')"
                          class="btn btn-danger btn-sm mr-1 mb-1">Delete</a>
                      <?php endif; ?>

                      <a target="_blank" href="print_order.php?id=<?= $r['purchase_id'] ?>&type=purchase"
                        class="btn btn-admin2 btn-sm mb-1">Print</a>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>

      </div> <!-- .card -->
  </div> <!-- .container-fluid -->
  </main> <!-- main -->
  </div> <!-- .wrapper -->

  <?php include_once 'includes/foot.php'; ?>
</body>

</html>