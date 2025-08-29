<?php
require_once '../php_action/db_connect.php';
require_once '../includes/functions.php';

if (isset($_REQUEST['product_id1'])) {

	$data = [

		'production_date' => @$_REQUEST['production_date'],
		'production_note' => @$_REQUEST['production_note'],
		'product_id1' => @$_REQUEST['product_id1'],
		'quantity1' => @$_REQUEST['quantity1'],
		'product_id2' => @$_REQUEST['product_id2'],
		'quantity2' => @$_REQUEST['quantity2'],
		'user_id' => @$_REQUEST['user_id'],
	];

	if ($_REQUEST['production_id'] == "") {

		if (insert_data($dbc, "production", $data)) {



			$pro_id1 = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM product WHERE product_id  =  '$_REQUEST[product_id1]' "));
			$pro_id2 = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM product WHERE product_id  =  '$_REQUEST[product_id2]' "));

			$stock1minus = $pro_id1['quantity_instock'] - $_REQUEST['quantity1'];

			$pro_id1minus = mysqli_query($dbc, "UPDATE product SET quantity_instock = '$stock1minus' WHERE product_id = '$_REQUEST[product_id1]'");


			$stock2plus = $pro_id2['quantity_instock'] + $_REQUEST['quantity2'];

			$pro_id1minus = mysqli_query($dbc, "UPDATE product SET quantity_instock = '$stock2plus' WHERE product_id = '$_REQUEST[product_id2]'");


			$res = ['msg' => "Prodcution Added Successfully", 'sts' => 'success'];
		} else {

			$res = ['msg' => mysqli_error($dbc), 'sts' => 'error'];
		}
	} else {
		if (update_data($dbc, "production", $data, "production_id", $_REQUEST['production_id'])) {


			$res = ['msg' => "Updated Successfully", 'sts' => 'success'];
		} else {

			$res = ['msg' => mysqli_error($dbc), 'sts' => 'error'];
		}
	}
	echo json_encode($res);
}




if (isset($_REQUEST['add_manually_user'])) {
	$data = [
		'customer_name' => @$_REQUEST['customer_name'],
		'customer_phone' => @$_REQUEST['customer_phone'],
		'customer_email' => @$_REQUEST['customer_email'],
		'customer_address' => @$_REQUEST['customer_address'],
		'customer_type' => @$_REQUEST['customer_type'],
		'customer_salary' => @$_REQUEST['customer_salary'],
		'customer_salary_date' => @$_REQUEST['customer_salary_date'],
		'customer_status' => @$_REQUEST['customer_status'],
		'customer_type' => @$_REQUEST['add_manually_user'],
	];
	if ($_REQUEST['customer_id'] == "") {

		if (insert_data($dbc, "customers", $data)) {


			$res = ['msg' => ucfirst($_REQUEST['add_manually_user']) . " Added Successfully", 'sts' => 'success'];
		} else {

			$res = ['msg' => mysqli_error($dbc), 'sts' => 'error'];
		}
	} else {
		if (update_data($dbc, "customers", $data, "customer_id", $_REQUEST['customer_id'])) {


			$res = ['msg' => ucfirst($_REQUEST['add_manually_user']) . " Updated Successfully", 'sts' => 'success'];
		} else {

			$res = ['msg' => mysqli_error($dbc), 'sts' => 'error'];
		}
	}
	echo json_encode($res);
}

