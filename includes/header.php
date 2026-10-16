<?php
if (!isset($page_title)) {
    $page_title = 'Appointments';
}
if (!isset($page_heading)) {
    $page_heading = $page_title;
}
if (!isset($page_subtitle)) {
    $page_subtitle = 'Manage your schedule in one place';
}
if (!isset($active_page)) {
    $active_page = basename($_SERVER['PHP_SELF']);
}
start_app_session();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($page_title); ?> | Appointment Booking</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body data-page="<?php echo e($active_page); ?>">
<div class="app-shell">
<?php require __DIR__ . '/sidebar.php'; ?>
<div class="main-wrap">
    <header class="topbar">
        <button class="menu-toggle" type="button" id="menuToggle" aria-label="Open menu">
            <span></span><span></span><span></span>
        </button>
        <div>
            <p class="eyebrow">Appointment Booking System</p>
            <h1><?php echo e($page_heading); ?></h1>
            <p class="subtitle"><?php echo e($page_subtitle); ?></p>
        </div>
        <div class="topbar-actions">
            <a class="btn btn-primary" href="book_appointment.php">Book Appointment</a>
        </div>
    </header>
    <main class="content">
