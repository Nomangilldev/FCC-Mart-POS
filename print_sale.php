<center>
    <?php


    require_once 'php_action/db_connect.php';

    $orderId = $_REQUEST['id'];

    $get_company = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM company ORDER BY id DESC LIMIT 1 "));

    $sql = "SELECT * FROM orders WHERE order_id = $orderId";

    $orderResult = $connect->query($sql);
    $orderData = $orderResult->fetch_array();

    $orderDate = $orderData[0];
    $clientName = $orderData[1];
    $clientContact = $orderData[2];
    $subTotal = $orderData[3];
    $vat = $orderData[4];
    $totalAmount = $orderData[5];
    $discount = $orderData['discount'];
    $grandTotal = $orderData[7];
    $paid = $orderData[8];
    $due = $orderData[9];
    $order_type = $orderData[10];
    $address = $orderData[11];
    $table = $orderData[12];


    $orderItemSql = "SELECT order_item.product_id, order_item.rate, order_item.quantity, order_item.total,
product.product_name FROM order_item
    INNER JOIN product ON order_item.product_id = product.product_id 
 WHERE order_item.order_id = $orderId ";
    $orderItemResult = $connect->query($orderItemSql);
    if (mysqli_num_rows($orderItemResult) > 0) {

    ?>

        <body>
            <table border="1" cellspacing="0" cellpadding="2" width="100%" style="font-size:20px;">
                <thead>
                    <tr>
                        <th colspan="5">
                            <div align="center">
                                <img src="img/logo/<?= $get_company['logo'] ?>" style="width: 100px;"><br />
                                <h4 style="margin: 5px 0;"><?= $get_company['name'] ?></h4>
                                <p style="font-size: 18px; margin: 0;">
                                    <strong>Cell No:</strong> <?= $get_company['company_phone'] ?><br />
                                    <?= $get_company['address'] ?>
                                </p>
                            </div>
                        </th>
                    </tr>
                    <tr>
                        <th colspan="5">
                            <div align="left" style="line-height: 1.5;">
                                Bill No.: <strong><?= $orderData['order_id']; ?></strong><br />
                                Order Date: <strong><?= $orderData['order_date']; ?></strong><br />
                                Client Name: <strong><?= $orderData['client_name']; ?></strong><br />
                                Contact: <strong><?= $orderData['client_contact']; ?></strong>
                            </div>
                        </th>
                    </tr>
                    <tr>
                        <th colspan="5">
                            <center><!-- Reserved for optional content --></center>
                        </th>
                    </tr>
                </thead>
            </table>

            <table border="1" width="90%" cellpadding="1" style="border-collapse: collapse; font-size: 20px; border: 1px solid black;">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Product</th>
                        <th>Rate</th>
                        <th>QTY</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $x = 1;
                    $subamount = 0;
                    $totaldisc = 0;
                    $grand_total_show = 0;
                    while ($row = $orderItemResult->fetch_array()) {
                        $product_id = $row['product_id'];
                        $fetchProduct = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM product WHERE product_id='$product_id'"));
                        $fetchCategory = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM categories WHERE categories_id='{$fetchProduct['category_id']}'"));

                        $product_name = !empty($fetchProduct['product_name_urdu']) ? $fetchProduct['product_name_urdu'] : $fetchProduct['product_name'];
                        $rate = $row['rate'];
                        $qty = $row[2];
                        $line_total = $rate * $qty;
                        $subamount += $line_total;
                    ?>
                        <tr>
                            <td><?= $x ?></td>
                            <td><?= ucwords($product_name) ?></td>
                            <td><?= number_format($rate, 2) ?></td>
                            <td><?= $qty ?></td>
                            <td><?= number_format($line_total, 2) ?></td>
                        </tr>
                    <?php
                        $x++;
                    }
                    ?>
                </tbody>
            </table>


            <table style="float: right; font-size: 25px; margin-right: 40px; border-collapse: collapse;" border="1">
                <tr>
                    <td><strong>Sub Amount</strong></td>
                    <td><?= number_format($subamount, 2) ?></td>
                </tr>

                <?php
                $grand_total = $subamount;

                // Add Freight (Addon)
                if (!empty($orderData['freight']) && $orderData['freight'] > 0) {
                    $freight = $orderData['freight'];
                    $grand_total += $freight;
                ?>
                    <tr>
                        <td><strong>Freight (Addon)</strong></td>
                        <td><?= number_format($freight, 2) ?></td>
                    </tr>
                <?php
                }

                // Discount
                if (!empty($discount) && $discount > 0) {
                    $grand_total -= $discount;
                ?>
                    <tr>
                        <td><strong>Discount (Rs.)</strong></td>
                        <td><?= number_format($discount, 2) ?></td>
                    </tr>
                <?php } ?>

                <tr>
                    <td><strong>Grand Total</strong></td>
                    <td><?= number_format($grand_total, 2) ?></td>
                </tr>

                <tr>
                    <td><strong>Cash Received</strong></td>
                    <td><?= number_format($orderData['paid'], 2) ?></td>
                </tr>

                <tr>
                    <td><strong>Returnable</strong></td>
                    <td><?= number_format(abs($grand_total - $orderData['paid']), 2) ?></td>
                </tr>
            </table>


            <div style="margin-top:120px;">

                <p style="font-size: 20px">
                    تشریف آوری کا شکریہ
                </p>
                <p style="font-size: 20px">
                    کسی بھی شکایت کی صورت میں رابطہ کریں
                </p>


                <p style="margin-top:0px;font-size:14px"><strong>Software Developed By: <br /> SAM'Z Creation<br />(0342-4264494)</strong></p>
            </div> <br />
        <?php
    }
        ?>

        </body>
</center>