if (isset($_REQUEST['new_voucher_date'])) {

	if ($_REQUEST['voucher_id'] == "") {
		if ($_REQUEST['voucher_group'] == "general_voucher") {
			$data = [
				'customer_id1' => @$_REQUEST['voucher_from_account'],
				'customer_id2' => @$_REQUEST['voucher_to_account'],
				'voucher_date' => @$_REQUEST['new_voucher_date'],
				'voucher_hint' => @$_REQUEST['voucher_hint'],
				'voucher_type' => @$_REQUEST['voucher_type'],
				'voucher_amount' => @$_REQUEST['voucher_debit'],
				'voucher_group' => @$_REQUEST['voucher_group'],
				'td_check_no' => @$_REQUEST['td_check_no'],
				'voucher_bank_name' => @$_REQUEST['voucher_bank_name'],
				'td_check_date' => @$_REQUEST['td_check_date'],
				'check_type' => @$_REQUEST['check_type'],
				'addby_user_id' => @$_SESSION['userId'],
			];
		} else {
			$data = [
				'customer_id1' => @$_REQUEST['voucher_from_account'],
				'customer_id2' => @$_REQUEST['voucher_to_account'],
				'voucher_date' => @$_REQUEST['new_voucher_date'],
				'voucher_hint' => @$_REQUEST['voucher_hint'],
				'voucher_type' => @$_REQUEST['voucher_type'],
				'voucher_amount' => @$_REQUEST['voucher_debit'],
				'voucher_group' => @$_REQUEST['voucher_group'],
				'addby_user_id' => @$_SESSION['userId'],
			];
		}
		if (insert_data($dbc, "vouchers", $data)) {
			$last_id = mysqli_insert_id($dbc);
			if ($_REQUEST['voucher_group'] == "expense_voucher") {
				$voucher_to_account = fetchRecord($dbc, "customers", "customer_id", $_REQUEST['voucher_to_account']);
				$budget = [
					'budget_amount' => @$_REQUEST['voucher_debit'],
					'budget_type' => "expense",
					'budget_date' => $_REQUEST['new_voucher_date'],
					'voucher_id' => $last_id,
					'voucher_type' => @$_REQUEST['voucher_type'],
					'budget_name' => @"expense added to " . @$voucher_to_account['customer_name'],
				];
				insert_data($dbc, "budget", $budget);
			} elseif ($_REQUEST['voucher_group'] == "general_voucher" and !empty($_REQUEST['td_check_no'])) {
				$data_checks = [
					'check_no' => $_REQUEST['td_check_no'],
					'check_bank_name' => $_REQUEST['voucher_bank_name'],
					'check_expiry_date' => $_REQUEST['td_check_date'],
					'check_type' => $_REQUEST['check_type'],
					'voucher_id' => $last_id,
					'check_status' => 0,
				];
				insert_data($dbc, "checks", $data_checks);
			}


			$debit = [
				'debit' => @$_REQUEST['voucher_debit'],
				'credit' => 0,
				'customer_id' => @$_REQUEST['voucher_from_account'],
				'transaction_from' => 'voucher',
				'transaction_type' => @$_REQUEST['voucher_type'],
				'transaction_remarks' => @$_REQUEST['voucher_hint'],
				'transaction_date' => @$_REQUEST['new_voucher_date'],
			];
			insert_data($dbc, "transactions", $debit);
			$transaction_id1 = mysqli_insert_id($dbc);
			$credit = [
				'credit' => @$_REQUEST['voucher_debit'],
				'debit' => 0,
				'customer_id' => @$_REQUEST['voucher_to_account'],
				'transaction_from' => 'voucher',
				'transaction_type' => @$_REQUEST['voucher_type'],
				'transaction_remarks' => @$_REQUEST['voucher_hint'],
				'transaction_date' => @$_REQUEST['new_voucher_date'],
			];

			insert_data($dbc, "transactions", $credit);
			$transaction_id2 = mysqli_insert_id($dbc);
			$newData = ['transaction_id1' => $transaction_id1, 'transaction_id2' => $transaction_id2];
			if (update_data($dbc, "vouchers", $newData, "voucher_id", $last_id)) {
				$res = ['msg' => "Voucher Added Successfully", 'sts' => 'success', 'voucher_id' => base64_encode($last_id)];
			} else {
				$res = ['msg' => mysqli_error($dbc), 'sts' => 'error'];
			}
		} else {

			$res = ['msg' => mysqli_error($dbc), 'sts' => 'error'];
		}
	} else {
		if ($_REQUEST['voucher_group'] == "general_voucher") {
			$data = [
				'customer_id1' => @$_REQUEST['voucher_from_account'],
				'customer_id2' => @$_REQUEST['voucher_to_account'],
				'voucher_date' => @$_REQUEST['new_voucher_date'],
				'voucher_hint' => @$_REQUEST['voucher_hint'],
				'voucher_type' => @$_REQUEST['voucher_type'],
				'voucher_amount' => @$_REQUEST['voucher_debit'],
				'voucher_group' => @$_REQUEST['voucher_group'],
				'editby_user_id' => @$_SESSION['userId'],
				'td_check_no' => @$_REQUEST['td_check_no'],
				'voucher_bank_name' => @$_REQUEST['voucher_bank_name'],
				'check_type' => @$_REQUEST['check_type'],
				'td_check_date' => @$_REQUEST['td_check_date'],
			];
		} else {
			$data = [
				'customer_id1' => @$_REQUEST['voucher_from_account'],
				'customer_id2' => @$_REQUEST['voucher_to_account'],
				'voucher_date' => @$_REQUEST['new_voucher_date'],
				'voucher_hint' => @$_REQUEST['voucher_hint'],
				'voucher_type' => @$_REQUEST['voucher_type'],
				'voucher_amount' => @$_REQUEST['voucher_debit'],
				'voucher_group' => @$_REQUEST['voucher_group'],
				'editby_user_id' => @$_SESSION['userId'],
			];
		}
		if (update_data($dbc, "vouchers", $data, "voucher_id", $_REQUEST['voucher_id'])) {
			$last_id = $_REQUEST['voucher_id'];

			$transactions = fetchRecord($dbc, "vouchers", "voucher_id", $_REQUEST['voucher_id']);


			if ($_REQUEST['voucher_group'] == "expense_voucher") {
				$voucher_to_account = fetchRecord($dbc, "customers", "customer_id", $_REQUEST['voucher_to_account']);
				$budget = [
					'budget_amount' => @$_REQUEST['voucher_debit'],
					'budget_type' => "expense",
					'budget_date' => $_REQUEST['new_voucher_date'],
					'voucher_id' => $last_id,
					'voucher_type' => @$_REQUEST['voucher_type'],
					'budget_name' => @"expense added to " . @$voucher_to_account['customer_name'],
				];

				update_data($dbc, "budget", $budget, "voucher_id", $_REQUEST['voucher_id']);
			} elseif ($_REQUEST['voucher_group'] == "general_voucher" and !empty($_REQUEST['td_check_no'])) {
				$data_checks = [
					'check_no' => $_REQUEST['td_check_no'],
					'check_bank_name' => $_REQUEST['voucher_bank_name'],
					'check_expiry_date' => $_REQUEST['td_check_date'],
					'check_type' => $_REQUEST['check_type'],
					'voucher_id' => $last_id,
				];
				update_data($dbc, "checks", $data_checks, "voucher_id", $_REQUEST['voucher_id']);
			}

			$debit = [
				'debit' => @$_REQUEST['voucher_debit'],
				'credit' => 0,
				'customer_id' => @$_REQUEST['voucher_from_account'],
				'transaction_from' => 'voucher',
				'transaction_type' => @$_REQUEST['voucher_type'],
				'transaction_remarks' => @$_REQUEST['voucher_hint'],
				'transaction_date' => @$_REQUEST['new_voucher_date'],
			];

			update_data($dbc, "transactions", $debit, "transaction_id", $transactions['transaction_id1']);

			$credit = [
				'credit' => @$_REQUEST['voucher_debit'],
				'debit' => 0,
				'customer_id' => @$_REQUEST['voucher_to_account'],
				'transaction_from' => 'voucher',
				'transaction_type' => @$_REQUEST['voucher_type'],
				'transaction_remarks' => @$_REQUEST['voucher_hint'],
				'transaction_date' => @$_REQUEST['new_voucher_date'],
			];

			update_data($dbc, "transactions", $credit, "transaction_id", $transactions['transaction_id2']);

			$res = ['msg' => "Voucher Updated Successfully", 'sts' => 'success', 'voucher_id' => base64_encode($last_id)];
		} else {

			$res = ['msg' => mysqli_error($dbc), 'sts' => 'error'];
		}
	}
	echo json_encode($res);
}
if (isset($_REQUEST['new_sin_voucher_date'])) {
	if (!empty($_REQUEST['voucher_debit'])) {
		$amount = $_REQUEST['voucher_debit'];
	} else {
		$amount = $_REQUEST['voucher_credit'];
	}
	if ($_REQUEST['voucher_id'] == "") {
		$data = [
			'customer_id1' => @$_REQUEST['voucher_from_account'],
			'voucher_date' => @$_REQUEST['new_sin_voucher_date'],
			'voucher_hint' => @$_REQUEST['voucher_hint'],
			'voucher_amount' => $amount,
			'voucher_group' => @$_REQUEST['voucher_group'],
			'addby_user_id' => @$_SESSION['userId'],
		];
		if (insert_data($dbc, "vouchers", $data)) {
			$last_id = mysqli_insert_id($dbc);

			if (!empty($_REQUEST['voucher_debit'])) {
				$debit = [
					'debit' => $amount,
					'credit' => 0,
					'customer_id' => @$_REQUEST['voucher_from_account'],
					'transaction_from' => 'voucher',
					'transaction_type' => "single_voucher",
					'transaction_remarks' => @$_REQUEST['voucher_hint'],
					'transaction_date' => @$_REQUEST['new_sin_voucher_date'],
				];
				insert_data($dbc, "transactions", $debit);
			} else {
				$credit = [
					'credit' => $amount,
					'debit' => 0,
					'customer_id' => @$_REQUEST['voucher_from_account'],
					'transaction_from' => 'voucher',
					'transaction_type' => "single_voucher",
					'transaction_remarks' => @$_REQUEST['voucher_hint'],
					'transaction_date' => @$_REQUEST['new_sin_voucher_date'],
				];
				insert_data($dbc, "transactions", $credit);
			}



			$transaction_id1 = mysqli_insert_id($dbc);

			$newData = ['transaction_id1' => $transaction_id1];
			if (update_data($dbc, "vouchers", $newData, "voucher_id", $last_id)) {
				$res = ['msg' => "Voucher Added Successfully", 'sts' => 'success', 'voucher_id' => base64_encode($last_id)];
			} else {
				$res = ['msg' => mysqli_error($dbc), 'sts' => 'error'];
			}
		} else {

			$res = ['msg' => mysqli_error($dbc), 'sts' => 'error'];
		}
	} else {
		$data = [
			'customer_id1' => @$_REQUEST['voucher_from_account'],
			'voucher_date' => @$_REQUEST['new_sin_voucher_date'],
			'voucher_hint' => @$_REQUEST['voucher_hint'],
			'voucher_amount' => $amount,
			'voucher_group' => @$_REQUEST['voucher_group'],
			'editby_user_id' => @$_SESSION['userId'],
		];

		if (update_data($dbc, "vouchers", $data, "voucher_id", $_REQUEST['voucher_id'])) {
			$last_id = $_REQUEST['voucher_id'];

			$transactions = fetchRecord($dbc, "vouchers", "voucher_id", $_REQUEST['voucher_id']);

			if (!empty($_REQUEST['voucher_debit'])) {
				$debit = [
					'debit' => @$_REQUEST['voucher_debit'],
					'credit' => 0,
					'customer_id' => @$_REQUEST['voucher_from_account'],
					'transaction_from' => 'voucher',
					'transaction_type' => "single_voucher",
					'transaction_remarks' => @$_REQUEST['voucher_hint'],
					'transaction_date' => @$_REQUEST['new_sin_voucher_date'],
				];
				update_data($dbc, "transactions", $debit, "transaction_id", $transactions['transaction_id1']);
			} else {
				$credit = [
					'credit' => @$_REQUEST['voucher_credit'],
					'debit' => 0,
					'customer_id' => @$_REQUEST['voucher_from_account'],
					'transaction_from' => 'voucher',
					'transaction_type' => "single_voucher",
					'transaction_remarks' => @$_REQUEST['voucher_hint'],
					'transaction_date' => @$_REQUEST['new_sin_voucher_date'],
				];
				update_data($dbc, "transactions", $credit, "transaction_id", $transactions['transaction_id1']);
				;
			}


			$res = ['msg' => "Voucher Updated Successfully", 'sts' => 'success', 'voucher_id' => base64_encode($last_id)];
		} else {

			$res = ['msg' => mysqli_error($dbc), 'sts' => 'error'];
		}
	}
	echo json_encode($res);
}
if (!empty($_REQUEST['action']) && $_REQUEST['action'] == "product_module") {

	if ($_REQUEST['product_id'] == "") {
		// Insert new product
		$data_array = [
			'product_name' => $_REQUEST['product_name'],
			'product_name_urdu' => $_REQUEST['product_name_urdu'],
			'product_code' => @$_REQUEST['product_code'],
			'brand_id' => @$_REQUEST['brand_id'],
			'category_id' => @$_REQUEST['category_id'],
			'current_rate' => @$_REQUEST['current_rate'],
			'product_description' => @$_REQUEST['product_description'],
			't_days' => @$_REQUEST['t_days'],
			'f_days' => @$_REQUEST['f_days'],
			'alert_at' => @$_REQUEST['alert_at'],
			'availability' => @$_REQUEST['availability'],
			'status' => 1,
			'addby_user_id' => @$_SESSION['userId'],
			'quantity_instock' => '0' // only set during insert
		];

		if (insert_data($dbc, "product", $data_array)) {
			$last_id = mysqli_insert_id($dbc);

			// Handle image upload
			if (!empty($_FILES['product_image']['tmp_name'])) {
				$targetDir = '../img/uploads/';
				if (!is_dir($targetDir)) {
					mkdir($targetDir, 0777, true);
				}

				$fileName = $_FILES['product_image']['name'];
				$fileTmp = $_FILES['product_image']['tmp_name'];
				$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
				$allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

				if (in_array($fileExt, $allowedTypes)) {
					$newFileName = time() . uniqid() . '.' . $fileExt;
					$targetFile = $targetDir . $newFileName;

					if (move_uploaded_file($fileTmp, $targetFile)) {
						$product_image = $newFileName;

						$data_image = [
							'product_image' => $product_image,
						];
						update_data($dbc, "product", $data_image, "product_id", $last_id);
					} else {
						die("Error: Unable to move the uploaded file.");
					}
				} else {
					die("Error: Invalid image type.");
				}
			}

			$response = [
				"msg" => "Product Has Been Added",
				"sts" => "success",
				"id" => base64_encode($last_id),
				"link" => base64_encode('add_stock'),
			];
		} else {
			$response = [
				"msg" => mysqli_error($dbc),
				"sts" => "error"
			];
		}
	} else {
		// Update existing product
		$last_id = base64_decode($_REQUEST['product_id']);

		$data_array = [
			'product_name' => $_REQUEST['product_name'],
			'product_name_urdu' => $_REQUEST['product_name_urdu'],
			'product_code' => @$_REQUEST['product_code'],
			'brand_id' => @$_REQUEST['brand_id'],
			'category_id' => @$_REQUEST['category_id'],
			'current_rate' => @$_REQUEST['current_rate'],
			'product_description' => @$_REQUEST['product_description'],
			't_days' => @$_REQUEST['t_days'],
			'f_days' => @$_REQUEST['f_days'],
			'alert_at' => @$_REQUEST['alert_at'],
			'availability' => @$_REQUEST['availability'],
			'status' => 1,
			'addby_user_id' => @$_SESSION['userId']
			// Do NOT include quantity_instock here
		];

		if (update_data($dbc, "product", $data_array, "product_id", $last_id)) {

			// Handle image upload
			if (!empty($_FILES['product_image']['tmp_name'])) {
				$targetDir = '../img/uploads/';
				if (!is_dir($targetDir)) {
					mkdir($targetDir, 0777, true);
				}

				$fileName = $_FILES['product_image']['name'];
				$fileTmp = $_FILES['product_image']['tmp_name'];
				$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
				$allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

				if (in_array($fileExt, $allowedTypes)) {
					$newFileName = time() . uniqid() . '.' . $fileExt;
					$targetFile = $targetDir . $newFileName;

					if (move_uploaded_file($fileTmp, $targetFile)) {
						$product_image = $newFileName;

						$data_image = [
							'product_image' => $product_image,
						];
						update_data($dbc, "product", $data_image, "product_id", $last_id);
					} else {
						die("Error: Unable to move the uploaded file.");
					}
				} else {
					die("Error: Invalid image type.");
				}
			}

			$response = [
				"msg" => "Product Updated",
				"sts" => "success",
				"id" => base64_encode($last_id),
				"link" => base64_encode('add_stock'),
			];
		} else {
			$response = [
				"msg" => mysqli_error($dbc),
				"sts" => "error"
			];
		}
	}

	echo json_encode($response);
}


