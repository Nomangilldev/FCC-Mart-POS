<!DOCTYPE html>
<html lang="en">
<?php include_once 'includes/head.php';

if (!empty($_REQUEST['edit_purchase_id'])) {
  $fetchPurchase = fetchRecord($dbc, "purchase", "purchase_id", base64_decode($_REQUEST['edit_purchase_id']));
}
?>

<body class="horizontal light  ">
  <div class="wrapper">
    <?php include_once 'includes/header.php'; ?>

    <div class="container-fluid">
      <div class="card">
        <div class="card-header card-bg" align="center">
          <div class="row">
            <div class="col-12 mx-auto h4">
              <b class="text-center card-text">Cash Purchase</b>
              <a href="cash_purchase.php" class="btn btn-admin float-right btn-sm">Add New</a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <form action="php_action/custom_action.php" method="POST" id="sale_order_fm">
            <input type="hidden" name="product_purchase_id" value="<?= @empty($_REQUEST['edit_purchase_id']) ? "" : base64_decode($_REQUEST['edit_purchase_id']) ?>">
            <input type="hidden" name="payment_type" id="payment_type" value="cash_purchase">
            <div class="row form-group">
              <!-- Purchase ID -->
              <div class="col-md-2">
                <label>Purchase ID#</label>
                <?php
                $result = mysqli_query($dbc, "SHOW TABLE STATUS LIKE 'purchase'");
                $data = mysqli_fetch_assoc($result);
                $next_increment = $data['Auto_increment'];
                ?>
                <input type="text" name="next_increment" id="next_increment" value="<?= @empty($_REQUEST['edit_purchase_id']) ? $next_increment : $fetchPurchase['purchase_id'] ?>" readonly class="form-control" placeholder="Auto-generated ID">
              </div>

              <!-- Purchase Date -->
              <div class="col-md-2">
                <label>Purchase Date</label>
                <input type="text" name="purchase_date" id="purchase_date" value="<?= @empty($_REQUEST['edit_purchase_id']) ? date('Y-m-d') : $fetchPurchase['purchase_date'] ?>" readonly class="form-control" placeholder="YYYY-MM-DD">
              </div>

              <!-- Bill Number (New Field) -->
              <div class="col-md-2">
                <label>Bill Number</label>
                <input type="text" name="supplier_bill_number" id="supplier_bill_number" value="<?= @$fetchPurchase['supplier_bill_number'] ?>" class="form-control" placeholder="Enter Bill No.">
              </div>

              <!-- Supplier Selection -->
              <div class="col-md-3">
                <div class="d-flex justify-content-between align-items-center">
                  <label class="mb-0">Select Supplier</label>
                  <a href="customers.php?type=supplier" class="btn btn-admin2 btn-sm">Add Supplier</a>
                </div>
                <div class="input-group mt-1">
                  <select class="form-control" name="cash_purchase_supplier" id="credit_order_client_name" required onchange="getBalance(this.value,'customer_account_exp')" aria-label="Supplier" aria-describedby="basic-addon1">
                    <option value="">Select Supplier</option>
                    <?php
                    $q = mysqli_query($dbc, "
              SELECT * FROM customers 
              WHERE customer_status = 1 
                AND customer_type = 'supplier' 
              ORDER BY customer_name ASC
            ");
                    while ($r = mysqli_fetch_assoc($q)) {
                    ?>
                      <option
                        <?= @($fetchPurchase['customer_account'] == $r['customer_id']) ? "selected" : "" ?>
                        data-id="<?= $r['customer_id'] ?>"
                        data-contact="<?= $r['customer_phone'] ?>"
                        value="<?= $r['customer_name'] ?>">
                        <?= ucwords($r['customer_name']) ?>
                      </option>
                    <?php } ?>
                  </select>

                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1">Balance: <span id="customer_account_exp">0</span></span>
                  </div>
                </div>
                <input type="hidden" name="customer_account" id="customer_account" value="<?= @$fetchPurchase['customer_account'] ?>">
                <input type="hidden" name="client_contact" id="client_contact" value="<?= @$fetchPurchase['client_contact'] ?>">
              </div>

              <!-- Narration -->
              <div class="col-md-3">
                <label>Narration</label>
                <input type="text" value="<?= @$fetchPurchase['purchase_narration'] ?>" autocomplete="off" class="form-control" name="purchase_narration" placeholder="Optional Notes">
              </div>
            </div>

            <div class="form-group row align-items-end">
              <div class="col-12 col-sm-6 col-md-2 mb-2">
                <label for="get_product_code">Product Barcode</label>
                <input type="text"
                  placeholder="Scan or enter barcode"
                  name="product_code"
                  autocomplete="off"
                  id="get_product_code"
                  class="form-control">
              </div>

              <div class="col-12 col-sm-6 col-md-3 mb-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <label for="get_product_name" class="mb-0 ">Products</label>
                  <span id="instockQty" class="badge badge-info font-weight-bold px-3 py-1" style="font-size: 0.9rem;">In Stock: 0</span>
                </div>
                <input type="hidden" id="add_pro_type" value="add">
                <select class="form-control searchableSelect"
                  id="get_product_name"
                  name="product_id">
                  <option value="">Select Product</option>
                  <?php
                  $result = mysqli_query($dbc, "SELECT * FROM product WHERE status=1 ORDER BY product_name ASC");
                  while ($row = mysqli_fetch_array($result)) {
                    $getBrand = fetchRecord($dbc, "brands", "brand_id", $row['brand_id']);
                    $getCat = fetchRecord($dbc, "categories", "categories_id", $row['category_id']);
                  ?>
                    <option
                      data-price="<?= $row["current_rate"] ?>"
                      data-code="<?= $row["product_code"] ?>"
                      value="<?= $row["product_id"] ?>">
                      <?= ucwords($row["product_name"]) ?> | <?= ucwords(@$getBrand["brand_name"]) ?> (<?= ucwords(@$getCat["categories_name"]) ?>)
                    </option>
                  <?php } ?>
                </select>
              </div>

              <div class="col-12 col-sm-6 col-md-2 mb-2">
                <label for="get_product_price">Purchase Rate</label>
                <input type="number"
                  min="0"
                  class="form-control"
                  id="get_product_price"
                  placeholder="Enter purchase rate">
              </div>

              <div class="col-12 col-sm-6 col-md-2 mb-2">
                <label for="get_sale_price">Sale Rate</label>
                <input type="number"
                  min="0"
                  <?= ($_SESSION['user_role'] == "admin") ? "" : "readonly" ?>
                  class="form-control"
                  id="get_sale_price"
                  placeholder="Enter sale rate">
              </div>

              <div class="col-12 col-sm-6 col-md-2 mb-2">
                <label for="get_product_quantity">Quantity</label>
                <input type="number"
                  class="form-control"
                  id="get_product_quantity"
                  value="0"
                  min="0"
                  name="quantity"
                  placeholder="Enter quantity">
              </div>

              <div class="col-12 col-sm-6 col-md-1 mb-2 text-right">
                <button type="button" class="btn btn-success btn-sm mt-2" id="addProductPurchase">
                  <i class="fa fa-plus"></i> <b>Add</b>
                </button>
              </div>
            </div>

            <div class="row">
              <div class="col-12">
                <table class="table saleTable" id="myDiv">
                  <thead class="table-bordered">
                    <tr>
                      <th><strong>Product Barcode</strong></th>
                      <th><strong>Product Name</strong></th>
                      <th><strong>Unit Price</strong></th>
                      <th><strong>Quantity</strong></th>
                      <th><strong>Total Price</strong></th>
                      <th><strong>Action</strong></th>
                    </tr>
                  </thead>
                  <tbody class="table table-bordered" id="purchase_product_tb">
                    <?php if (isset($_REQUEST['edit_purchase_id'])):
                      $q = mysqli_query($dbc, "SELECT  product.*,brands.*,purchase_item.* FROM purchase_item INNER JOIN product ON product.product_id=purchase_item.product_id INNER JOIN brands ON product.brand_id=brands.brand_id   WHERE purchase_item.purchase_id='" . base64_decode($_REQUEST['edit_purchase_id']) . "'");

                      while ($r = mysqli_fetch_assoc($q)) {

                    ?>
                        <tr id="product_idN_<?= $r['product_id'] ?>">
                          <input type="hidden" data-price="<?= $r['rate'] ?>" data-quantity="<?= $r['quantity'] ?>" id="product_ids_<?= $r['product_id'] ?>" class="product_ids" name="product_ids[]" value="<?= $r['product_id'] ?>">
                          <input type="hidden" id="product_quantites_<?= $r['product_id'] ?>" name="product_quantites[]" value="<?= $r['quantity'] ?>">
                          <input type="hidden" id="product_rate_<?= $r['product_id'] ?>" name="product_rates[]" value="<?= $r['rate'] ?>">
                          <input type="hidden" id="product_totalrate_<?= $r['product_id'] ?>" name="product_totalrates[]" value="<?= $r['rate'] ?>">

                          <td><?= ucwords($r['product_code']) ?></td>
                          <td><?= ucwords($r['product_name']) ?></td>
                          <td><?= $r['rate'] ?></td>
                          <td><?= $r['quantity'] ?></td>
                          <td><?= (float)$r['rate'] * (float)$r['quantity'] ?></td>
                          <td>
                            <button type="button" onclick="removeByid('#product_idN_<?= $r['product_id'] ?>')" class="fa fa-trash text-danger"></button>
                            <button type="button" onclick="editByid(<?= $r['product_id'] ?>, '<?= ucwords($r['product_code']) ?>', <?= $r['rate'] ?>, <?= $r['quantity'] ?>)" class="fa fa-edit text-success ml-2"></button>
                          </td>
                        </tr>
                    <?php }
                    endif ?>
                  </tbody>

                  <tfoot>
                    <!-- Subtotal & Discount -->
                    <tr>
                      <td colspan="2"></td>
                      <td class="table-bordered align-middle"><strong>Sub Total:</strong></td>
                      <td class="table-bordered align-middle" id="product_total_amount">
                        <?= @$fetchPurchase['total_amount'] ?>
                      </td>
                      <td class="table-bordered align-middle"><strong>Discount:</strong></td>
                      <td class="table-bordered">
                        <div class="input-group input-group-sm">
                          <span class="input-group-text py-0">Rs</span>
                          <input
                            type="number"
                            id="ordered_discount"
                            name="ordered_discount"
                            class="form-control"
                            value="<?= @$fetchPurchase['discount'] ?>"
                            min="0"
                            placeholder="Enter discount"
                            onkeyup="getOrderTotal()">
                        </div>
                      </td>

                    </tr>

                    <!-- Grand Total & Paid -->
                    <tr>
                      <td colspan="2" class="border-none"></td>
                      <td class="table-bordered align-middle"><strong>Grand Total:</strong></td>
                      <td class="table-bordered align-middle" id="product_grand_total_amount">
                        <?= @$fetchPurchase['grand_total'] ?>
                      </td>
                      <td class="table-bordered align-middle"><strong>Paid:</strong></td>
                      <td class="table-bordered">
                        <div class="input-group input-group-sm">
                          <span class="input-group-text py-0">Rs</span>
                          <input
                            type="number"
                            class="form-control"
                            id="paid_ammount"
                            name="paid_ammount"
                            value="<?= @$fetchPurchase['paid'] ?>"
                            min="0"
                            placeholder="Enter paid amount"
                            onkeyup="getRemaingAmount()"
                            required>
                        </div>
                      </td>
                    </tr>

                    <!-- Remaining & Account -->
                    <tr>
                      <td colspan="2" class="border-none"></td>
                      <td class="table-bordered align-middle"><strong>Remaining Amount:</strong></td>
                      <td class="table-bordered">
                        <div class="input-group input-group-sm">
                          <span class="input-group-text py-0">Rs</span>
                          <input
                            type="number"
                            class="form-control form-control-sm"
                            id="remaining_ammount"
                            name="remaining_ammount"
                            value="<?= @$fetchPurchase['due'] ?>"
                            readonly
                            placeholder="Calculated due">
                        </div>
                      </td>
                      <td class="table-bordered align-middle"><strong>Account:</strong></td>
                      <td class="table-bordered">
                        <div class="input-group">
                          <select
                            class="form-control"
                            name="payment_account"
                            id="payment_account"
                            onchange="getBalance(this.value,'payment_account_bl')"
                            aria-label="Payment Account"
                            aria-describedby="basic-addon1">
                            <option value="">Select Bank</option>
                            <?php
                            $q = mysqli_query($dbc, "SELECT * FROM customers WHERE customer_status = 1 AND customer_type = 'bank'");
                            while ($r = mysqli_fetch_assoc($q)):
                              // Check if this option should be selected
                              $isSelected = '';
                              if (!empty($fetchPurchase['payment_account'])) {
                                // Edit Mode
                                $isSelected = ($fetchPurchase['payment_account'] == $r['customer_id']) ? 'selected' : '';
                              } else {
                                // New Form - Default to 'Cash In Hand'
                                $isSelected = (strtolower($r['customer_id']) == '2') ? 'selected' : '';
                              }
                            ?>
                              <option <?= $isSelected ?> value="<?= $r['customer_id'] ?>">
                                <?= ucwords($r['customer_name']) ?>
                              </option>
                            <?php endwhile; ?>
                          </select>
                          <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">
                              Balance: <span id="payment_account_bl">0</span>
                            </span>
                          </div>
                        </div>
                      </td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-6 offset-6">

                <button class="btn btn-admin float-right " name="sale_order_btn" value="print" type="submit" id="sale_order_btn">Save and Print</button>

              </div>
            </div>
          </form>
        </div>
      </div> <!-- .row -->
    </div> <!-- .container-fluid -->


  </div> <!-- .wrapper -->

</body>

</html>
<?php include_once 'includes/foot.php'; ?>

<script>
  $('#get_product_quantity').on('keydown', function(e) {
    let currentVal = parseInt($(this).val()) || 0;

    if (e.key === 'ArrowUp') {
      e.preventDefault();
      $(this).val(currentVal + 5);
    }

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      let newVal = currentVal - 5;
      $(this).val(newVal < 1 ? 1 : newVal); // prevent value from going below 1
    }
  });
</script>