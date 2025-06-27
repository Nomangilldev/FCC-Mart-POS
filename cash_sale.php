<!DOCTYPE html>
<html lang="en">
<?php include_once 'includes/head.php';

if (!empty($_REQUEST['edit_order_id'])) {
  # code...
  $fetchOrder = fetchRecord($dbc, "orders", "order_id", base64_decode($_REQUEST['edit_order_id']));
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
              <b class="text-center card-text">Cash Sale</b>


              <a href="cash_sale.php" class="btn btn-admin float-right btn-sm">Add New</a>
            </div>
          </div>

        </div>
        <div class="card-body">
          <form action="php_action/custom_action.php" method="POST" id="sale_order_fm">
            <input type="hidden" name="product_order_id" value="<?= @empty($_REQUEST['edit_order_id']) ? "" : base64_decode($_REQUEST['edit_order_id']) ?>">
            <input type="hidden" name="payment_type" id="payment_type" value="cash_in_hand">

            <div class="row form-group">
              <div class="col-md-2">
                <label for="next_increment">Order ID#</label>
                <?php
                $result = mysqli_query($dbc, "SHOW TABLE STATUS LIKE 'orders'");
                $data = mysqli_fetch_assoc($result);
                $next_increment = $data['Auto_increment'];
                ?>
                <input
                  type="text"
                  name="next_increment"
                  id="next_increment"
                  value="<?= empty($_REQUEST['edit_order_id']) ? $next_increment : $fetchOrder['order_id'] ?>"
                  readonly
                  class="form-control"
                  placeholder="Auto Order ID">
              </div>

              <div class="col-md-2">
                <label for="order_date">Order Date</label>
                <input
                  type="text"
                  name="order_date"
                  id="order_date"
                  value="<?= empty($_REQUEST['edit_order_id']) ? date('Y-m-d') : $fetchOrder['order_date'] ?>"
                  readonly
                  class="form-control"
                  placeholder="YYYY-MM-DD">
              </div>

              <div class="col-md-3">
                <label for="client_contact">Customer Number</label>
                <input
                  type="number"
                  name="client_contact"
                  id="client_contact"
                  onchange="getCustomer_name(this.value)"
                  value="<?= @$fetchOrder['client_contact'] ?>"
                  autocomplete="off"
                  required
                  min="0"
                  list="phone"
                  class="form-control"
                  placeholder="Enter customer number">
                <datalist id="phone">
                  <?php
                  $q = mysqli_query($dbc, "SELECT DISTINCT client_contact FROM orders");
                  while ($r = mysqli_fetch_assoc($q)) {
                  ?>
                    <option value="<?= $r['client_contact'] ?>"><?= $r['client_contact'] ?></option>
                  <?php } ?>
                </datalist>
              </div>

              <div class="col-md-3">
                <label for="sale_order_client_name">Customer Name</label>
                <input
                  type="text"
                  name="sale_order_client_name"
                  id="sale_order_client_name"
                  value="<?= @$fetchOrder['client_name'] ?>"
                  autocomplete="off"
                  required
                  list="client_name"
                  class="form-control"
                  placeholder="Enter customer name">
                <datalist id="client_name">
                  <?php
                  $q = mysqli_query($dbc, "SELECT DISTINCT client_name FROM orders");
                  while ($r = mysqli_fetch_assoc($q)) {
                  ?>
                    <option value="<?= $r['client_name'] ?>"><?= $r['client_name'] ?></option>
                  <?php } ?>
                </datalist>
              </div>

              <div class="col-md-2">
                <label for="vehicle_no">Vehicle NO</label>
                <input
                  type="text"
                  name="vehicle_no"
                  id="vehicle_no"
                  value="<?= @$fetchOrder['vehicle_no'] ?>"
                  autocomplete="off"
                  list="vehicle_no_list"
                  class="form-control"
                  placeholder="Enter vehicle number">
                <datalist id="vehicle_no_list">
                  <?php
                  $q = mysqli_query($dbc, "SELECT DISTINCT vehicle_no FROM orders");
                  while ($r = mysqli_fetch_assoc($q)) {
                  ?>
                    <option value="<?= $r['vehicle_no'] ?>"><?= $r['vehicle_no'] ?></option>
                  <?php } ?>
                </datalist>
              </div>
            </div>

            <div class="form-group row">
              <div class="col-6 col-md-3">
                <label for="get_product_code">Product Code</label>
                <input
                  type="text"
                  name="product_code"
                  id="get_product_code"
                  autocomplete="off"
                  autofocus
                  class="form-control"
                  placeholder="Enter product code">
              </div>

              <div class="col-6 col-md-3">
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
              <div class="col-6 col-sm-2 col-md-2">
                <label for="get_product_price">Price</label>
                <input
                  type="number"
                  id="get_product_price"
                  min="0"
                  class="form-control"
                  placeholder="Price"
                  <?= ($_SESSION['user_role'] == "admin") ? "" : "readonly" ?>>
              </div>

              <div class="col-6 col-sm-2 col-md-2">
                <label for="get_product_quantity">Quantity</label>
                <input
                  type="number"
                  id="get_product_quantity"
                  name="quantity"
                  value="0"
                  min="0"
                  data-max=""
                  class="form-control"
                  placeholder="Quantity">
              </div>

              <div class="col-sm-1 d-flex pb-1 align-items-end">
                <button
                  type="button"
                  class="btn btn-success btn-sm w-100"
                  id="addProductPurchase">
                  <i class="fa fa-plus"></i> <b>Add</b>
                </button>
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <table class="table saleTable" id="myDiv">
                  <thead class="table-bordered">
                    <tr>
                      <th><strong>Code</strong></th>
                      <th><strong>Product Name</strong></th>
                      <th><strong>Unit Price</strong></th>
                      <th><strong>Quantity</strong></th>
                      <th><strong>Profit</strong></th>
                      <th><strong>Total Price</strong></th>
                      <th><strong>Action</strong></th>
                    </tr>
                  </thead>
                  <tbody class="table table-bordered" id="purchase_product_tb">
                    <?php if (isset($_REQUEST['edit_order_id'])):
                      $q = mysqli_query($dbc, "SELECT product.*,brands.*,order_item.* FROM order_item INNER JOIN product ON product.product_id=order_item.product_id INNER JOIN brands ON product.brand_id=brands.brand_id WHERE order_item.order_id='" . base64_decode($_REQUEST['edit_order_id']) . "'");
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
                            <button type="button" onclick="removeByid(`#product_idN_<?= $r['product_id'] ?>`)" class="fa fa-trash text-danger"></button>
                            <button type="button" onclick="editByid(<?= $r['product_id'] ?>,`<?= $r['product_code'] ?>`,<?= $r['rate'] ?>,<?= $r['quantity'] ?>)" class="fa fa-edit text-success ml-2"></button>
                          </td>
                        </tr>
                    <?php }
                    endif ?>
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="2"></td>
                      <td class="table-bordered"><strong>Sub Total :</strong></td>
                      <td class="table-bordered" id="product_total_amount"><?= @$fetchOrder['total_amount'] ?></td>
                      <td class="table-bordered"><strong>Discount :</strong></td>
                      <td class="table-bordered" id="getDiscount">
                        <div class="row">
                          <div class="col-sm-6 pr-0">
                            <input
                              onkeyup="getOrderTotal()"
                              type="number"
                              id="ordered_discount"
                              class="form-control form-control-sm"
                              value="<?= @empty($_REQUEST['edit_order_id']) ? "0" : $fetchOrder['discount'] ?>"
                              min="0"
                              name="ordered_discount"
                              placeholder="Enter Discount">
                          </div>
                          <div class="col-sm-6 pl-0">
                            <input
                              onkeyup="getOrderTotal()"
                              type="number"
                              id="freight"
                              class="form-control form-control-sm"
                              placeholder="Freight Amount"
                              value="<?= @$fetchOrder['freight'] ?>"
                              min="0"
                              name="freight">
                          </div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="2" class="border-none"></td>
                      <td class="table-bordered"><strong>Grand Total :</strong></td>
                      <td class="table-bordered" id="product_grand_total_amount"><?= @$fetchOrder['grand_total'] ?></td>
                      <td class="table-bordered"><strong>Paid :</strong></td>
                      <td class="table-bordered">
                        <div class="form-group row">
                          <div class="col-sm-6">
                            <input
                              type="number"
                              min="0"
                              class="form-control form-control-sm"
                              id="paid_ammount"
                              required
                              onkeyup="getRemaingAmount()"
                              name="paid_ammount"
                              value="<?= @isset($fetchOrder['paid']) ? $fetchOrder['paid'] : "0" ?>"
                              placeholder="Amount Paid">
                          </div>
                          <div class="col-sm-6">
                            <div class="custom-control custom-switch">
                              <input type="checkbox" class="custom-control-input" id="full_payment_check">
                              <label class="custom-control-label" for="full_payment_check">Full Payment</label>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="2" class="border-none"></td>
                      <td class="table-bordered"><strong>Remaining Amount :</strong></td>
                      <td class="table-bordered">
                        <input
                          type="number"
                          class="form-control form-control-sm"
                          id="remaining_ammount"
                          readonly
                          name="remaining_ammount"
                          value="<?= @$fetchOrder['due'] ?>"
                          placeholder="Remaining Amount">
                      </td>
                      <td class="table-bordered"><strong>Account :</strong></td>
                      <td class="table-bordered">
                        <select class="form-control" id="payment_account" name="payment_account" required>
                          <?php
                          $q = mysqli_query($dbc, "SELECT * FROM customers WHERE customer_status = 1 AND customer_type='bank'");
                          while ($r = mysqli_fetch_assoc($q)) : ?>
                            <option <?= @($fetchOrder['payment_account'] == $r['customer_id']) ? "selected" : "" ?> value="<?= $r['customer_id'] ?>">
                              <?= $r['customer_name'] ?>
                            </option>
                          <?php endwhile; ?>
                        </select>
                      </td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-6 offset-6">

                <button class="btn btn-admin float-right " name="sale_order_btn" value="print" type="submit" id="sale_order_btn" formnovalidate>Save and Print</button>

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