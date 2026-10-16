<?php
if (!isset($active_page)) {
    $active_page = basename($_SERVER['PHP_SELF']);
}
?>
<aside class="sidebar" id="sidebar">
    <div class="brand">
        <div class="brand-mark">AB</div>
        <div>
            <strong>Appointly</strong>
            <span>Booking Studio</span>
        </div>
    </div>
    <nav class="side-nav">
        <a class="<?php echo $active_page === 'dashboard.php' || $active_page === 'index.php' ? 'active' : ''; ?>" href="dashboard.php">
            <span class="nav-icon">⌂</span> Dashboard
        </a>
        <a class="<?php echo $active_page === 'book_appointment.php' ? 'active' : ''; ?>" href="book_appointment.php">
            <span class="nav-icon">＋</span> Book Appointment
        </a>
        <a class="<?php echo $active_page === 'my_appointments.php' ? 'active' : ''; ?>" href="my_appointments.php">
            <span class="nav-icon">☰</span> My Appointments
        </a>
        <a class="<?php echo $active_page === 'calendar.php' ? 'active' : ''; ?>" href="calendar.php">
            <span class="nav-icon">▦</span> Calendar
        </a>
    </nav>
    <div class="sidebar-note">
        <p>Need a new slot?</p>
        <a href="book_appointment.php">Create booking</a>
    </div>
</aside>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