if (!empty($_REQUEST['action']) and $_REQUEST['action'] == "inventory_module") {

	$data_array = [
		'product_name' => $_REQUEST['product_name'],
		'product_code' => rand(),
		'brand_id' => 0,
		'category_id' => 0,
		'current_rate' => @$_REQUEST['current_rate'],
		'alert_at' => 5,
		'availability' => 1,
		'purchase_rate' => $_REQUEST['current_rate'],
		'status' => 1,
		'inventory' => 1,
	];
	if ($_REQUEST['product_id'] == "") {

		if (insert_data($dbc, "product", $data_array)) {
			$last_id = mysqli_insert_id($dbc);

			$response = [
				"msg" => "Inventory Product Has Been Added",
				"sts" => "success",
			];
		} else {
			$response = [
				"msg" => mysqli_error($dbc),
				"sts" => "error"
			];
		}
	} else {
		if (update_data($dbc, "product", $data_array, "product_id", base64_decode($_REQUEST['product_id']))) {
			$last_id = $_REQUEST['product_id'];



			$response = [
				"msg" => "Product Updated",
				"sts" => "success",
			];
		} else {
			$response = [
				"msg" => mysqli_error($dbc),
				"sts" => "error"
			];
		}
	}
	echo json_encode($response);
}
if (isset($_REQUEST['get_products_list'])) {

	if ($_REQUEST['type'] == "code") {
		$q = mysqli_query($dbc, "SELECT * FROM product WHERE product_code LIKE '%" . $_REQUEST['get_products_list'] . "%' AND status=1 ");
		if (mysqli_num_rows($q) > 0) {
			while ($r = mysqli_fetch_assoc($q)) {
				echo '<option value="' . $r['product_id'] . '">' . $r['product_name'] . '</option>';
			}
		} else {
			echo '<option value="">Not Found</option>';
		}
	}
	if ($_REQUEST['type'] == "product") {
		$q = mysqli_query($dbc, "SELECT * FROM product WHERE product_id='" . $_REQUEST['get_products_list'] . "' AND status=1 ");
		if (mysqli_num_rows($q) > 0) {
			$r = mysqli_fetch_assoc($q);
			echo $r['product_code'];
		}
	}
}
if (isset($_GET['searchTerm'])) {

	$search = $_GET['searchTerm'];
	$sql = "SELECT product_id,category_id,brand_id,product_name FROM product 
                WHERE product_name LIKE '%" . $search . "%'
                LIMIT 10";
	$result = $dbc->query($sql);
	$json = [];
	while ($row = $result->fetch_assoc()) {
		$getBrand = fetchRecord($dbc, "brands", "brand_id", $row['brand_id']);
		$getCat = fetchRecord($dbc, "categories", "categories_id", $row['category_id']);

		$json[] = ['id' => $row['product_id'], 'text' => $row['product_name'] . " | " . $getBrand["brand_name"] . "(" . $getCat["categories_name"] . ")"];
	}
	echo json_encode($json);
}
if (isset($_REQUEST['getPrice'])) {
	if ($_REQUEST['type'] == "product") {
		$record = fetchRecord($dbc, "product", "product_id", $_REQUEST['getPrice']);
	} else {
		$record = fetchRecord($dbc, "product", "product_code", $_REQUEST['getPrice']);
	}

	// Check if record exists
	if (!$record) {
		echo json_encode([
			"sts" => "error",
			"msg" => "Product not found"
		]);
		exit;
	}

	// Determine price based on type
	if (isset($_REQUEST['credit_sale_type']) && $_REQUEST['credit_sale_type'] == "15days") {
		$price = $record['f_days'];
	} elseif (isset($_REQUEST['credit_sale_type']) && $_REQUEST['credit_sale_type'] == "30days") {
		$price = $record['t_days'];
	} else {
		if ($_REQUEST['payment_type'] == "credit_purchase" || $_REQUEST['payment_type'] == "cash_purchase") {
			$price = $record['purchase_rate'];
		} else {
			$price = @$record['current_rate'];
		}
	}

	// Build and return response
	$response = [
		"current_rate" => @$record['current_rate'],
		"wholesale_rate" => @$record['f_days'],
		"purchase_rate" => @$record['purchase_rate'],
		"qty" => (float) @$record['quantity_instock'],
		"sts" => "success",
		"type" => @$_REQUEST['credit_sale_type'],
		"id" => $record['product_id'],
		"product_code" => $record['product_code'],
		"product_name" => ucwords($record['product_name'])
	];

	echo json_encode($response);
}


