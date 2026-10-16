<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

start_app_session();
$db = get_db();
$booked = get_appointments_by_status($db, 'booked');
$cancelled = get_appointments_by_status($db, 'cancelled');

$page_title = 'My Appointments';
$page_heading = 'My appointments';
$page_subtitle = 'Edit details, change time slots, or cancel with confirmation.';
$active_page = 'my_appointments.php';
require __DIR__ . '/includes/header.php';
?>
<div class="alert" id="formAlert"></div>
<section class="table-card" style="margin-bottom:22px;">
    <h2 class="section-title">Active bookings</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Notes</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="activeTable">
            <?php if (empty($booked)): ?>
                <tr><td colspan="6" class="empty-state">You have no active appointments.</td></tr>
            <?php else: ?>
                <?php foreach ($booked as $row): ?>
                    <tr data-id="<?php echo (int) $row['id']; ?>">
                        <td><?php echo e($row['title']); ?></td>
                        <td><?php echo e(format_display_date($row['appointment_date'])); ?></td>
                        <td><?php echo e(format_display_time($row['appointment_time'])); ?></td>
                        <td><?php echo e($row['notes'] ? $row['notes'] : '—'); ?></td>
                        <td><span class="badge booked">Booked</span></td>
                        <td class="row-actions">
                            <button class="btn btn-outline js-edit"
                                type="button"
                                data-id="<?php echo (int) $row['id']; ?>"
                                data-title="<?php echo e($row['title']); ?>"
                                data-date="<?php echo e($row['appointment_date']); ?>"
                                data-time="<?php echo e($row['appointment_time']); ?>"
                                data-notes="<?php echo e($row['notes']); ?>">Edit</button>
                            <button class="btn btn-danger js-cancel" type="button" data-id="<?php echo (int) $row['id']; ?>" data-title="<?php echo e($row['title']); ?>">Cancel</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="table-card">
    <h2 class="section-title">Cancelled appointments</h2>
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
            <tbody>
            <?php if (empty($cancelled)): ?>
                <tr><td colspan="5" class="empty-state">No cancelled appointments.</td></tr>
            <?php else: ?>
                <?php foreach ($cancelled as $row): ?>
                    <tr>
                        <td><?php echo e($row['title']); ?></td>
                        <td><?php echo e(format_display_date($row['appointment_date'])); ?></td>
                        <td><?php echo e(format_display_time($row['appointment_time'])); ?></td>
                        <td><?php echo e($row['notes'] ? $row['notes'] : '—'); ?></td>
                        <td><span class="badge cancelled">Cancelled</span></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<div class="modal-backdrop" id="editModal">
    <div class="modal">
        <h3>Edit appointment</h3>
        <form id="editForm">
            <input type="hidden" id="editId">
            <label for="editTitle">Title</label>
            <input type="text" id="editTitle" maxlength="150" required>
            <label for="editDate">Date</label>
            <input type="date" id="editDate" required>
            <label>Time slot</label>
            <input type="hidden" id="editTime" required>
            <div class="slots" id="editSlotList"></div>
            <label for="editNotes">Notes</label>
            <textarea id="editNotes" maxlength="500"></textarea>
            <div class="row-actions">
                <button class="btn btn-primary" type="submit">Save changes</button>
                <button class="btn btn-outline" type="button" id="closeEdit">Close</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="cancelModal">
    <div class="modal">
        <h3>Cancel appointment</h3>
        <p id="cancelMessage">Are you sure you want to cancel this appointment?</p>
        <div class="row-actions">
            <button class="btn btn-danger" type="button" id="confirmCancel">Yes, cancel it</button>
            <button class="btn btn-outline" type="button" id="closeCancel">Keep appointment</button>
        </div>
    </div>
</div>
</main>
</div>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
