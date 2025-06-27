<!DOCTYPE html>
<html lang="en">
<?php include_once 'includes/head.php'; ?>

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
            <div class="table-responsive">
              <table class="table table-bordered table-striped dataTable" id="view_purchase_tb">
                <thead class="thead-dark">
                  <tr>
                    <th>#</th>
                    <th>Supplier Name</th>
                    <th>Supplier Contact</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Purchase Type</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $q = mysqli_query($dbc, "SELECT * FROM purchase");
                  $c = 0;
                  while ($r = mysqli_fetch_assoc($q)) {
                    $c++;
                  ?>
                    <tr>
                      <td><?= $r['purchase_id'] ?></td>
                      <td><?= ucwords($r['client_name']) ?></td>
                      <td><?= $r['client_contact'] ?></td>
                      <td><?= $r['purchase_date'] ?></td>
                      <td><?= $r['grand_total'] ?></td>
                      <td><?= ucwords(str_replace('_', ' ', $r['payment_type'])) ?></td>
                      <td class="d-flex flex-wrap">
                        <?php if (@$userPrivileges['nav_edit'] == 1 || $fetchedUserRole == "admin") : ?>
                          <?php if ($r['payment_type'] == "cash_purchase") : ?>
                            <form action="cash_purchase.php" method="POST" class="mr-1 mb-1">
                              <input type="hidden" name="edit_purchase_id" value="<?= base64_encode($r['purchase_id']) ?>">
                              <button type="submit" class="btn btn-admin btn-sm">Edit</button>
                            </form>
                          <?php endif; ?>

                          <?php if ($r['payment_type'] == "credit_purchase") : ?>
                            <form action="credit_purchase.php" method="POST" class="mr-1 mb-1">
                              <input type="hidden" name="edit_purchase_id" value="<?= base64_encode($r['purchase_id']) ?>">
                              <button type="submit" class="btn btn-admin btn-sm">Edit</button>
                            </form>
                          <?php endif; ?>
                        <?php endif; ?>

                        <?php if (@$userPrivileges['nav_delete'] == 1 || $fetchedUserRole == "admin") : ?>
                          <a href="#" onclick="deleteAlert('<?= $r['purchase_id'] ?>','purchase','purchase_id','view_purchase_tb')" class="btn btn-danger btn-sm mr-1 mb-1">Delete</a>
                        <?php endif; ?>

                        <a target="_blank" href="print_order.php?id=<?= $r['purchase_id'] ?>&type=purchase" class="btn btn-admin2 btn-sm mb-1">Print</a>
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