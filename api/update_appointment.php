<?php
define('API_REQUEST', true);
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

start_app_session();
require_post();
$body = read_json_body();
require_valid_csrf($body);
$db = get_db();

$id = isset($body['id']) ? (int) $body['id'] : 0;
if ($id < 1) {
    json_error('Appointment not found.', 404);
}

$existing = get_appointment_by_id($db, $id);
if (!$existing) {
    json_error('Appointment not found.', 404);
}
if ($existing['status'] !== 'booked') {
    json_error('Cancelled appointments cannot be edited.');
}

$title = isset($body['title']) ? $body['title'] : '';
$date = isset($body['date']) ? $body['date'] : '';
$time = isset($body['time']) ? $body['time'] : '';
$notes = isset($body['notes']) ? $body['notes'] : '';

$same_slot = ($date === $existing['appointment_date'] && normalize_time($time) === $existing['appointment_time']);
$parsed = validate_appointment_input($title, $date, $time, $notes, $same_slot);
if (!empty($parsed['errors'])) {
    json_error($parsed['errors'][0]);
}

if (slot_is_taken($db, $parsed['date'], $parsed['time'], $id)) {
    json_error('The selected time slot is already booked. Choose another time.', 409);
}

$sql = 'UPDATE appointments SET title = ?, appointment_date = ?, appointment_time = ?, notes = ? WHERE id = ? AND status = ?';
$status = 'booked';

try {
    $stmt = $db->prepare($sql);
    $stmt->bind_param('ssssis', $parsed['title'], $parsed['date'], $parsed['time'], $parsed['notes'], $id, $status);
    $stmt->execute();
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    if ((int) $e->getCode() === 1062) {
        json_error('The selected time slot is already booked. Choose another time.', 409);
    }
    error_log('Update appointment failed: ' . $e->getMessage());
    json_error('Unable to update the appointment.', 500);
}

$appointment = get_appointment_by_id($db, $id);
json_response(array(
    'success' => true,
    'message' => 'Appointment updated successfully.',
    'appointment' => appointment_to_public($appointment)
));
