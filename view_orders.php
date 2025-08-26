<!DOCTYPE html>
<html lang="en">
<?php

include_once 'includes/head.php';
// Get filters from GET request
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Build the SQL query
$sql = "SELECT * FROM orders WHERE payment_type='cash_in_hand'";

if (!empty($startDate) && !empty($endDate)) {
  $sql .= " AND `timestamp` BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'";
} elseif (!empty($startDate)) {
  $sql .= " AND `timestamp` >= '{$startDate} 00:00:00'";
} elseif (!empty($endDate)) {
  $sql .= " AND `timestamp` <= '{$endDate} 23:59:59'";
}

$sql .= " ORDER BY order_id DESC";
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

  .item-code, .item-name, .item-qty, .item-total {
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
            <div class="row">
              <div class="col-12 mx-auto h4">
                <b class="text-center card-text">Orders List</b>
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


              <?php if (!empty($startDate) || !empty($endDate)): ?>
                <p><strong>Showing results from:</strong>
                  <?= !empty($startDate) ? date('d M, Y h:i A', strtotime($startDate)) : 'Beginning' ?> to
                  <?= !empty($endDate) ? date('d M, Y h:i A', strtotime($endDate)) : 'Now' ?>
                </p>
              <?php endif; ?>

            </div>

            <hr>
            <table class="table dataTable" id="view_orders_tb">
  <thead>
    <tr>
      <th>#</th>
      <th>Customer Detail</th>
      <th>Order Date</th>
      <th>Order Type</th>
      <th>Items</th>
      <th>Amount</th>
      <th>Profit</th>
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
        <td><?= $r['order_id'] ?></td>
        <td><?= !empty($r['client_name']) ? ucwords($r['client_name']) : 'Not Assigned' ?><br>
          <small class="text-muted"><?= !empty($r['client_contact']) ? $r['client_contact'] : 'Not Assigned' ?></small>
        </td>
        <td>
          <?= date('h:i A', strtotime($r['timestamp'])) ?><br>
          <small class="text-muted"><?= date('Y-m-d', strtotime($r['timestamp'])) ?></small>
        </td>
        <td><?= ucwords(str_replace('_', ' ', $r['payment_type'])) ?></td>
        <td>
          <?php
          $sql_item = "SELECT oi.*, p.product_name, p.product_code FROM order_item oi 
                       JOIN product p ON oi.product_id = p.product_id 
                       WHERE oi.order_id = {$r['order_id']}
                       ORDER BY oi.order_item_id DESC";

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
                        <span class='item-code' style='width: 150px;'>{$code}</span>
                        <span class='item-name' style='width: 200px;'><strong>{$name}</strong></span>
                        <span class='item-qty' style='width: 80px;'>{$qty} × {$rate}</span>
                        <span class='item-total' style='width: 50px;'><strong>{$total}</strong></span>
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
        <td><?= $r['customer_profit'] ?></td>
        <td>
          <?php
          if (@$get_company['sale_interface'] == "barcode") {
            $cash_sale_url = "cash_salebarcode.php";
            $credit_sale_url = "credit_sale.php";
          } elseif (@$get_company['sale_interface'] == "keyboard") {
            $cash_sale_url = "cash_salegui.php";
            $credit_sale_url = "credit_sale.php";
          } else {
            $cash_sale_url = "cash_sale.php";
            $credit_sale_url = "credit_sale.php";
          }
          ?>

          <?php if ((@$userPrivileges['nav_edit'] == 1 || $fetchedUserRole == "admin") && $r['payment_type'] == "cash_in_hand"): ?>
            <form action="<?= $cash_sale_url ?>" method="POST" style="display:inline-block;">
              <input type="hidden" name="edit_order_id" value="<?= base64_encode($r['order_id']) ?>">
              <button type="submit" class="btn btn-admin btn-sm m-1">Edit</button>
            </form>
          <?php endif; ?>

          <?php if ((@$userPrivileges['nav_edit'] == 1 || $fetchedUserRole == "admin") && $r['payment_type'] == "credit_sale"): ?>
            <form action="<?= $credit_sale_url ?>" method="POST" style="display:inline-block;">
              <input type="hidden" name="edit_order_id" value="<?= base64_encode($r['order_id']) ?>">
              <input type="hidden" name="credit_type" value="<?= $r['credit_sale_type'] ?>">
              <button type="submit" class="btn btn-admin btn-sm m-1">Edit</button>
            </form>
          <?php endif; ?>

          <?php if (@$userPrivileges['nav_delete'] == 1 || $fetchedUserRole == "admin"): ?>
            <a href="#" onclick="deleteAlert('<?= $r['order_id'] ?>','orders','order_id','view_orders_tb')"
              class="btn btn-danger btn-sm m-1">Delete</a>
          <?php endif; ?>

          <button class="btn btn-info btn-sm m-1" onclick="printOrder(<?= $r['order_id'] ?>)">Print</button>
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