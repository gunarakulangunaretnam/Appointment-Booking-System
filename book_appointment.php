<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

start_app_session();
$db = get_db();
$marked_dates = get_all_booked_date_counts($db);

$page_title = 'Book Appointment';
$page_heading = 'Book appointment';
$page_subtitle = 'Select a date, choose an open slot, then save your booking.';
$active_page = 'book_appointment.php';
require __DIR__ . '/includes/header.php';
?>
<section class="grid-2">
    <article class="calendar-card">
        <div class="calendar-head">
            <h2 class="section-title" id="calendarTitle">Select a date</h2>
            <div>
                <button class="icon-btn" type="button" id="prevMonth" aria-label="Previous month">&lt;</button>
                <button class="icon-btn" type="button" id="nextMonth" aria-label="Next month">&gt;</button>
            </div>
        </div>
        <div class="weekdays">
            <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
        </div>
        <div class="calendar-grid" id="calendarGrid" data-marked="<?php echo e(json_encode($marked_dates)); ?>"></div>
        <p class="muted" id="selectedDateLabel">No date selected.</p>
    </article>
    <article class="form-card">
        <h2 class="section-title">Appointment details</h2>
        <div class="alert" id="formAlert"></div>
        <form id="bookingForm">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" maxlength="150" required placeholder="Client meeting">

            <label for="dateField">Date</label>
            <input type="date" id="dateField" name="date" required>

            <label>Time slot</label>
            <input type="hidden" id="timeField" name="time" required>
            <div class="slots" id="slotList">
                <p class="empty-state">Select a date to load available times.</p>
            </div>

            <label for="notes">Notes (optional)</label>
            <textarea id="notes" name="notes" maxlength="500" placeholder="Add a short note"></textarea>

            <button class="btn btn-primary" type="submit" id="submitBooking">Confirm booking</button>
        </form>
    </article>
</section>
</main>
</div>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
