<?php
require_once __DIR__ . '/../config/mail.php';

// Sends an email if PHPMailer is installed via composer (vendor/autoload.php),
// otherwise falls back to PHP's mail() and always logs to storage/emails.log
// so notifications are visible during local development without real SMTP.
function send_email(string $to, string $subject, string $body): bool {
    $log_line = sprintf(
        "[%s] To: %s | Subject: %s | Body: %s\n",
        date('Y-m-d H:i:s'),
        $to,
        $subject,
        str_replace("\n", ' ', $body)
    );
    file_put_contents(__DIR__ . '/../storage/emails.log', $log_line, FILE_APPEND);

    $vendor_autoload = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($vendor_autoload)) {
        require_once $vendor_autoload;
        if (class_exists('PHPMailer\PHPMailer\PHPMailer') && SMTP_HOST !== '') {
            try {
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->Port = SMTP_PORT;
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_USER;
                $mail->Password = SMTP_PASS;
                $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
                $mail->addAddress($to);
                $mail->Subject = $subject;
                $mail->Body = $body;
                return $mail->send();
            } catch (Exception $e) {
                return false;
            }
        }
    }

    // No PHPMailer/SMTP configured yet - mail() will usually fail silently
    // on local dev environments, but we've already logged it above either way.
    @mail($to, $subject, $body, 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM . '>');
    return true;
}

// Notifies every volunteer and admin when a post is flagged urgent/critical.
function send_emergency_alert(PDO $pdo, string $post_title): void {
    $stmt = $pdo->query("SELECT email FROM users WHERE role IN ('volunteer', 'admin')");
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $email) {
        send_email($email, '🚨 Emergency case detected', "A post titled \"{$post_title}\" has been flagged as urgent and needs immediate attention. Please review it in the moderation queue.");
    }
}
