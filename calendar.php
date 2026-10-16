<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

start_app_session();
$db = get_db();
$year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');
$month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');
if ($month < 1 || $month > 12) {
    $month = (int) date('n');
}
if ($year < 2020 || $year > 2100) {
    $year = (int) date('Y');
}

$marked = get_all_booked_date_counts($db);
$upcoming = get_upcoming_appointments($db, 12);

$page_title = 'Calendar';
$page_heading = 'Calendar';
$page_subtitle = 'See booked dates at a glance and jump into a new booking.';
$active_page = 'calendar.php';
require __DIR__ . '/includes/header.php';
?>
<section class="grid-2">
    <article class="calendar-card">
        <div class="calendar-head">
            <h2 class="section-title" id="calendarTitle"><?php echo e(date('F Y', mktime(0, 0, 0, $month, 1, $year))); ?></h2>
            <div>
                <button class="icon-btn" type="button" id="prevMonth" aria-label="Previous month">&lt;</button>
                <button class="icon-btn" type="button" id="nextMonth" aria-label="Next month">&gt;</button>
            </div>
        </div>
        <div class="weekdays">
            <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
        </div>
        <div class="calendar-grid" id="calendarGrid" data-marked="<?php echo e(json_encode($marked)); ?>"></div>
        <p class="muted" id="selectedDateLabel">Dates with a blue underline have at least one booking.</p>
    </article>
    <article class="form-card">
        <h2 class="section-title">Slots for selected date</h2>
        <div class="slots" id="slotList">
            <p class="empty-state">Click a date to inspect availability.</p>
        </div>
        <p style="margin-top:16px;">
            <a class="btn btn-primary" id="bookFromDashboard" href="book_appointment.php">Book this date</a>
        </p>
    </article>
</section>
<section class="table-card">
    <h2 class="section-title">Upcoming on the calendar</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($upcoming)): ?>
                <tr><td colspan="4" class="empty-state">No upcoming appointments.</td></tr>
            <?php else: ?>
                <?php foreach ($upcoming as $row): ?>
                    <tr>
                        <td><?php echo e($row['title']); ?></td>
                        <td><?php echo e(format_display_date($row['appointment_date'])); ?></td>
                        <td><?php echo e(format_display_time($row['appointment_time'])); ?></td>
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
