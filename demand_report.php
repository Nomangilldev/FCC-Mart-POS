<?php include_once "includes/head.php" 
   
     ?>
     <style type="text/css">
       tr>td{
        font-size: 17px;
        font-weight: bold;
       }
       tr>th{
        font-size: 22px;
        font-weight: bolder;
       }
     </style>
<center><h3>Low Stock Products List</h3></center>
<center><h5><?=date('D,d-m-Y')?></h5></center>

<center>
<table  style="width: 95%;margin: 10px;background-color: #fff;font-size: 25px;text-align: center;" border="2" cellpadding="15px" cellspacing="5px">
  <tr>
    <th>Sr. No.</th>
    <th>Name</th>
    <th>Category</th>
    <th>Brand</th>
    <th>inStock</th>
  </tr>

<tr>
 <?php 

  $query=mysqli_query($dbc,"SELECT * FROM product WHERE status=1 AND quantity_instock<alert_at ORDER BY product_name ASC ");

    $c=0;
    $finaltotal = 0;
    while($r=mysqli_fetch_assoc($query)):
    @$categoryFetched=fetchRecord($dbc,"categories","categories_id",$r['category_id']);
      @$brandFetched=fetchRecord($dbc,"brands","brand_id",$r['brand_id']);

      $c++;
  ?>
  <td><?=$c?></td>
  <td><?=$r['product_name']?></td>
  <td><?=@strtoupper($categoryFetched['categories_name'])?></td>
  <td><?=@strtoupper($brandFetched['brand_name'])?></td>
  <td><?=$r['quantity_instock']?></td>
    <?php 
    $finaltotal +=$r['quantity_instock'];

     ?>
  </tr>

<?php endwhile; ?>

<<!-- tr>
  <th colspan="4"><h3>Total </h3></th>
  <th><?=$finaltotal?></th>
</tr>
 -->
</table></center>