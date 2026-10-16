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
    json_error('This appointment is already cancelled.');
}

$status = 'cancelled';
$stmt = $db->prepare('UPDATE appointments SET status = ? WHERE id = ? AND status = ?');
$current = 'booked';
$stmt->bind_param('sis', $status, $id, $current);
$stmt->execute();
$changed = $stmt->affected_rows;
$stmt->close();

if ($changed < 1) {
    json_error('Unable to cancel the appointment.', 409);
}

json_response(array(
    'success' => true,
    'message' => 'Appointment cancelled successfully.'
));