/*---------------------- cash sale-order   -------------------------------------------------------------------*/
if (isset($_REQUEST['sale_order_client_name'])) {
	$get_company = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM company ORDER BY id DESC LIMIT 1"));
	if (!empty($_REQUEST['product_ids'])) {
		# code...

		$total_ammount = $total_grand = 0;

		$data = [
			'order_date' => $_REQUEST['order_date'],
			'client_name' => $_REQUEST['sale_order_client_name'],
			'client_contact' => $_REQUEST['client_contact'],
			'paid' => $_REQUEST['paid_ammount'],
			'payment_account' => @$_REQUEST['payment_account'],
			'payment_type' => 'cash_in_hand',
			'vehicle_no' => @$_REQUEST['vehicle_no'],
			'freight' => @$_REQUEST['freight'],
			'user_id' => @$_SESSION['userId'],
			'type' => @$_REQUEST['ratetype']
		];

		if ($_REQUEST['product_order_id'] == "") {

			if (insert_data($dbc, 'orders', $data)) {
				$last_id = mysqli_insert_id($dbc);
				$paidAmount = @(float) $_REQUEST['paid_ammount'];
				if ($paidAmount > 0) {
					$debit = [
						'credit' => @$_REQUEST['paid_ammount'],
						'debit' => 0,
						'customer_id' => @$_REQUEST['payment_account'],
						'transaction_from' => 'invoice',
						'transaction_type' => "cash_in_hand",
						'transaction_remarks' => "cash_sale by order id#" . $last_id,
						'transaction_date' => $_REQUEST['order_date'],
					];
					insert_data($dbc, 'transactions', $debit);
					$transaction_paid_id = mysqli_insert_id($dbc);
				}

				$x = 0;
				foreach ($_REQUEST['product_ids'] as $key => $value) {
					$total = $qty = 0;
					$product_quantites = (float) $_REQUEST['product_quantites'][$x];
					$product_rates = (float) $_REQUEST['product_rates'][$x];
					$total = (float) $product_quantites * $product_rates;
					$total_ammount += (float) $total;
					$order_items = [
						'product_id' => $_REQUEST['product_ids'][$x],
						'rate' => $product_rates,
						'total' => $total,
						'order_id' => $last_id,
						'quantity' => $product_quantites,
						'order_item_status' => 1,
					];
					if ($get_company['stock_manage'] == 1) {
						$product_id = $_REQUEST['product_ids'][$x];
						$quantity_instock = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT quantity_instock FROM  product WHERE product_id='" . $product_id . "' "));
						@$qty = (float) $quantity_instock['quantity_instock'] - (float) $product_quantites;
						$quantity_update = mysqli_query($dbc, "UPDATE product SET  quantity_instock='$qty' WHERE product_id='" . $product_id . "' ");
					}
					insert_data($dbc, 'order_item', $order_items);

					$x++;
				} //end of foreach
				$total_grand = @(float) $_REQUEST['freight'] + $total_ammount - (float) $_REQUEST['ordered_discount'];

				$due_amount = (float) $total_grand - @(float) $_REQUEST['paid_ammount'];

				if ($due_amount > 0) {
					$payment_status = 0; //pending

				} else {
					$payment_status = 1; //completed
				}
				$newOrder = [
					'total_amount' => $total_ammount,
					'discount' => $_REQUEST['ordered_discount'],
					'customer_profit' => @$_REQUEST['total_profit'],
					'grand_total' => $total_grand,
					'payment_status' => $payment_status,
					'due' => $due_amount,
					'order_status' => 1,
					'transaction_paid_id' => @$transaction_paid_id,
				];
				if (update_data($dbc, 'orders', $newOrder, 'order_id', $last_id)) {
					# code...
					//echo "<script>alert('company Updated....!')</script>";
					$msg = "Order Has been Added";
					$sts = 'success';
				} else {
					$msg = mysqli_error($dbc);
					$sts = "danger";
				}
			} else {
				$msg = mysqli_error($dbc);
				$sts = "danger";
			}
		} else {
			if (update_data($dbc, 'orders', $data, 'order_id', $_REQUEST['product_order_id'])) {
				$last_id = $_REQUEST['product_order_id'];
				if ($get_company['stock_manage'] == 1) {
					$proQ = get($dbc, "order_item WHERE order_id='" . $last_id . "' ");

					while ($proR = mysqli_fetch_assoc($proQ)) {
						$newqty = 0;
						$quantity_instock = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT quantity_instock FROM  product WHERE product_id='" . $proR['product_id'] . "' "));
						$newqty = (float) $quantity_instock['quantity_instock'] + (float) $proR['quantity'];
						$quantity_update = mysqli_query($dbc, "UPDATE product SET  quantity_instock='$newqty'  WHERE product_id='" . $proR['product_id'] . "' ");
					}
				}
				deleteFromTable($dbc, "order_item", 'order_id', $_REQUEST['product_order_id']);

				$x = 0;
				foreach ($_REQUEST['product_ids'] as $key => $value) {
					$total = $qty = 0;
					$product_quantites = (float) $_REQUEST['product_quantites'][$x];
					$product_rates = (float) $_REQUEST['product_rates'][$x];
					$total = $product_quantites * $product_rates;
					$total_ammount += (float) $total;
					$order_items = [
						'product_id' => $_REQUEST['product_ids'][$x],
						'rate' => $product_rates,
						'total' => $total,
						'order_id' => $_REQUEST['product_order_id'],
						'quantity' => $product_quantites,
						'order_item_status' => 1,
					];
					if ($get_company['stock_manage'] == 1) {
						$product_id = $_REQUEST['product_ids'][$x];
						$quantity_instock = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT quantity_instock FROM  product WHERE product_id='" . $product_id . "' "));
						$qty = (float) $quantity_instock['quantity_instock'] - $product_quantites;
						$quantity_update = mysqli_query($dbc, "UPDATE product SET  quantity_instock='$qty'   WHERE product_id='" . $product_id . "' ");
					}
					//update_data($dbc,'order_item', $order_items , 'order_id',$_REQUEST['product_order_id']);
					insert_data($dbc, 'order_item', $order_items);

					$x++;
				} //end of foreach
				// $total_grand = @(float)$_REQUEST['freight'] + $total_ammount - $total_ammount * ((float)$_REQUEST['ordered_discount'] / 100);
				$total_grand = @(float) $_REQUEST['freight'] + $total_ammount - (float) $_REQUEST['ordered_discount'];
				$due_amount = (float) $total_grand - @(float) $_REQUEST['paid_ammount'];
				if ($due_amount > 0) {
					$payment_status = 0; //pending

				} else {
					$payment_status = 1; //completed

				}
				$newOrder = [

					'total_amount' => $total_ammount,
					'discount' => $_REQUEST['ordered_discount'],
					'grand_total' => $total_grand,
					'payment_status' => $payment_status,
					'customer_profit' => @$_REQUEST['total_profit'],
					'due' => $due_amount,
				];
				$paidAmount = @(float) $_REQUEST['paid_ammount'];
				if ($paidAmount > 0) {
					$credit1 = [
						'credit' => @$_REQUEST['paid_ammount'],
						'debit' => 0,
						'customer_id' => @$_REQUEST['payment_account'],

					];
					$transactions = fetchRecord($dbc, "orders", "order_id", $_REQUEST['product_order_id']);
					update_data($dbc, "transactions", $credit1, "transaction_id", $transactions['transaction_paid_id']);
				}
				if (update_data($dbc, 'orders', $newOrder, 'order_id', $_REQUEST['product_order_id'])) {
					# code...
					//echo "<script>alert('company Updated....!')</script>";
					$msg = "Data Has been Updated";
					$sts = 'success';
				} else {
					$msg = mysqli_error($dbc);
					$sts = "danger";
				}
			} else {
				$msg = mysqli_error($dbc);
				$sts = "danger";
			}
		}
	} else {
		$msg = "Please Add Any Product";
		$sts = 'error';
	}
	echo json_encode(['msg' => $msg, 'sts' => $sts, 'order_id' => @$last_id, 'type' => "order", 'subtype' => $_REQUEST['payment_type']]);
}
/*---------------------- credit sale-order   -------------------------------------------------------------------*/

