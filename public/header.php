<?php
require_once __DIR__ . '/url.php';
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Base URL for all relative links -->
  <base href="<?php echo base_url(); ?>">

  <!-- Bootstrap CSS & Icons (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">

  <!-- Project Styles -->
  <link rel="stylesheet" href="<?php echo base_url('assets/css/styles.css'); ?>">
  <link rel="stylesheet" href="<?php echo base_url('assets/css/theme.css'); ?>">
  <link rel="stylesheet" href="<?php echo base_url('assets/css/cars.css'); ?>">
  <link rel="stylesheet" href="<?php echo base_url('assets/css/auth.css'); ?>">

  <title>CarRental</title>
</head>
<body>
<?php
// You can include or render navbar.php here, for example:
// include __DIR__ . '/navbar.php';
?>