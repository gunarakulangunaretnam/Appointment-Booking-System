<?php
function start_app_session()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token()
{
    start_app_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function get_request_csrf_token()
{
    $header = '';
    if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        $header = $_SERVER['HTTP_X_CSRF_TOKEN'];
    }
    return is_string($header) ? $header : '';
}

function verify_csrf($token)
{
    start_app_session();
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function json_response($data, $status_code = 200)
{
    http_response_code($status_code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function json_error($message, $status_code = 400)
{
    json_response(array('success' => false, 'error' => $message), $status_code);
}

function read_json_body()
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return array();
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : array();
}

function require_post()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_error('Method not allowed.', 405);
    }
}

function require_valid_csrf($body = array())
{
    $token = get_request_csrf_token();
    if ($token === '' && isset($body['csrf_token'])) {
        $token = $body['csrf_token'];
    }
    if (!verify_csrf($token)) {
        json_error('Invalid security token. Refresh the page and try again.', 403);
    }
}

function allowed_time_slots()
{
    return array(
        '09:00:00', '09:30:00', '10:00:00', '10:30:00',
        '11:00:00', '11:30:00', '13:00:00', '13:30:00',
        '14:00:00', '14:30:00', '15:00:00', '15:30:00',
        '16:00:00', '16:30:00'
    );
}

function normalize_time($time)
{
    $time = trim((string) $time);
    $dt = DateTime::createFromFormat('H:i:s', $time);
    if ($dt && $dt->format('H:i:s') === $time) {
        return $time;
    }
    $dt = DateTime::createFromFormat('H:i', $time);
    if ($dt && $dt->format('H:i') === $time) {
        return $dt->format('H:i:s');
    }
    return null;
}

function normalize_date($date)
{
    $date = trim((string) $date);
    $dt = DateTime::createFromFormat('Y-m-d', $date);
    if ($dt && $dt->format('Y-m-d') === $date) {
        return $date;
    }
    return null;
}

function is_past_slot($date, $time)
{
    $slot = DateTime::createFromFormat('Y-m-d H:i:s', $date . ' ' . $time);
    if (!$slot) {
        return true;
    }
    return $slot < new DateTime('now');
}

function format_display_time($time)
{
    $dt = DateTime::createFromFormat('H:i:s', $time);
    if (!$dt) {
        $dt = DateTime::createFromFormat('H:i', $time);
    }
    return $dt ? $dt->format('g:i A') : $time;
}

function format_display_date($date)
{
    $dt = DateTime::createFromFormat('Y-m-d', $date);
    return $dt ? $dt->format('D, M j, Y') : $date;
}