/*---------------------- credit sale-return-order   -------------------------------------------------------------------*/
if (isset($_REQUEST['return_sale_order_client_name'])) {
	// print_r($_REQUEST); exit;

	$get_company = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM company ORDER BY id DESC LIMIT 1"));

	if (!empty($_REQUEST['product_ids'])) {
		$sub_total = $grand_total = 0;
		$last_id = null;

		// Prepare main return data
		$data = [
			'return_date' => $_REQUEST['order_date'],
			'client_name' => $_REQUEST['return_sale_order_client_name'],
			'client_contact' => $_REQUEST['client_contact'],
			'refund_amount' => $_REQUEST['returnable_amount'],
			'payment_type' => 'cash_in_hand',
			'user_id' => @$_SESSION['userId'],
			'vehicle_no' => @$_REQUEST['vehicle_no'],
			'discount' => $_REQUEST['ordered_discount'],
			'payment_status' => 0,
			'return_status' => 1,
			'note' => $_REQUEST['note'] ?? '',
			'address' => $_REQUEST['address'] ?? '',
			'original_order_id' => $_REQUEST['product_order_id'],
		];

		if (empty($_REQUEST['return_id'])) {
			// Insert new return
			if (insert_data($dbc, 'returns', $data)) {
				$last_id = mysqli_insert_id($dbc);

				$x = 0;
				foreach ($_REQUEST['product_ids'] as $key => $product_id) {
					$qty = (float) $_REQUEST['return_quantities'][$x];
					$rate = (float) $_REQUEST['product_rates'][$x];
					$total = $qty * $rate;
					$sub_total += $total;

					$item = [
						'return_id' => $last_id,
						'product_id' => $product_id,
						'quantity' => $qty,
						'rate' => $rate,
						'total' => $total,
						'discount' => 0,
						'gauge' => '',
						'width' => '',
						'return_reason' => $_REQUEST['return_reasons'][$x] ?? '',
					];

					insert_data($dbc, 'return_items', $item);

					if ($get_company['stock_manage'] == 1) {
						$existing_stock = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT quantity_instock FROM product WHERE product_id='$product_id'"));
						$new_stock = (float) $existing_stock['quantity_instock'] + $qty;
						mysqli_query($dbc, "UPDATE product SET quantity_instock='$new_stock', current_rate='$rate' WHERE product_id='$product_id'");
					}

					$x++;
				}

				$grand_total = $sub_total - ($sub_total * ((float) $_REQUEST['ordered_discount'] / 100));

				// Update totals
				$update = [
					'sub_total' => $sub_total,
					'grand_total' => $grand_total,
				];
				update_data($dbc, 'returns', $update, 'return_id', $last_id);

				// Refund transaction if needed
				$refundAmount = @(float) $_REQUEST['paid_ammount'];
				if ($refundAmount > 0) {
					$refundTransaction = [
						'credit' => 0,
						'debit' => $refundAmount,
						'customer_id' => @$_REQUEST['payment_account'],
						'transaction_from' => 'return',
						'transaction_type' => 'refund',
						'transaction_remarks' => "Refund for return ID #" . $last_id,
						'transaction_date' => $_REQUEST['order_date'],
					];
					insert_data($dbc, 'transactions', $refundTransaction);
				}

				$msg = "Return has been recorded.";
				$sts = "success";
			} else {
				$msg = mysqli_error($dbc);
				$sts = "danger";
			}
		} else {
			// Update existing return
			$last_id = $_REQUEST['return_id'];

			if (update_data($dbc, 'returns', $data, 'return_id', $last_id)) {
				// Restore previous stock
				if ($get_company['stock_manage'] == 1) {
					$existing_items = get($dbc, "return_items WHERE return_id='$last_id'");
					while ($row = mysqli_fetch_assoc($existing_items)) {
						$product_id = $row['product_id'];
						$qty = $row['quantity'];
						$stock = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT quantity_instock FROM product WHERE product_id='$product_id'"));
						$new_stock = (float) $stock['quantity_instock'] - $qty;
						mysqli_query($dbc, "UPDATE product SET quantity_instock='$new_stock' WHERE product_id='$product_id'");
					}
				}

				// Delete old items
				deleteFromTable($dbc, 'return_items', 'return_id', $last_id);

				// Re-insert updated items
				$x = 0;
				foreach ($_REQUEST['product_ids'] as $key => $product_id) {
					$qty = (float) $_REQUEST['product_quantites'][$x];
					$rate = (float) $_REQUEST['product_rates'][$x];
					$total = $qty * $rate;
					$sub_total += $total;

					$item = [
						'return_id' => $last_id,
						'product_id' => $product_id,
						'quantity' => $qty,
						'rate' => $rate,
						'total' => $total,
						'discount' => 0,
						'gauge' => '',
						'width' => '',
						'return_reason' => $_REQUEST['return_reasons'][$x] ?? '',
					];

					insert_data($dbc, 'return_items', $item);

					if ($get_company['stock_manage'] == 1) {
						$stock = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT quantity_instock FROM product WHERE product_id='$product_id'"));
						$new_stock = (float) $stock['quantity_instock'] + $qty;
						mysqli_query($dbc, "UPDATE product SET quantity_instock='$new_stock', current_rate='$rate' WHERE product_id='$product_id'");
					}

					$x++;
				}

				$grand_total = $sub_total - ($sub_total * ((float) $_REQUEST['ordered_discount'] / 100));

				$update = [
					'sub_total' => $sub_total,
					'grand_total' => $grand_total,
				];
				update_data($dbc, 'returns', $update, 'return_id', $last_id);

				$msg = "Return has been updated.";
				$sts = "success";
			} else {
				$msg = mysqli_error($dbc);
				$sts = "danger";
			}
		}
	} else {
		$msg = "Please add at least one product.";
		$sts = "error";
	}

	echo json_encode([
		'msg' => $msg,
		'sts' => $sts,
		'return_id' => @$last_id,
		'type' => 'return',
		'subtype' => 'cash_in_hand',
	]);
}

