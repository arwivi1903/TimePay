<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/classes/allClass.php';
require_once __DIR__ . '/functions/allFunctions.php';
require_once __DIR__ . '/config/mail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class EventNotificationMailer
{
    private $db;
    private $logFile;
    private $currentYear;

    public function __construct()
    {
        $this->logFile = __DIR__ . '/error_log.php';
        $this->currentYear = date('Y');
        $this->initializeDatabase();
    }

    private function initializeDatabase(): void
    {
        try {
            Database::configure([]);
            $this->db = new Database();
            $this->log('INFO', 'Database connection successful');
        } catch (Exception $e) {
            $this->log('ERROR', 'Database connection failed - ' . $e->getMessage());
            exit("Database connection failed: " . $e->getMessage() . "\n");
        }
    }

    private function log(string $level, string $message): void
    {
        file_put_contents(
            $this->logFile,
            date('Y-m-d H:i:s') . " {$level}: {$message}" . PHP_EOL,
            FILE_APPEND
        );
    }

    private function createMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);
        
        // SMTP kullanarak daha güvenilir gönderim
        if (defined('SMTP_HOST') && defined('SMTP_PORT')) {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME ?? SMTP_FROM;
            $mail->Password = SMTP_PASSWORD ?? '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;
        } else {
            // Fallback to mail() function
            $mail->isMail();
        }
        
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        
        return $mail;
    }

    private function getUpcomingEvents(): array
    {
        try {
            $rows = $this->db->getRows(
                "SELECT u.UserID, u.UserMail, u.UserName, e.title, e.description, e.start_date 
                 FROM events e 
                 JOIN users u ON e.UserID = u.UserID
                 WHERE e.start_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
                 ORDER BY u.UserID, e.start_date"
            );
            
            $this->log('INFO', 'Query executed successfully. Found ' . count($rows) . ' events');
            return $rows;
            
        } catch (Exception $e) {
            $this->log('ERROR', 'Query failed - ' . $e->getMessage());
            exit("Query failed: " . $e->getMessage() . "\n");
        }
    }

    private function groupEventsByUser(array $events): array
    {
        $users = [];
        
        foreach ($events as $event) {
            $userId = $event->UserID;
            
            if (!isset($users[$userId])) {
                $users[$userId] = [
                    'email' => $event->UserMail,
                    'name' => $event->UserName,
                    'events' => []
                ];
            }
            
            $users[$userId]['events'][] = $event;
        }
        
        $this->log('INFO', 'Grouped events for ' . count($users) . ' users');
        return $users;
    }

    private function generateEmailTemplate(array $user): string
    {
        $eventsHtml = '';
        
        foreach ($user['events'] as $event) {
            $formattedDate = date('d.m.Y H:i', strtotime($event->start_date));
            $description = !empty($event->description) 
                ? '<div class="event-description">' . htmlspecialchars($event->description) . '</div>'
                : '';
            
            $eventsHtml .= <<<HTML
                <div class="list-group-item event-card">
                    <h5 class="mb-1">{$event->title}</h5>
                    <div class="event-time">{$formattedDate}</div>
                    {$description}
                </div>
HTML;
        }

        return <<<HTML
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
                <p>Merhaba {$user['name']},</p>
                <p>Aşağıdaki etkinlikleriniz önümüzdeki 3 gün içinde gerçekleşecek:</p>
                <div class="list-group">
                    {$eventsHtml}
                </div>
            </div>
            <div class="card-footer text-muted footer">
                TimePay &copy; {$this->currentYear}
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function sendMail(string $toEmail, string $toName, string $subject, string $body): bool
    {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            $result = $mail->send();
            
            if ($result) {
                $this->log('SUCCESS', "Email sent to: {$toEmail}");
                echo "E-posta gönderildi: {$toEmail}\n";
                return true;
            } else {
                $this->log('ERROR', "Email failed to: {$toEmail} - Unknown error");
                echo "E-posta gönderilemedi: {$toEmail} - Bilinmeyen hata\n";
                return false;
            }
            
        } catch (Exception $e) {
            $this->log('ERROR', "Email failed to: {$toEmail} - " . $e->getMessage());
            echo "E-posta gönderilemedi: {$toEmail} - Hata: " . $e->getMessage() . "\n";
            return false;
        }
    }

    private function sendAdminNotification(bool $success, string $userEmail, string $errorMessage = ''): void
    {
        try {
            $subject = $success 
                ? 'E-posta gönderim durumu: Başarılı ✓' 
                : 'E-posta gönderim durumu: Başarısız ✗';
            
            $statusIcon = $success ? '✅' : '❌';
            $statusText = $success ? 'Başarılı' : 'Başarısız';
            $statusColor = $success ? '#28a745' : '#dc3545';
            
            $errorHtml = '';
            if (!empty($errorMessage)) {
                $errorHtml = "<p><strong>Hata Mesajı:</strong> {$errorMessage}</p>";
            }
            
            $body = <<<HTML
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Language" content="tr">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; }
        .card { border: 1px solid #ddd; border-radius: 8px; padding: 20px; }
        .status { font-size: 24px; margin-bottom: 15px; }
        .details { margin-top: 15px; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="status" style="color: {$statusColor}">
                {$statusIcon} {$statusText}
            </div>
            <div class="details">
                <p><strong>Kullanıcı E-postası:</strong> {$userEmail}</p>
                {$errorHtml}
            </div>
            <div class="footer">
                TimePay &copy; {$this->currentYear}
            </div>
        </div>
    </div>
</body>
</html>
HTML;
            
            $this->sendMail(SMTP_FROM, SMTP_FROM_NAME, $subject, $body);
            
        } catch (Exception $e) {
            $this->log('WARNING', 'Admin notification failed: ' . $e->getMessage());
        }
    }

    public function run(): void
    {
        echo "Event Notification Mailer başlatılıyor...\n";
        
        // Etkinlikleri getir
        $events = $this->getUpcomingEvents();
        
        if (empty($events)) {
            echo "Bildirim gönderilecek etkinlik bulunamadı.\n";
            $this->log('INFO', 'No events found for the next 3 days');
            return;
        }

        echo "Found " . count($events) . " events to notify\n";
        
        // Kullanıcılara göre grupla
        $users = $this->groupEventsByUser($events);
        
        // Her kullanıcıya e-posta gönder
        $successCount = 0;
        $failureCount = 0;
        
        foreach ($users as $user) {
            $this->log('INFO', "Preparing email for user {$user['name']} ({$user['email']}) with " . count($user['events']) . " events");
            
            $emailBody = $this->generateEmailTemplate($user);
            $success = $this->sendMail($user['email'], $user['name'], 'Yaklaşan Etkinlik Hatırlatması', $emailBody);
            
            if ($success) {
                $successCount++;
                $this->sendAdminNotification(true, $user['email']);
            } else {
                $failureCount++;
                $this->sendAdminNotification(false, $user['email'], 'Mail gönderim hatası');
            }
            
            // Rate limiting - sunucuyu yormamak için
            sleep(1);
        }
        
        echo "\nToplam: " . count($users) . " kullanıcı\n";
        echo "Başarılı: {$successCount}\n";
        echo "Başarısız: {$failureCount}\n";
        
        $this->log('INFO', "Mailing completed. Success: {$successCount}, Failed: {$failureCount}");
    }
}

// Script'i çalıştır
try {
    $mailer = new EventNotificationMailer();
    $mailer->run();
} catch (Exception $e) {
    echo "Kritik hata: " . $e->getMessage() . "\n";
    file_put_contents(
        __DIR__ . '/error_log.php',
        date('Y-m-d H:i:s') . " CRITICAL: " . $e->getMessage() . PHP_EOL,
        FILE_APPEND
    );
}