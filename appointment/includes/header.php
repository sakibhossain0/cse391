<?php
/* ============================================================
   header.php: the top of every page (head, skip link, menu).
   Before including it, a page sets:
     $pageTitle  the text for the browser tab
     $active     which menu item to highlight: 'book', 'help' or 'admin'
     $base       '' for pages in the main folder, '../' for admin pages
   ============================================================ */
defined('APP_RUNNING') or exit('No direct access.');

$menu = [
    'book' => ['index.php', 'fa-calendar-check', 'Book'],
    'help' => ['help.php', 'fa-circle-question', 'Help'],
    'admin' => ['admin/index.php', 'fa-user-gear', 'Admin'],
];
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Book a car service appointment online and choose your own mechanic.">
  <meta name="theme-color" content="#2E3440">
  <title><?= e($pageTitle) ?> | Workshop Appointments</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&amp;family=JetBrains+Mono:wght@500;600&amp;display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="<?= $base ?>assets/css/style.css">
</head>
<body>
<a class="skip-link" href="#main">Skip to main content</a>

<header class="topbar">
  <div class="wrap topbar-inner">
    <a class="brand" href="<?= $base ?>index.php">
      <span class="brand-icon"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i></span>
      <span>Workshop <strong>Appointments</strong></span>
    </a>
    <nav class="menu" aria-label="Main menu">
      <?php foreach ($menu as $key => $item): ?>
        <a href="<?= $base . $item[0] ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>>
          <i class="fa-solid <?= $item[1] ?>" aria-hidden="true"></i> <?= $item[2] ?>
        </a>
      <?php endforeach; ?>
    </nav>
  </div>
</header>

<main id="main" class="wrap">
