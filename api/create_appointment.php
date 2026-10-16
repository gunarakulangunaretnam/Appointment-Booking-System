<?php
define('API_REQUEST', true);
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

start_app_session();
require_post();
$body = read_json_body();
require_valid_csrf($body);
$db = get_db();

$title = isset($body['title']) ? $body['title'] : '';
$date = isset($body['date']) ? $body['date'] : '';
$time = isset($body['time']) ? $body['time'] : '';
$notes = isset($body['notes']) ? $body['notes'] : '';

$parsed = validate_appointment_input($title, $date, $time, $notes);
if (!empty($parsed['errors'])) {
    json_error($parsed['errors'][0]);
}

if (slot_is_taken($db, $parsed['date'], $parsed['time'])) {
    json_error('This date and time is already booked. Please choose another slot.', 409);
}

$sql = 'INSERT INTO appointments (title, appointment_date, appointment_time, notes, status) VALUES (?, ?, ?, ?, ?)';
$status = 'booked';

try {
    $stmt = $db->prepare($sql);
    $stmt->bind_param('sssss', $parsed['title'], $parsed['date'], $parsed['time'], $parsed['notes'], $status);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    if ((int) $e->getCode() === 1062) {
        json_error('This date and time is already booked. Please choose another slot.', 409);
    }
    error_log('Create appointment failed: ' . $e->getMessage());
    json_error('Unable to create the appointment.', 500);
}

$appointment = get_appointment_by_id($db, $id);
json_response(array(
    'success' => true,
    'message' => 'Appointment booked successfully.',
    'appointment' => appointment_to_public($appointment)
), 201);
