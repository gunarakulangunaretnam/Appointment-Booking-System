<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

start_app_session();
$db = get_db();
$stats = get_dashboard_stats($db);

$page_title = 'Home';
$page_heading = 'Welcome back';
$page_subtitle = 'Plan, book, and manage appointments from a clean dashboard.';
$active_page = 'index.php';
require __DIR__ . '/includes/header.php';
?>
<section class="welcome-hero">
    <h2>Stay on top of every appointment</h2>
    <p>Pick a date, choose an open time slot, and keep your schedule free of double bookings. All stats below come from live database records.</p>
    <a class="btn" href="book_appointment.php">Book a new appointment</a>
</section>
<section class="stats-grid">
    <article class="stat-card">
        <div>
            <span>Upcoming</span>
            <strong><?php echo (int) $stats['upcoming']; ?></strong>
        </div>
        <div class="stat-icon">📅</div>
    </article>
    <article class="stat-card">
        <div>
            <span>Today</span>
            <strong><?php echo (int) $stats['today']; ?></strong>
        </div>
        <div class="stat-icon green">✔</div>
    </article>
    <article class="stat-card">
        <div>
            <span>Cancelled</span>
            <strong><?php echo (int) $stats['cancelled']; ?></strong>
        </div>
        <div class="stat-icon red">✕</div>
    </article>
    <article class="stat-card">
        <div>
            <span>Total records</span>
            <strong><?php echo (int) $stats['total']; ?></strong>
        </div>
        <div class="stat-icon gold">▣</div>
    </article>
</section>
<section class="grid-2">
    <article class="form-card">
        <h2 class="section-title">Quick actions</h2>
        <p class="muted">Use the calendar to choose a date, then lock in an available time. Booked slots stay disabled until they are cancelled.</p>
        <p>
            <a class="btn btn-primary" href="book_appointment.php">Create booking</a>
            <a class="btn btn-outline" href="my_appointments.php">View appointments</a>
        </p>
    </article>
    <article class="form-card">
        <h2 class="section-title">How it works</h2>
        <p class="muted">1. Select a date on the calendar.</p>
        <p class="muted">2. Choose an open time slot.</p>
        <p class="muted">3. Add a title and optional notes.</p>
        <p class="muted">4. Edit or cancel later without losing slot protection.</p>
    </article>
</section>
</main>
</div>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
