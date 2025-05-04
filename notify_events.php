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
     WHERE e.start_date BETWEEN DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND DATE_ADD(CURDATE(), INTERVAL 1 DAY) + INTERVAL 1 DAY
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

        // HTML template as a separate variable for better readability
        $htmlTemplate = <<<HTML
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Language" content="tr">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .event-card { margin-bottom: 1rem; }
        .event-time { color: #666; font-size: 0.9em; }
        .event-description { color: #444; margin-top: 0.5rem; }
        .footer { font-size: 0.8em; color: #999; }
    </style>
    <title>Yaklaşan Etkinlikleriniz</title>
</head>
<body>
    <div class="container my-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Yaklaşan Etkinlik Hatırlatması</h4>
            </div>
            <div class="card-body">
                <p>Merhaba {$u['name']},</p>
                <p>Aşağıdaki etkinlikleriniz yarın gerçekleşecek:</p>
                <div class="list-group">
HTML;

        // Add events to the template
        foreach ($u['events'] as $ev) {
            $formattedDate = date('d.m.Y H:i', strtotime($ev->start_date));
            $htmlTemplate .= <<<HTML
                    <div class="list-group-item event-card">
                        <h5 class="mb-1">{$ev->title}</h5>
                        <div class="event-time">{$formattedDate}</div>
HTML;
            
            if (!empty($ev->description)) {
                $htmlTemplate .= <<<HTML
                        <div class="event-description">{$ev->description}</div>
HTML;
            }
            
            $htmlTemplate .= <<<HTML
                    </div>
HTML;
        }

        // Close the template
        $htmlTemplate .= <<<HTML
                </div>
            </div>
            <div class="card-footer text-muted footer">
                TimePay &copy; {$currentYear}
            </div>
        </div>
    </div>
</body>
</html>
HTML;

        $mail->Body = $htmlTemplate;
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
