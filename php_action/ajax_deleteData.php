<?php 
include_once 'db_connect.php';
require_once '../includes/functions.php';
	 /*Delete*/
 if (!empty($_REQUEST['delete_id'])) {
 	# code...
 	$id=$_REQUEST['delete_id'];
 	$table=$_REQUEST['table'];
 	$fld=$_REQUEST['fld'];
 	if(mysqli_query($dbc,"DELETE FROM $table WHERE $fld='$id'")){
 		$msg = "Data Has been deleted...";
 		$sts ="success";
 	}else{
 			$msg = mysqli_error($dbc);
 			$sts = "danger";
 	}
 	echo json_encode(['msg'=>$msg,"sts"=>$sts]);
 }
 if (isset($_REQUEST['delete_bymanually'])) {
 	# code...
 	$id=$_REQUEST['delete_bymanually'];
 	$table=$_REQUEST['table'];
 	$row=$_REQUEST['row'];
 	
 	if ($table=="vouchers") {
 		$vouchers=fetchRecord($dbc,"vouchers", "voucher_id", $id);
 		@deleteFromTable($dbc,"transactions",'transaction_id',$vouchers['transaction_id1']);
 		@deleteFromTable($dbc,"transactions",'transaction_id',$vouchers['transaction_id2']);
 		if(mysqli_query($dbc,"DELETE FROM vouchers WHERE voucher_id='$id'")){
 			$msg = "Data Has been deleted...";
 			$sts ="success";
 		}else{
 			$msg = mysqli_error($dbc);
 			$sts = "error";
 		}

 	}elseif ($table=="orders") {
 		$orders=fetchRecord($dbc,'orders', $row, $id);
 		$get_company =mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM company ORDER BY id DESC LIMIT 1"));

 		if ($get_company['stock_manage']==1) {
						$proQ=get($dbc,"order_item WHERE order_id='".$id."' ");

						while ($proR=mysqli_fetch_assoc($proQ)) {
							$newqty=0;
							$quantity_instock=mysqli_fetch_assoc(mysqli_query($dbc,"SELECT quantity_instock FROM  product WHERE product_id='".$proR['product_id']."' "));
							$newqty=(int)$quantity_instock['quantity_instock']+(int)$proR['quantity'];
							$quantity_update=mysqli_query($dbc,"UPDATE product SET  quantity_instock='$newqty' WHERE product_id='".$proR['product_id']."' ");
					

						}
					}
 		deleteFromTable($dbc,"transactions",'transaction_id',$orders['transaction_paid_id']);
 		deleteFromTable($dbc,"transactions",'transaction_id',$orders['transaction_id']);
 		if(mysqli_query($dbc,"DELETE FROM orders WHERE $row='$id'")){
 			$msg = "Data Has been deleted...";
 			$sts ="success";
 		}else{
 			$msg = mysqli_error($dbc);
 			$sts = "error";
 		}

 	}elseif ($table=="purchase") {

 		$get_company =mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM company ORDER BY id DESC LIMIT 1"));

 		if ($get_company['stock_manage']==1) {
						$proQ=get($dbc,"purchase_item WHERE purchase_id='".$id."' ");

						while ($proR=mysqli_fetch_assoc($proQ)) {
							$newqty=0;
							$quantity_instock=mysqli_fetch_assoc(mysqli_query($dbc,"SELECT quantity_instock FROM  product WHERE product_id='".$proR['product_id']."' "));
							$newqty=(int)$quantity_instock['quantity_instock']-(int)$proR['quantity'];
							$quantity_update=mysqli_query($dbc,"UPDATE product SET  quantity_instock='$newqty' WHERE product_id='".$proR['product_id']."' ");
					

						}
					}
 		$vouchers=fetchRecord($dbc,'purchase', $row, $id);
 		@deleteFromTable($dbc,"transactions",'transaction_id',$vouchers['transaction_paid_id']);
 		@deleteFromTable($dbc,"transactions",'transaction_id',$vouchers['transaction_id']);
 		if(mysqli_query($dbc,"DELETE FROM purchase WHERE $row='$id'")){
 			$msg = "Data Has been deleted...";
 			$sts ="success";
 		}else{
 			$msg = mysqli_error($dbc);
 			$sts = "error";
 		}

 	}elseif ($table=="product") {
 		if(mysqli_query($dbc,"UPDATE product SET status=0 WHERE $row='$id'")){
 			$msg = "Product Has been deleted...";
 			$sts ="success";
 		}else{
 			$msg = mysqli_error($dbc);
 			$sts = "error";
 		}
 	}elseif ($table=="production") {
 	    $id = $_REQUEST['delete_bymanually'];
 	    $row = $_REQUEST['row'];
 	   $production_details = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM production WHERE production_id = '$id'"));
 	   $product1 = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM product WHERE product_id = '$production_details[product_id1]'"));
 	    $product2 = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM product WHERE product_id = '$production_details[product_id2]'"));
 	    
 	    $p1stock = $product1['quantity_instock'] + $production_details['quantity1']; 
 	    
 	    $p2stock = $product2['quantity_instock'] - $production_details['quantity2']; 
 	    
 	    
 	    $managepro1stock = mysqli_query($dbc,"UPDATE product SET quantity_instock = '$p1stock' WHERE product_id = '$product1[product_id]' ");
 	     $managepro2stock = mysqli_query($dbc,"UPDATE product SET quantity_instock = '$p2stock' WHERE product_id = '$product2[product_id]' ");
 	     
 	     if($managepro1stock ){
 	         
 	         
 	         mysqli_query($dbc,"UPDATE production SET production_status = '0' WHERE production_id = '$id'");
 	         
 	        	$msg = "Production Has been deleted...";
 			    $sts ="success";
 	         
 	     }else{
 	         $msg = mysqli_error($dbc);
 			$sts = "error";
 	     }
 	    
 	    
 	    
 	   
 	   
 	   
 	}
 	else{
 		if(deleteFromTable($dbc,$table,$row,$id)){
 			$msg = $table." Has been deleted...";
 			$sts ="success";
 		}else{
 			$msg = mysqli_error($dbc);
 			$sts = "error";
 		}
 	}


 	echo json_encode(['msg'=>$msg,"sts"=>$sts]);
 }
  ?>
