<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

start_app_session();
$db = get_db();
$stats = get_dashboard_stats($db);
$upcoming = get_upcoming_appointments($db, 8);
$marked_dates = get_all_booked_date_counts($db);

$page_title = 'Dashboard';
$page_heading = 'Dashboard';
$page_subtitle = 'A live snapshot of booked, upcoming, and cancelled appointments.';
$active_page = 'dashboard.php';
require __DIR__ . '/includes/header.php';
?>
<section class="stats-grid">
    <article class="stat-card">
        <div>
            <span>Upcoming appointments</span>
            <strong id="statUpcoming"><?php echo (int) $stats['upcoming']; ?></strong>
        </div>
        <div class="stat-icon">📅</div>
    </article>
    <article class="stat-card">
        <div>
            <span>Today's appointments</span>
            <strong id="statToday"><?php echo (int) $stats['today']; ?></strong>
        </div>
        <div class="stat-icon green">✔</div>
    </article>
    <article class="stat-card">
        <div>
            <span>Cancelled</span>
            <strong id="statCancelled"><?php echo (int) $stats['cancelled']; ?></strong>
        </div>
        <div class="stat-icon red">✕</div>
    </article>
    <article class="stat-card">
        <div>
            <span>Total appointments</span>
            <strong id="statTotal"><?php echo (int) $stats['total']; ?></strong>
        </div>
        <div class="stat-icon gold">▣</div>
    </article>
</section>

<section class="grid-2">
    <article class="calendar-card">
        <div class="calendar-head">
            <h2 class="section-title" id="calendarTitle">Calendar</h2>
            <div>
                <button class="icon-btn" type="button" id="prevMonth" aria-label="Previous month">&lt;</button>
                <button class="icon-btn" type="button" id="nextMonth" aria-label="Next month">&gt;</button>
            </div>
        </div>
        <div class="weekdays">
            <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
        </div>
        <div class="calendar-grid" id="calendarGrid" data-marked="<?php echo e(json_encode($marked_dates)); ?>"></div>
        <p class="muted" id="selectedDateLabel">Select a date to review the schedule.</p>
    </article>
    <article class="form-card">
        <h2 class="section-title">Available slots</h2>
        <div class="slots" id="slotList">
            <p class="empty-state">Choose a date to see open times.</p>
        </div>
        <p style="margin-top:16px;">
            <a class="btn btn-primary" id="bookFromDashboard" href="book_appointment.php">Book selected date</a>
        </p>
    </article>
</section>

<section class="table-card">
    <h2 class="section-title">Upcoming appointments</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Notes</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="upcomingTable">
            <?php if (empty($upcoming)): ?>
                <tr><td colspan="5" class="empty-state">No upcoming appointments yet.</td></tr>
            <?php else: ?>
                <?php foreach ($upcoming as $row): ?>
                    <tr>
                        <td><?php echo e($row['title']); ?></td>
                        <td><?php echo e(format_display_date($row['appointment_date'])); ?></td>
                        <td><?php echo e(format_display_time($row['appointment_time'])); ?></td>
                        <td><?php echo e($row['notes'] ? $row['notes'] : '—'); ?></td>
                        <td><span class="badge booked">Booked</span></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
</main>
</div>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
