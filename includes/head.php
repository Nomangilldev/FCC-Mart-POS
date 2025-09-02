<?php include_once 'php_action/core.php';
@$get_company = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM company ORDER BY id DESC LIMIT 1 "));
?>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="a software deleveloped by Sam'z Creations">
  <meta name="author" content="Samz Creations">
  <link rel="icon" href="img/logo/<?= $get_company['logo'] ?>" type="image/gif" sizes="16x16">
  <title><?= $get_company['name'] ?></title>
  <!-- Simple bar CSS -->
  <link rel="stylesheet" href="css/simplebar.css">
  <!-- Fonts CSS -->
  <!--   <link href="https://fonts.googleapis.com/css2?family=Overpass:ital,wght@0,100;0,200;0,300;0,400;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet"> -->
  <!-- Icons CSS -->
  <link rel="stylesheet" href="css/fontawesome.min.css">
  <link rel="stylesheet" href="css/feather.css">
  <link rel="stylesheet" href="css/select2.css">
  <link rel="stylesheet" href="css/dropzone.css">
  <link rel="stylesheet" href="css/uppy.min.css">
  <link rel="stylesheet" href="css/jquery.steps.css">
  <link rel="stylesheet" href="css/jquery.timepicker.css">
  <link rel="stylesheet" href="css/quill.snow.css">
  <!-- Date Range Picker CSS -->
  <link rel="stylesheet" href="css/daterangepicker.css">
  <!-- App CSS -->
  <link rel="stylesheet" href="css/app-light.css" id="lightTheme">
  <link rel="stylesheet" href="css/app-dark.css" id="darkTheme" disabled>
  <script src="js/sweetalert2.min.js"></script>
  <script src="js/all.min.js"></script>
  <link rel="stylesheet" href="css/sweetalert2.min.css">
  <link rel="stylesheet" href="css/dataTables.bootstrap4.css">
  <style type="text/css">
    .btn-admin,
    .bg-admin {
      color: #ffffff;
      background-color: #ff1a1a;
      border-color: #ff1a1a;
    }

    .btn-admin2,
    .bg-admin2 {
      color: white;
      background-color: black;
      border-color: black;
    }

    .card-bg {

      background-color: black;

    }

    .card-text {
      color: white;
    }
  </style>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4"></script>
  <script src="https://cdn.jsdelivr.net/npm/luxon@3.5.0"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-luxon@1.3.1"></script>
  <style>
    .chart-container {
      position: relative;
      width: 100%;
      height: 400px;
      max-width: 100%;
    }

    #chart {
      width: 100% !important;
      height: 100% !important;
    }
  </style>
</head>