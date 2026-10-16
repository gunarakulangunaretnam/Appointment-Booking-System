<?php
define('API_REQUEST', true);
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

start_app_session();
$db = get_db();

$date = isset($_GET['date']) ? normalize_date($_GET['date']) : null;
$exclude_id = isset($_GET['exclude_id']) ? (int) $_GET['exclude_id'] : 0;

if ($date === null) {
    json_error('A valid date is required.');
}

$booked = get_booked_times($db, $date, $exclude_id);
$slots = array();

foreach (allowed_time_slots() as $time) {
    $is_booked = in_array($time, $booked, true);
    $is_past = is_past_slot($date, $time);
    $slots[] = array(
        'time' => $time,
        'label' => format_display_time($time),
        'booked' => $is_booked,
        'past' => $is_past,
        'available' => (!$is_booked && !$is_past)
    );
}

json_response(array(
    'success' => true,
    'date' => $date,
    'slots' => $slots
));