/*---------------------- credit sale-return-order   -------------------------------------------------------------------*/
if (isset($_REQUEST['credit_order_client_name'])) {
	$get_company = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM company ORDER BY id DESC LIMIT 1"));
	if (!empty($_REQUEST['product_ids'])) {
		# code...
		$total_ammount = $total_grand = 0;

		$data = [
			'order_date' => $_REQUEST['order_date'],
			'client_name' => $_REQUEST['credit_order_client_name'],
			'client_contact' => $_REQUEST['client_contact'],
			'paid' => $_REQUEST['paid_ammount'],
			'order_narration' => @$_REQUEST['order_narration'],
			'payment_account' => @$_REQUEST['payment_account'],
			'customer_account' => @$_REQUEST['customer_account'],
			'payment_type' => 'credit_sale',
			'credit_sale_type' => @$_REQUEST['credit_sale_type'],
			'vehicle_no' => @$_REQUEST['vehicle_no'],
			'freight' => @$_REQUEST['freight'],
		];
		//'payment_status'=>1,
		if ($_REQUEST['product_order_id'] == "") {

			if (insert_data($dbc, 'orders', $data)) {
				$last_id = mysqli_insert_id($dbc);
				$x = 0;
				foreach ($_REQUEST['product_ids'] as $key => $value) {
					$total = $qty = 0;
					$product_quantites = (float) $_REQUEST['product_quantites'][$x];
					$product_rates = (float) $_REQUEST['product_rates'][$x];
					$total = $product_quantites * $product_rates;
					$total_ammount += (float) $total;
					$order_items = [
						'product_id' => $_REQUEST['product_ids'][$x],
						'rate' => $product_rates,
						'total' => $total,
						'order_id' => $last_id,
						'quantity' => $product_quantites,
						'order_item_status' => 1,
					];

					if ($get_company['stock_manage'] == 1) {
						$product_id = $_REQUEST['product_ids'][$x];
						$quantity_instock = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT quantity_instock FROM  product WHERE product_id='" . $product_id . "' "));
						$qty = (float) $quantity_instock['quantity_instock'] - $product_quantites;
						$quantity_update = mysqli_query($dbc, "UPDATE product SET  quantity_instock='$qty' WHERE product_id='" . $product_id . "' ");
					}
					insert_data($dbc, 'order_item', $order_items);

					$x++;
				} //end of foreach

				$total_grand = @(float) $_REQUEST['freight'] + $total_ammount - $total_ammount * ((float) $_REQUEST['ordered_discount'] / 100);
				$due_amount = (float) $total_grand - @(float) $_REQUEST['paid_ammount'];

				$credit = [
					'credit' => $due_amount,
					'debit' => 0,
					'customer_id' => @$_REQUEST['customer_account'],
					'transaction_from' => 'invoice',
					'transaction_type' => "credit_sale",
					'transaction_remarks' => "credit_sale by order id#" . $last_id,
					'transaction_date' => $_REQUEST['order_date'],
				];
				if ($due_amount > 0) {
					$payment_status = 0; //pending
					insert_data($dbc, 'transactions', $credit);
					$transaction_id = mysqli_insert_id($dbc);
				} else {
					$payment_status = 1; //completed
					$transaction_id = 0;
				}
				$paidAmount = @(float) $_REQUEST['paid_ammount'];
				if ($paidAmount > 0) {
					$credit1 = [
						'credit' => @$_REQUEST['paid_ammount'],
						'debit' => 0,
						'customer_id' => @$_REQUEST['payment_account'],
						'transaction_from' => 'invoice',
						'transaction_type' => "credit_sale",
						'transaction_remarks' => "credit_sale by order id#" . $last_id,
						'transaction_date' => $_REQUEST['order_date'],
					];
					insert_data($dbc, 'transactions', $credit1);
					$transaction_paid_id = mysqli_insert_id($dbc);
				}


				$newOrder = [
					'payment_status' => $payment_status,
					'total_amount' => $total_ammount,
					'discount' => $_REQUEST['ordered_discount'],
					'grand_total' => $total_grand,
					'due' => $due_amount,
					'order_status' => 1,
					'transaction_id' => @$transaction_id,
					'transaction_paid_id' => @$transaction_paid_id,
				];
				if (update_data($dbc, 'orders', $newOrder, 'order_id', $last_id)) {
					# code...
					//echo "<script>alert('company Updated....!')</script>";
					$msg = "Order Has been Added";
					$sts = 'success';
				} else {
					$msg = mysqli_error($dbc);
					$sts = "danger";
				}
			} else {
				$msg = mysqli_error($dbc);
				$sts = "danger";
			}
		} else {
			if (update_data($dbc, 'orders', $data, 'order_id', $_REQUEST['product_order_id'])) {
				$last_id = $_REQUEST['product_order_id'];
				if ($get_company['stock_manage'] == 1) {
					$proQ = get($dbc, "order_item WHERE order_id='" . $last_id . "' ");

					while ($proR = mysqli_fetch_assoc($proQ)) {
						$newqty = 0;
						$quantity_instock = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT quantity_instock FROM  product WHERE product_id='" . $proR['product_id'] . "' "));
						$newqty = (float) $quantity_instock['quantity_instock'] + (float) $proR['quantity'];
						$quantity_update = mysqli_query($dbc, "UPDATE product SET  quantity_instock='$newqty',current_rate = '$product_rates' WHERE product_id='" . $proR['product_id'] . "' ");
					}
				}
				deleteFromTable($dbc, "order_item", 'order_id', $_REQUEST['product_order_id']);

				$x = 0;
				foreach ($_REQUEST['product_ids'] as $key => $value) {
					$total = $qty = 0;
					$product_quantites = (float) $_REQUEST['product_quantites'][$x];
					$product_rates = (float) $_REQUEST['product_rates'][$x];
					$total = $product_quantites * $product_rates;
					$total_ammount += (float) $total;
					$order_items = [
						'product_id' => $_REQUEST['product_ids'][$x],
						'rate' => $product_rates,
						'total' => $total,
						'order_id' => $_REQUEST['product_order_id'],
						'quantity' => $product_quantites,
						'order_item_status' => 1,
					];
					if ($get_company['stock_manage'] == 1) {
						$product_id = $_REQUEST['product_ids'][$x];
						$quantity_instock = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT quantity_instock FROM  product WHERE product_id='" . $product_id . "' "));
						$qty = (float) $quantity_instock['quantity_instock'] - $product_quantites;
						$quantity_update = mysqli_query($dbc, "UPDATE product SET  quantity_instock='$qty',current_rate = '$product_rates' WHERE product_id='" . $product_id . "' ");
					}
					insert_data($dbc, 'order_item', $order_items);

					$x++;
				} //end of foreach
				$total_grand = @(float) $_REQUEST['freight'] + $total_ammount - $total_ammount * ((float) $_REQUEST['ordered_discount'] / 100);
				$due_amount = (float) $total_grand - @(float) $_REQUEST['paid_ammount'];

				$transactions = fetchRecord($dbc, "orders", "order_id", $_REQUEST['product_order_id']);
				@deleteFromTable($dbc, "transactions", 'transaction_id', $transactions['transaction_id']);
				@deleteFromTable($dbc, "transactions", 'transaction_id', $transactions['transaction_paid_id']);

				$credit = [
					'credit' => $due_amount,
					'debit' => 0,
					'customer_id' => @$_REQUEST['customer_account'],
					'transaction_from' => 'invoice',
					'transaction_type' => "credit_sale",
					'transaction_remarks' => "credit_sale by order id#" . $last_id,
					'transaction_date' => $_REQUEST['order_date'],
				];
				if ($due_amount > 0) {
					$payment_status = 0; //pending
					insert_data($dbc, 'transactions', $credit);
					$transaction_id = mysqli_insert_id($dbc);
				} else {
					$payment_status = 1; //completed
					$transaction_id = 0;
				}
				$paidAmount = @(float) $_REQUEST['paid_ammount'];
				if ($paidAmount > 0) {
					$credit1 = [
						'credit' => @$_REQUEST['paid_ammount'],
						'debit' => 0,
						'customer_id' => @$_REQUEST['payment_account'],
						'transaction_from' => 'invoice',
						'transaction_type' => "credit_sale",
						'transaction_remarks' => "credit_sale by order id#" . $last_id,
						'transaction_date' => $_REQUEST['order_date'],
					];
					insert_data($dbc, 'transactions', $credit1);
					$transaction_paid_id = mysqli_insert_id($dbc);
				}

				$newOrder = [
					'payment_status' => $payment_status,
					'total_amount' => $total_ammount,
					'discount' => $_REQUEST['ordered_discount'],
					'grand_total' => $total_grand,
					'due' => $due_amount,
					'transaction_id' => @$transaction_id,
					'transaction_paid_id' => @$transaction_paid_id,
				];


				if (update_data($dbc, 'orders', $newOrder, 'order_id', $_REQUEST['product_order_id'])) {
					# code...
					//echo "<script>alert('company Updated....!')</script>";
					$msg = "Data Has been Updated";
					$sts = 'success';
				} else {
					$msg = mysqli_error($dbc);
					$sts = "danger";
				}
			} else {
				$msg = mysqli_error($dbc);
				$sts = "danger";
			}
		}
	} else {
		$msg = "Please Add Any Product";
		$sts = 'error';
	}
	echo json_encode(['msg' => $msg, 'sts' => $sts, 'order_id' => @$last_id, 'type' => "order", 'subtype' => $_REQUEST['payment_type']]);
}

