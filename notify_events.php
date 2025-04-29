<?php
// Cron job to send reminder emails for events occurring in the next 3 days
// Schedule daily at 08:00: 0 8 * * * /usr/bin/php /Applications/XAMPP/xamppfiles/htdocs/Proje/notify_events.php

require      __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/classes/allClass.php';
require_once __DIR__ . '/functions/allFunctions.php';
require_once __DIR__ . '/config/mail.php';

$logFile = __DIR__ . '/error_log.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Configure DB and instantiate
Database::configure([]);
$db = new Database();

// Fetch events in the next 3 days
$rows = $db->getRows(
    "SELECT u.UserID, u.UserMail, u.UserName, e.title, e.description, e.start_date 
     FROM events e JOIN users u ON e.UserID = u.UserID
     WHERE e.start_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 3 DAY)
     ORDER BY u.UserID, e.start_date"
);

if (empty($rows)) {
    exit("Bildirim gönderilecek etkinlik bulunamadı.\n");
}

// Group events by user
$users = [];
foreach ($rows as $r) {
    $uid = $r->UserID;
    if (!isset($users[$uid])) {
        $users[$uid] = [
            'email' => $r->UserMail,
            'name'  => $r->UserName,
            'events'=> []
        ];
    }
    $users[$uid]['events'][] = $r;
}

// Send email to each user
foreach ($users as $u) {
    $mail = new PHPMailer(true);
    try {
        // Use PHP mail() function
        $mail->isMail();
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($u['email'], $u['name']);
        $mail->isHTML(true);
        $mail->Subject = 'Yaklaşan Etkinlik Hatırlatması';

        // Build HTML body with Bootstrap
        $body = '<!doctype html>';
        $body .= '<html lang="tr"><head>';
        $body .= '<meta charset="utf-8">';
        $body .= '<meta http-equiv="Content-Language" content="tr">';
        $body .= '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">';
        $body .= '<title>Yaklaşan Etkinlikleriniz</title>';
        $body .= '</head><body>';
        $body .= '<div class="container my-4">';
        $body .= '<div class="card">';
        $body .= '<div class="card-header bg-primary text-white">Yaklaşan Etkinlik Hatırlatması</div>';
        $body .= '<div class="card-body">';
        $body .= '<p>Merhaba ' . htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') . ',</p>';
        $body .= '<p>Aşağıdaki etkinlikleriniz önümüzdeki 3 gün içinde gerçekleşecek:</p>';
        $body .= '<ul class="list-group">';
        foreach ($u['events'] as $ev) {
            $body .= '<li class="list-group-item">';
            $body .= '<strong>' . htmlspecialchars($ev->title, ENT_QUOTES, 'UTF-8') . '</strong><br>';
            $body .= '<small>' . date('d.m.Y H:i', strtotime($ev->start_date)) . '</small><br>';
            if (!empty($ev->description)) {
                $body .= '<p class="mt-2 mb-0">' . nl2br(htmlspecialchars($ev->description, ENT_QUOTES, 'UTF-8')) . '</p>';
            }
            $body .= '</li>';
        }
        $body .= '</ul>';
        $body .= '</div>'; // card-body
        $body .= '<div class="card-footer text-muted">TimePay &copy; ' . date('Y') . '</div>';
        $body .= '</div></div></body></html>';

        $mail->Body = $body;
        $mail->send();
        echo "E-posta gönderildi: {$u['email']}\n";
        file_put_contents(
            $logFile,
            date('Y-m-d H:i:s') . " SUCCESS: {$u['email']}" . PHP_EOL,
            FILE_APPEND
        );
        // Admina durum bildirimi: başarılı
        $adminMail = new PHPMailer(true);
        try {
            $adminMail->isMail();
            $adminMail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
            $adminMail->addAddress(SMTP_FROM, SMTP_FROM_NAME);
            $adminMail->Subject = 'E-posta gönderim durumu: Başarılı';
            $adminMail->Body = "E-posta başarıyla gönderildi: {$u['email']}";
            $adminMail->send();
        } catch (Exception $ex) {
            // Admin bildirim başarısız, yoksay
        }
    } catch (Exception $e) {
        echo "E-posta gönderilemedi: {$u['email']} - Hata: {$mail->ErrorInfo}\n";
        file_put_contents(
            $logFile,
            date('Y-m-d H:i:s') . " ERROR: {$u['email']} - {$mail->ErrorInfo}" . PHP_EOL,
            FILE_APPEND
        );
        // Admina durum bildirimi: başarısız
        $adminMail = new PHPMailer(true);
        try {
            $adminMail->isMail();
            $adminMail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
            $adminMail->addAddress(SMTP_FROM, SMTP_FROM_NAME);
            $adminMail->Subject = 'E-posta gönderim durumu: Başarısız';
            $adminMail->Body = "E-posta gönderilemedi: {$u['email']} - Hata: {$mail->ErrorInfo}";
            $adminMail->send();
        } catch (Exception $ex) {
            // Admin bildirim başarısız, yoksay
        }
    }
}