function get_booked_times(mysqli $db, $date, $exclude_id = 0)
{
    $status = 'booked';
    if ($exclude_id > 0) {
        $stmt = $db->prepare('SELECT appointment_time FROM appointments WHERE appointment_date = ? AND status = ? AND id != ?');
        $stmt->bind_param('ssi', $date, $status, $exclude_id);
    } else {
        $stmt = $db->prepare('SELECT appointment_time FROM appointments WHERE appointment_date = ? AND status = ?');
        $stmt->bind_param('ss', $date, $status);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $times = array();
    while ($row = $result->fetch_assoc()) {
        $times[] = $row['appointment_time'];
    }
    $stmt->close();
    return $times;
}

function slot_is_taken(mysqli $db, $date, $time, $exclude_id = 0)
{
    $status = 'booked';
    if ($exclude_id > 0) {
        $stmt = $db->prepare('SELECT id FROM appointments WHERE appointment_date = ? AND appointment_time = ? AND status = ? AND id != ? LIMIT 1');
        $stmt->bind_param('sssi', $date, $time, $status, $exclude_id);
    } else {
        $stmt = $db->prepare('SELECT id FROM appointments WHERE appointment_date = ? AND appointment_time = ? AND status = ? LIMIT 1');
        $stmt->bind_param('sss', $date, $time, $status);
    }
    $stmt->execute();
    $stmt->store_result();
    $taken = $stmt->num_rows > 0;
    $stmt->close();
    return $taken;
}

function validate_appointment_input($title, $date, $time, $notes, $allow_past = false)
{
    $errors = array();
    $title = trim((string) $title);
    $notes = trim((string) $notes);

    if ($title === '') {
        $errors[] = 'Title is required.';
    } elseif (strlen($title) > 150) {
        $errors[] = 'Title must be 150 characters or fewer.';
    }

    $date = normalize_date($date);
    if ($date === null) {
        $errors[] = 'A valid date is required.';
    }

    $time = normalize_time($time);
    if ($time === null) {
        $errors[] = 'A valid time is required.';
    } elseif (!in_array($time, allowed_time_slots(), true)) {
        $errors[] = 'Please choose an available time slot.';
    }

    if ($notes !== '' && strlen($notes) > 500) {
        $errors[] = 'Notes must be 500 characters or fewer.';
    }

    if ($date !== null && $time !== null && !$allow_past && is_past_slot($date, $time)) {
        $errors[] = 'You cannot book a date and time in the past.';
    }

    return array(
        'errors' => $errors,
        'title' => $title,
        'date' => $date,
        'time' => $time,
        'notes' => $notes
    );
}

function get_dashboard_stats(mysqli $db)
{
    $today = date('Y-m-d');
    $now_time = date('H:i:s');

    $sql = "SELECT
                COUNT(*) AS total,
                COALESCE(SUM(status = 'cancelled'), 0) AS cancelled,
                COALESCE(SUM(status = 'booked' AND (appointment_date > ? OR (appointment_date = ? AND appointment_time >= ?))), 0) AS upcoming,
                COALESCE(SUM(status = 'booked' AND appointment_date = ?), 0) AS today_count
            FROM appointments";

    $stmt = $db->prepare($sql);
    $stmt->bind_param('ssss', $today, $today, $now_time, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return array(
        'upcoming' => (int) $row['upcoming'],
        'today' => (int) $row['today_count'],
        'cancelled' => (int) $row['cancelled'],
        'total' => (int) $row['total']
    );
}

function get_upcoming_appointments(mysqli $db, $limit = 10)
{
    $today = date('Y-m-d');
    $now_time = date('H:i:s');
    $limit = (int) $limit;

    $sql = "SELECT id, title, appointment_date, appointment_time, notes, status
            FROM appointments
            WHERE status = 'booked'
              AND (appointment_date > ? OR (appointment_date = ? AND appointment_time >= ?))
            ORDER BY appointment_date ASC, appointment_time ASC
            LIMIT ?";

    $stmt = $db->prepare($sql);
    $stmt->bind_param('sssi', $today, $today, $now_time, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = array();
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function get_appointments_by_status(mysqli $db, $status = 'booked')
{
    $sql = 'SELECT id, title, appointment_date, appointment_time, notes, status, created_at
            FROM appointments
            WHERE status = ?
            ORDER BY appointment_date ASC, appointment_time ASC';
    $stmt = $db->prepare($sql);
    $stmt->bind_param('s', $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = array();
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function get_appointment_by_id(mysqli $db, $id)
{
    $stmt = $db->prepare('SELECT id, title, appointment_date, appointment_time, notes, status FROM appointments WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ? $row : null;
}

function get_all_booked_date_counts(mysqli $db)
{
    $status = 'booked';
    $stmt = $db->prepare('SELECT appointment_date, COUNT(*) AS total FROM appointments WHERE status = ? GROUP BY appointment_date');
    $stmt->bind_param('s', $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $dates = array();
    while ($row = $result->fetch_assoc()) {
        $dates[$row['appointment_date']] = (int) $row['total'];
    }
    $stmt->close();
    return $dates;
}

function get_booked_dates_for_month(mysqli $db, $year, $month)
{
    $start = sprintf('%04d-%02d-01', $year, $month);
    $end_dt = DateTime::createFromFormat('Y-m-d', $start);
    $end = $end_dt->format('Y-m-t');
    $status = 'booked';

    $stmt = $db->prepare('SELECT appointment_date, COUNT(*) AS total FROM appointments WHERE status = ? AND appointment_date BETWEEN ? AND ? GROUP BY appointment_date');
    $stmt->bind_param('sss', $status, $start, $end);
    $stmt->execute();
    $result = $stmt->get_result();
    $dates = array();
    while ($row = $result->fetch_assoc()) {
        $dates[$row['appointment_date']] = (int) $row['total'];
    }
    $stmt->close();
    return $dates;
}

function appointment_to_public(array $row)
{
    return array(
        'id' => (int) $row['id'],
        'title' => $row['title'],
        'date' => $row['appointment_date'],
        'time' => $row['appointment_time'],
        'display_date' => format_display_date($row['appointment_date']),
        'display_time' => format_display_time($row['appointment_time']),
        'notes' => $row['notes'],
        'status' => $row['status']
    );
}
