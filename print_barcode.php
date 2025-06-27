<?php
include_once 'includes/head.php';
require_once 'vendor/autoload.php'; // Include the Composer autoloader

use Picqer\Barcode\BarcodeGeneratorSVG;

// Check for the product ID
if (isset($_REQUEST['id'])) {
  // Fetch product data
  $fetchproduct = fetchRecord($dbc, "product", "product_id", base64_decode($_REQUEST['id']));

  // Generate SVG barcode
  $generator = new BarcodeGeneratorSVG();
  $barcodeSVG = $generator->getBarcode($fetchproduct['product_code'], $generator::TYPE_CODE_128);

  // Output HTML
  echo "<div class='printtest'>
          <div style='text-align: center;'>
            <p style='margin: 0; font-weight: bold; font-size: 14px;'>" . htmlspecialchars(ucwords($fetchproduct['product_name'])) . "</p>
            <div style='margin-top: 5px;'>$barcodeSVG</div>
          </div>
        </div>";
}
?>

<style>
  body {
    margin: 0;
    padding: 0;
  }

  .printtest {
    width: 280px;
    text-align: center;
    padding: 5px;
  }

  @media print {
    body {
      margin: 0;
      padding: 0;
    }

    .printtest {
      page-break-after: always;
      margin: 0;
      padding: 0;
    }
  }
</style>

<?php include_once 'includes/foot.php'; ?>