if (isset($_REQUEST['getProductPills'])) {
	$q = mysqli_query($dbc, "SELECT * FROM product WHERE brand_id='" . $_REQUEST['getProductPills'] . "' ");
	if (mysqli_num_rows($q) > 0) {
		while ($r = mysqli_fetch_assoc($q)) {
			echo '<li class="nav-item text-capitalize"  ><button type="button" onclick="addProductOrder(' . $r["product_id"] . ',' . $r["quantity_instock"] . ',`plus`)" class="btn btn-primary  m-1 ">' . $r["product_name"] . '</button></li>';
		}
	} else {
		echo '<li class="nav-item text-capitalize ">No Product Has Been Added</li>';
	}
}
if (isset($_REQUEST['getCustomer_name'])) {
	$q = mysqli_query($dbc, "SELECT DISTINCT client_name FROM  orders WHERE client_contact='" . $_REQUEST['getCustomer_name'] . "' ");
	if (mysqli_num_rows($q) > 0) {
		$r = mysqli_fetch_assoc($q);
		echo $r['client_name'];
	} else {
		echo '';
	}
}
if (isset($_REQUEST['getProductDetails'])) {
	$product = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT  product.*,brands.* FROM product INNER JOIN brands ON product.brand_id=brands.brand_id   WHERE product.product_id='" . $_REQUEST['getProductDetails'] . "' AND product.status=1  "));
	echo json_encode($product);
}
if (isset($_REQUEST['getProductDetailsBycode'])) {
	$product = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT  product.*,brands.* FROM product INNER JOIN brands ON product.brand_id=brands.brand_id   WHERE product.product_code='" . $_REQUEST['getProductDetailsBycode'] . "' AND product.status=1  "));
	echo json_encode($product);
}
/*---------------------- cash purchase   -------------------------------------------------------------------*/
if (isset($_REQUEST['cash_purchase_supplier'])) {


	// print_r($_REQUEST);
	// exit;
	if (!empty($_REQUEST['product_ids'])) {
		# code...
		$total_ammount = $total_grand = 0;
		$get_company = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM company ORDER BY id DESC LIMIT 1"));

		$data = [
			'purchase_date' => $_REQUEST['purchase_date'],
			'supplier_bill_number' => @$_REQUEST['supplier_bill_number'],
			'client_name' => @$_REQUEST['cash_purchase_supplier'],
			'client_contact' => @$_REQUEST['client_contact'],
			'purchase_narration' => @$_REQUEST['purchase_narration'],
			'payment_account' => @$_REQUEST['payment_account'],
			'customer_account' => @$_REQUEST['customer_account'],
			'paid' => $_REQUEST['paid_ammount'],
			'payment_status' => 1,
			'payment_type' => $_REQUEST['payment_type'],
			'user_id' => @$_SESSION['userId']
		];

		if ($_REQUEST['product_purchase_id'] == "") {

			if (insert_data($dbc, 'purchase', $data)) {
				$last_id = mysqli_insert_id($dbc);

				$x = 0;
				foreach ($_REQUEST['product_ids'] as $key => $value) {
					$total = $qty = 0;
					$product_quantites = (float) $_REQUEST['product_quantites'][$x];
					$product_rates = (float) $_REQUEST['product_rates'][$x];
					$product_Crates = (float) $_REQUEST['get_sale_price'][$x];
					$total = (float) $product_quantites * $product_rates;
					$total_ammount += (float) $total;
					$product_id = $_REQUEST['product_ids'][$x];
					// print_r($product_rates);
					// exit;
					$order_items = [
						'product_id' => $_REQUEST['product_ids'][$x],
						'rate' => $product_rates,
						'total' => $total,
						'purchase_id' => $last_id,
						'quantity' => $product_quantites,
						'purchase_item_status' => 1,
					];

					insert_data($dbc, 'purchase_item', $order_items);
					mysqli_query($dbc, "UPDATE product SET  current_rate='$product_Crates',purchase_rate='$product_rates' WHERE product_id='" . $product_id . "' ");

					if ($get_company['stock_manage'] == 1) {

						$stock_row = mysqli_fetch_assoc(
							mysqli_query($dbc, "SELECT quantity_instock, total_stock FROM product 
                            WHERE product_id='" . $product_id . "' ")
						);
						$new_instock = (float) $stock_row['quantity_instock'] + $product_quantites;
						$new_total = (float) $stock_row['total_stock'] + $product_quantites;
						// update both quantity_instock and total_stock
						$quantity_update = mysqli_query($dbc, "UPDATE product SET quantity_instock='$new_instock',total_stock='$new_total'
                                                                              WHERE product_id='" . $product_id . "' 
    													");
					}



					$x++;
				} //end of foreach
				// $total_grand = $total_ammount - $total_ammount * ((float)$_REQUEST['ordered_discount'] / 100);
				$total_grand = $total_ammount - (float) $_REQUEST['ordered_discount'];


				$due_amount = (float) $total_grand - @(float) $_REQUEST['paid_ammount'];
				if ($_REQUEST['payment_type'] == "credit_purchase"):
					if ($due_amount > 0) {
						$debit = [
							'debit' => $due_amount,
							'credit' => 0,
							'customer_id' => @$_REQUEST['customer_account'],
							'transaction_from' => 'purchase',
							'transaction_type' => $_REQUEST['payment_type'],
							'transaction_remarks' => "purchased on  purchased id#" . $last_id,
							'transaction_date' => $_REQUEST['purchase_date'],
						];
						insert_data($dbc, 'transactions', $debit);
						$transaction_id = mysqli_insert_id($dbc);
					}
				endif;
				$paidAmount = @(float) $_REQUEST['paid_ammount'];
				if ($paidAmount > 0) {
					$credit = [
						'debit' => @$_REQUEST['paid_ammount'],
						'credit' => 0,
						'customer_id' => @$_REQUEST['payment_account'],
						'transaction_from' => 'purchase',
						'transaction_type' => $_REQUEST['payment_type'],
						'transaction_remarks' => "purchased by purchased id#" . $last_id,
						'transaction_date' => $_REQUEST['purchase_date'],
					];
					insert_data($dbc, 'transactions', $credit);
					$transaction_paid_id = mysqli_insert_id($dbc);
				}

				$newOrder = [

					'total_amount' => $total_ammount,
					'discount' => $_REQUEST['ordered_discount'],
					'grand_total' => $total_grand,
					'due' => $due_amount,
					'transaction_paid_id' => @$transaction_paid_id,
					'transaction_id' => @$transaction_id,
				];
				if (update_data($dbc, 'purchase', $newOrder, 'purchase_id', $last_id)) {
					# code...
					//echo "<script>alert('company Updated....!')</script>";
					$msg = "Purchase Has been Added";
					$sts = 'success';
				} else {
					$msg = mysqli_error($dbc);
					$sts = "danger";
				}
			} else {
				$msg = mysqli_error($dbc);
				$sts = "danger";
			}
		} else {
			if (update_data($dbc, 'purchase', $data, 'purchase_id', $_REQUEST['product_purchase_id'])) {
				$last_id = $_REQUEST['product_purchase_id'];


				if ($get_company['stock_manage'] == 1) {
					$proQ = get($dbc, "purchase_item WHERE purchase_id='" . $last_id . "' ");

					while ($proR = mysqli_fetch_assoc($proQ)) {
						$newqty = 0;
						$quantity_instock = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT quantity_instock,total_stock FROM  product WHERE product_id='" . $proR['product_id'] . "' "));
						$new_instock = (float) $quantity_instock['quantity_instock'] - (float) $proR['quantity'];
						$new_total = (float) $quantity_instock['total_stock'] - (float) $proR['quantity'];
						$quantity_update = mysqli_query($dbc, "UPDATE product SET  quantity_instock='$new_instock',total_stock='$new_total' WHERE product_id='" . $proR['product_id'] . "' ");
					}
				}
				deleteFromTable($dbc, "purchase_item", 'purchase_id', $_REQUEST['product_purchase_id']);
				$x = 0;
				foreach ($_REQUEST['product_ids'] as $key => $value) {


					$total = $qty = 0;
					$product_quantites = (float) $_REQUEST['product_quantites'][$x];
					$product_rates = (float) $_REQUEST['product_rates'][$x];
					$total = $product_quantites * $product_rates;
					$total_ammount += (float) $total;
					$purchase_item = [
						'product_id' => $_REQUEST['product_ids'][$x],
						'rate' => $product_rates,
						'total' => $total,
						'purchase_id' => $_REQUEST['product_purchase_id'],
						'quantity' => $product_quantites,
						'purchase_item_status' => 1,
					];

					//update_data($dbc,'order_item', $order_items , 'purchase_id',$_REQUEST['product_purchase_id']);
					insert_data($dbc, 'purchase_item', $purchase_item);

					if ($get_company['stock_manage'] == 1) {
						$product_id = $_REQUEST['product_ids'][$x];
						$quantity_instock = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT quantity_instock,total_stock FROM  product WHERE product_id='" . $product_id . "' "));
						$new_instock = (float) $quantity_instock['quantity_instock'] + $product_quantites;
						$new_total = (float) $quantity_instock['total_stock'] + $product_quantites;
						$quantity_update = mysqli_query($dbc, "UPDATE product SET  quantity_instock='$new_instock',total_stock='$new_total' WHERE product_id='" . $product_id . "' ");
					}

					$x++;
				} //end of foreach
				// $total_grand = $total_ammount - $total_ammount * ((float)$_REQUEST['ordered_discount'] / 100);
				$total_grand = $total_ammount - (float) $_REQUEST['ordered_discount'];

				$due_amount = (float) $total_grand - @(float) $_REQUEST['paid_ammount'];


				$transactions = fetchRecord($dbc, "purchase", "purchase_id", $_REQUEST['product_purchase_id']);
				@deleteFromTable($dbc, "transactions", 'transaction_id', $transactions['transaction_id']);
				@deleteFromTable($dbc, "transactions", 'transaction_id', $transactions['transaction_paid_id']);


				if ($_REQUEST['payment_type'] == "credit_purchase"):
					if ($due_amount > 0) {
						$debit = [
							'debit' => $due_amount,
							'credit' => 0,
							'customer_id' => @$_REQUEST['customer_account'],
							'transaction_from' => 'purchase',
							'transaction_type' => $_REQUEST['payment_type'],
							'transaction_remarks' => "purchased on  purchased id#" . $last_id,
							'transaction_date' => $_REQUEST['purchase_date'],
						];
						insert_data($dbc, 'transactions', $debit);
						$transaction_id = mysqli_insert_id($dbc);
					}
				endif;
				$paidAmount = @(float) $_REQUEST['paid_ammount'];
				if ($paidAmount > 0) {
					$credit = [
						'debit' => @$_REQUEST['paid_ammount'],
						'credit' => 0,
						'customer_id' => @$_REQUEST['payment_account'],
						'transaction_from' => 'purchase',
						'transaction_type' => $_REQUEST['payment_type'],
						'transaction_remarks' => "purchased by purchased id#" . $last_id,
						'transaction_date' => $_REQUEST['purchase_date'],
					];
					insert_data($dbc, 'transactions', $credit);
					$transaction_paid_id = mysqli_insert_id($dbc);
				}

				$newOrder = [

					'total_amount' => $total_ammount,
					'discount' => $_REQUEST['ordered_discount'],
					'grand_total' => $total_grand,
					'due' => $due_amount,
					'transaction_paid_id' => @$transaction_paid_id,
					'transaction_id' => @$transaction_id,
				];

				if (update_data($dbc, 'purchase', $newOrder, 'purchase_id', $_REQUEST['product_purchase_id'])) {
					# code...
					//echo "<script>alert('company Updated....!')</script>";
					$msg = "Purchase Has been Updated";
					$sts = 'success';
				} else {
					$msg = mysqli_error($dbc);
					$sts = "danger";
				}
			} else {
				$msg = mysqli_error($dbc);
				$sts = "danger";
			}
		}
	} else {
		$msg = "Please Add Any Product";
		$sts = 'error';
	}
	echo json_encode(['msg' => $msg, 'sts' => $sts, 'order_id' => @$last_id, 'type' => "purchase", 'subtype' => $_REQUEST['payment_type']]);
}
/*---------------------- credit Purchase-order  end -------------------------------------------------------------------*/
if (isset($_REQUEST['get_products_code'])) {
	$q = mysqli_query($dbc, "SELECT *  FROM product WHERE product_code='" . $_REQUEST['get_products_code'] . "' AND status=1 ");
	if (mysqli_num_rows($q) > 0) {
		$r = mysqli_fetch_assoc($q);
		$response = [
			"msg" => "This Product Barcode Already Assign to " . $r['product_name'],
			"product_id" => base64_encode($r['product_id']),
			"sts" => "error",
		];
	} else {
		$response = [
			"msg" => "",
			"sts" => "success"
		];
	}
	echo json_encode($response);
}
if (isset($_REQUEST['getBalance'])) {
	$from_balance = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT SUM(credit-debit) AS from_balance FROM transactions WHERE customer_id='" . $_REQUEST['getBalance'] . "'"));
	if (!empty($from_balance['from_balance'])) {
		echo $from_balance['from_balance'];
	} else {
		echo 0;
	}
}
if (isset($_REQUEST['pending_bills_detils'])) {
	$pending_bills_detils = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM orders WHERE order_id='" . base64_decode($_REQUEST['pending_bills_detils']) . "'"));
	echo json_encode($pending_bills_detils);
}
if (isset($_REQUEST['getBarcode'])) {

	do {
		$barcode = rand('111', '9999');
		$barcodeQ = mysqli_num_rows(mysqli_query($dbc, "SELECT * FROM product WHERE product_code='" . $barcode . "'"));
	} while ($barcodeQ > 0);
	echo json_encode($barcode);
}
if (isset($_REQUEST['add_expense_name'])) {
	$data_array = [
		'expense_name' => $_REQUEST['add_expense_name'],
		'expense_status' => $_REQUEST['expense_status'],
	];
	if ($_REQUEST['expense_id'] == '') {
		if (insert_data($dbc, "expenses", $data_array)) {
			# code...
			$response = [
				"msg" => "expense Added successfully",
				"sts" => "success"
			];
		} else {
			$response = [
				"msg" => mysqli_error($dbc),
				"sts" => "danger"
			];
		}
	} else {
		if (update_data($dbc, "expenses", $data_array, "expense_id", $_REQUEST['expense_id'])) {
			# code...
			$response = [
				"msg" => "expense Updated successfully",
				"sts" => "success"
			];
		} else {
			$response = [
				"msg" => mysqli_error($dbc),
				"sts" => "error"
			];
		}
	}
	echo json_encode($response);
}

if (isset($_REQUEST['setAmountPaid'])) {
	$newOrder = [
		'payment_status' => 1,
		'paid' => $_REQUEST['paid'],
		'due' => 0,
	];
	if (update_data($dbc, 'orders', $newOrder, 'order_id', $_REQUEST['setAmountPaid'])) {

		$response = [
			'msg' => "Amount Has been Paid",
			'sts' => 'success'
		];
	} else {
		$response = [
			'msg' => mysqli_error($dbc),
			'sts' => 'error'
		];
	}
	echo json_encode($response);
}
if (isset($_REQUEST['setCheckStatus'])) {
	$newStat = [
		'check_status' => $_REQUEST['status'],
	];
	if (update_data($dbc, 'checks', $newStat, 'check_id', $_REQUEST['setCheckStatus'])) {

		$response = [
			'msg' => "Action Has been Perform Successfully",
			'sts' => 'success'
		];
	} else {
		$response = [
			'msg' => mysqli_error($dbc),
			'sts' => 'error'
		];
	}
	echo json_encode($response);
}
if (isset($_REQUEST['bill_customer_name'])) {
	$paidAmount = (float) $_REQUEST['bill_paid_ammount'] + (float) $_REQUEST['bill_paid'];


	if ($paidAmount > 0) {
		$transactions = fetchRecord($dbc, "orders", "order_id", $_REQUEST['order_id']);
		$order_date = date('Y-m-d');
		if ($transactions['transaction_paid_id'] > 0) {
			$credit1 = [
				'credit' => @$paidAmount,
				'debit' => 0,
				'customer_id' => @$_REQUEST['bill_payment_account'],
			];

			update_data($dbc, "transactions", $credit1, "transaction_id", $transactions['transaction_paid_id']);
			$transaction_paid_id = $transactions['transaction_paid_id'];
		} else {
			$credit1 = [
				'credit' => @$paidAmount,
				'debit' => 0,
				'customer_id' => @$_REQUEST['bill_payment_account'],
				'transaction_from' => 'invoice',
				'transaction_type' => "cash_in_hand",
				'transaction_remarks' => "cash_sale by order id#" . $_REQUEST['order_id'],
				'transaction_date' => $order_date,
			];
			insert_data($dbc, 'transactions', $credit1);
			$transaction_paid_id = mysqli_insert_id($dbc);
		}
	}
	$due_amount = (float) $_REQUEST['bill_grand_total'] - $paidAmount;
	if ($due_amount > 0) {
		$payment_status = 0; //pending
	} else {
		$payment_status = 1; //completed
	}
	$newOrder = [
		'payment_status' => $payment_status,
		'paid' => $paidAmount,
		'due' => $due_amount,
		'transaction_paid_id' => $transaction_paid_id,
	];
	if (update_data($dbc, 'orders', $newOrder, 'order_id', $_REQUEST['order_id'])) {

		$response = [
			'msg' => "Amount Has been Paid",
			'sts' => 'success'
		];
	} else {
		$response = [
			'msg' => mysqli_error($dbc),
			'sts' => 'error'
		];
	}
	echo json_encode($response);
}
