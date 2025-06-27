<!DOCTYPE html>
<html lang="en">
<?php include_once 'includes/head.php'; 

if (!empty($_REQUEST['edit_order_id'])) {
    # code...
    $fetchOrder=fetchRecord($dbc,"orders","order_id",base64_decode($_REQUEST['edit_order_id']));
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
                <b class="text-center card-text">Production</b>
           
             
                 <a href="cash_sale.php" class="btn btn-admin float-right btn-sm">Add New</a>
              </div>
            </div>
  
          </div>
           <div class="card-body">
            <form action="php_action/custom_action.php" method="POST" id="sale_order_fm">
              <input type="hidden" name="product_order_id" value="<?=@empty($_REQUEST['edit_order_id'])?"":base64_decode($_REQUEST['edit_order_id'])?>">
              <!--<input type="hidden" name="payment_type" id="payment_type" value="cash_in_hand">-->

            <div class="row form-group">
                <div class="col-md-4">
                <label>Production ID#</label>
                <?php $result = mysqli_query($dbc,"
    SHOW TABLE STATUS LIKE 'orders'
");
$data = mysqli_fetch_assoc($result);
$next_increment = $data['Auto_increment']; ?>
                <input type="text" name="next_increment" id="next_increment" value="<?=@empty($_REQUEST['production_id'])?$next_increment:$fetchOrder['order_id']?>" readonly class="form-control">
              </div>
              <div class="col-md-4">
                <label>Production Date</label>
              
                <input type="text" name="production_date" id="production_date" value="<?=@empty($_REQUEST['production_date'])?date('Y-m-d'):$fetchOrder['order_date']?>" readonly class="form-control">
              </div>
                      
                 
                   
                   <div class="col-sm-4">
                     <label>Note </label>
                      <input type="text" id="production_note" value="<?=@$fetchOrder['production_note']?>" class="form-control" autocomplete="off" name="production_note"  >
                    
                   </div>
              </div> <!-- end of form-group -->
              
            <div class="row">
                <div class="col-6 col-md-4">
                      <label>Product Code</label>
                      <input type="text"  name="product_code" autocomplete="off" id="get_product_code" class="form-control">
                </div>
                <div class="col-6 col-md-4">
                        <label>Products</label>
                        <input type="hidden" id="add_pro_type" value="add">  
                         <select class="form-control searchableSelect" id="get_product_name"  name="product_id1"   >
                              <option value=" ">Select Product</option>
                          <?php
                            $result=mysqli_query($dbc,"SELECT * FROM product WHERE status=1 ORDER BY product_name ASC");
                             while($row=mysqli_fetch_array($result)){ 
                             $getBrand=fetchRecord($dbc,"brands","brand_id",$row['brand_id']);
                             $getCat=fetchRecord($dbc,"categories","categories_id",$row['category_id']);
                          ?>
                    
                      <option data-price="<?=$row["current_rate"]?>"  <?=empty($r['product_id'])?"":"selected"?>  value="<?=$row["product_id"]?>"  >
                        <?=$row["product_name"]?> |  <?=@$getBrand["brand_name"]?>(<?=@$getCat["categories_name"]?>) </option>

                      <?php   } ?>
                  </select>
                  <span  class="text-center w-100" id="instockQty"></span>
                      </div>
                      
                      
                      <div class="col-6 col-md-4">
                      <label> Quantity  OUT </label>
                      <input type="text"   name="quantity1" autocomplete="off" id="quantity" class="form-control">
                </div>
                
            </div>
            
            <hr/>
             <h3>Production Items</h3>
              <hr/>
            <div class="row">
               
                <div class="col-6 col-md-4">
                      <label>Product Code</label>
                      <input type="text"  name="product_code" autocomplete="off" id="getprocode" class="form-control " onkeyup="getproinfo(value)">
                </div>
                <div class="col-6 col-md-4">
                        <label>Products</label>
                        <input type="hidden" id="add_pro_type" value="add">  
                         <select class="form-control searchableSelect get_product_name1" id="get_product_name1"  name="product_id2"   >
                              <option value=" ">Select Product</option>
                          <?php
                            $result=mysqli_query($dbc,"SELECT * FROM product WHERE status=1 ORDER BY product_name ASC");
                             while($row=mysqli_fetch_array($result)){ 
                             $getBrand=fetchRecord($dbc,"brands","brand_id",$row['brand_id']);
                             $getCat=fetchRecord($dbc,"categories","categories_id",$row['category_id']);
                          ?>
                    
                      <option data-price="<?=$row["current_rate"]?>"  <?=empty($r['product_id'])?"":"selected"?>  value="<?=$row["product_id"]?>"  >
                        <?=$row["product_name"]?> |  <?=@$getBrand["brand_name"]?>(<?=@$getCat["categories_name"]?>) </option>

                      <?php   } ?>
                  </select>
                  <span  class="text-center w-100 instockQty1" id="instockQty1"></span>
                      </div>
                      
                      
                      <div class="col-6 col-md-4">
                      <label> Quantity IN </label>
                      <input type="text"   name="quantity2" autocomplete="off" id="quantity" class="form-control">
                </div>
                
            </div>
              
              
              
              
                <div class="row">
                  <div class="col-sm-6 offset-6">
                    
                  <button class="btn btn-admin float-right " name="sale_order_btn" value="print" type="submit" id="sale_order_btn">Save</button>
                
                  </div>
                </div>
            </form>
           </div>
          </div> <!-- .row -->
          <hr/>
          <!--second card-->
          
          <div class="card">
            <div class="card-header card-bg" align="center">

            <div class="row">
              <div class="col-12 mx-auto h4">
                <b class="text-center card-text">Production List</b>
           
             
                 
              </div>
            </div>
  
          </div>
           <div class="card-body">
               	<table class="table dataTable" id="tableData">
               	    <thead>
               	        <tr>
               	            <th>Production ID</th>
               	            <th>Date</th>
               	            <th>Product 1</th>
               	            <th>Quantity Out</th>
               	            <th>Product 2</th>
               	            <th>Quantity IN</th>
               	            <th>Action</th>
               	        </tr>
               	    </thead>
               	    
               	    <tbody>
               	<?php
               	$q = mysqli_query($dbc,"SELECT * FROM production ORDER BY production_id DESC LIMIT 50");
               	while($r = mysqli_fetch_assoc($q)):
               	    
               	    $p1 = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM product WHERE product_id = $r[product_id1]"));
               	    $p2 = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM product WHERE product_id = $r[product_id2]"));
               	
               	?>        
               	        
               	        
               	        <tr>
               	             <th><?=$r['production_id']?></th>
               	            <th><?=$r['production_date']?></th>
               	            <th><?=$p1['product_name']?> </th>
               	            <th><?=$r['quantity1']?></th>
               	            <th><?=$p2['product_name']?> </th>
               	            <th> <?=$r['quantity2']?></th>
               	            <th>
               	                
               	            <?php
               	            if($r['production_status'] == '1'){
               	            ?>    
               	                 <a href="print_barcode.php?id=<?=base64_encode($r['product_id2'])?>" class="btn btn-primary btn-sm">Barcode</a>
               	                 <button type="button" onclick="deleteAlert('<?=$r['production_id']?>','production','production_id','tableData')" class="btn btn-admin2 btn-sm  d-inline-block" >Delete</button>
               	            <?php
               	            }else{
               	            ?>    
               	            <button class="btn btn-danger">
               	                Deleted Stock Reversed 
               	            </button>
               	            <?php
               	            }
               	            ?>
               	            </th>
               	        </tr>
               	        
               <?php
               endwhile;
               ?>	        
               	    </tbody>
               	</table>
           </div>
          
            </div>
             
          
          <!-second card end--->
          
        </div> <!-- .container-fluid -->
       
     
    </div> <!-- .wrapper -->
    
  </body>
</html>
<?php include_once 'includes/foot.php'; ?>


<script>
    $('body').on('keydown', 'input, select', function(e) {
    if (e.key === "Enter") {
        var self = $(this), form = self.parents('form:eq(0)'), focusable, next;
        focusable = form.find('input,a,select,button,textarea').filter(':visible');
        next = focusable.eq(focusable.index(this)+1);
        if (next.length) {
            next.focus();
        } else {
            form.submit();
        }
        return false;
    }
});

function getproinfo(value){
 
    var code=  $("#getprocode").val();
         var credit_sale_type=  $('#credit_sale_type').val();
         var payment_type=  $('#payment_type').val();
   $.ajax({
            type: 'POST',
            url: 'php_action/custom_action.php',
            data: {get_products_list:code,type:"code"},
            dataType:"text",
            success:function (msg) {
              var res=msg.trim();
               $("#get_product_name1").empty().html(res);
                
            }
        });//ajax call }
         $.ajax({
            type: 'POST',
            url: 'php_action/custom_action.php',
            data: {getPrice:code,type:"code",credit_sale_type:credit_sale_type,payment_type:payment_type},
            dataType:"json",
            success:function (response) {
                
               
               $("#instockQty1").html("instock :"+response.qty);
               console.log(response.qty);
            
                
            }
        });//ajax call }
}


</script>