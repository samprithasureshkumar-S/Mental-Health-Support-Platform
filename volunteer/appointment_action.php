<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';
require_once '../includes/mailer.php';

restrict_to_role('volunteer');

$volunteer_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';
$appt_id = $_GET['id'] ?? null;

$status_map = [
    'accept' => 'accepted',
    'cancel' => 'cancelled',
    'complete' => 'completed',
];

if ($appt_id && isset($status_map[$action])) {
    try {
        $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ? AND volunteer_id = ?");
        if ($stmt->execute([$status_map[$action], $appt_id, $volunteer_id])) {
            if ($action === 'accept') {
                $user_stmt = $pdo->prepare("SELECT u.email FROM appointments a JOIN users u ON a.user_id = u.id WHERE a.id = ?");
                $user_stmt->execute([$appt_id]);
                $user_row = $user_stmt->fetch();
                if ($user_row) {
                    send_email($user_row['email'], 'Your appointment was accepted', "Good news! Your support appointment request has been accepted by the volunteer. Log in to Community Connect to coordinate further.");
                }
            }
            header("Location: /volunteer/appointments.php?success=Appointment updated.");
            exit;
        } else {
            header("Location: /volunteer/appointments.php?error=Failed to update appointment.");
            exit;
        }
    } catch (PDOException $e) {
        header("Location: /volunteer/appointments.php?error=" . urlencode($e->getMessage()));
        exit;
    }
}

header("Location: /volunteer/appointments.php");
exit;
