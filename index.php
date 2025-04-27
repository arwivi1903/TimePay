<?php

require_once 'header.php';
require_once 'sidebar.php';

// Oturum kontrolü
if (!isset($_SESSION['UserName'])) {
    header('Location: login.php');
    exit;
}
// Brüt ücreti al
try {
    $maasHesaplayici = new MaasHesaplayici($SaatUcreti ?? 0);
    $brutUcret = $maasHesaplayici->brutMaas();
} catch (Exception $e) {
    error_log("Maaş Hesaplama Hatası: " . $e->getMessage());
    $brutUcret = 0;
}

// Öneri gönderme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['suggestion'])) {
    $title      = trim($_POST['title']);
    $suggestion = trim($_POST['suggestion']);
    $userId     = $_SESSION['UserID'] ?? null;

    if (empty($title) || empty($suggestion)) {
        $error = "Lütfen tüm alanları doldurun.";
    } else {
        try {
            $OneriAdd = $db->insert('oneri', [
                'Baslik'        => $title,
                'OneriDetay'    => $suggestion,
                'UserID'        => $userId,
            ]);
            if ($OneriAdd) {
                $success = "Öneriniz başarıyla kaydedildi. Teşekkür ederiz!";
            } else {
                $error = "Öneriniz kaydedilirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.";
            }
        } catch (PDOException $e) {
            error_log("Öneri kaydetme hatası: " . $e->getMessage());
            $error = "Öneriniz kaydedilirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.";
        }
    }
}

?>

<main class="app-main">
    <div class="center dashboard-welcome">
        <h1 class="m-4">Merhaba; <?= htmlspecialchars($_SESSION['UserName']) ?>!</h1>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row d-flex align-items-stretch">
                <div class="col-lg-3 col-6 d-flex">
                    <div class="small-box text-bg-primary w-100 h-100">
                        <div class="inner">
                            <h3><?= $brutUcret > 0 ? paraFormatla($brutUcret) : 'Hesaplanamadı' ?></h3>
                            <p>Brüt Ücret</p>
                        </div>
                        <i class="small-box-icon fas fa-money-bill-wave"></i>
                    </div>
                </div>
                <div class="col-lg-3 col-6 d-flex">
                    <div class="small-box text-bg-info w-100 h-100">
                        <div class="inner">
                            <?php
                            $events      = $db->getRows('SELECT * FROM events WHERE UserID = ?', [$_SESSION['UserID']]);
                            $totalEvents = count($events);
                            $now = new DateTime('now');
                            $upcomingEvents = array_filter($events, function($event) use ($now) {
                                return (new DateTime($event->start_date)) > $now;
                            });
                            usort($upcomingEvents, function($a, $b) {
                                return strtotime($a->start_date) - strtotime($b->start_date);
                            });
                            $upcomingEvents = array_slice($upcomingEvents, 0, 4);
                            ?>
                            <h3><?= $totalEvents ?></h3>
                            <p>Toplam Etkinlik</p>
                        </div>
                        <i class="small-box-icon fas fa-calendar-alt"></i>
                    </div>
                </div>
                <div class="col-lg-6 col-6 d-flex">
                    <div class="small-box text-bg-warning w-100 h-100">
                        <div class="inner">
                            <h3><?= count($upcomingEvents) ?></h3>
                            <p>Yaklaşan Etkinlikler</p>
                            <?php $liCount = count($upcomingEvents); ?>
                            <ul style="<?= $liCount > 2 ? 'display:flex;flex-wrap:wrap;gap:10px;padding:0;margin:0;' : 'padding-left:15px;font-size:13px;' ?>">
                                <?php foreach($upcomingEvents as $event): ?>
                                    <li style="<?= $liCount > 2 ? 'flex:1 1 45%;list-style:none;' : '' ?>"><?= htmlspecialchars($event->title) ?> <span style="color:#888;">(<?= date('d.m.Y H:i', strtotime($event->start_date)) ?>)</span></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <i class="small-box-icon fas fa-hourglass-half"></i>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary py-3">
                            <h3 class="card-title text-white mb-0">
                                <i class="bi bi-clock-history me-2"></i>Sürüm Notları
                            </h3>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <!-- En yeni sürüm en üstte -->
                                <!-- <li class="list-group-item d-flex align-items-center py-3">
                                    <span class="badge bg-primary rounded-pill me-3">v0.5</span>
                                    <div>
                                        <div class="text-muted small">11 Mart 2025</div>
                                        <div>Yeni özellik eklendi</div>
                                    </div>
                                </li> -->
                                <li class="list-group-item d-flex align-items-center py-3">
                                    <span class="badge bg-primary rounded-pill me-3">v0.5</span>
                                    <div>
                                        <div class="text-muted small">27.04.2025</div>
                                        <h3>Takvim özelliği eklendi.</h3>
                                        <h6> Ajanda sayfasına tıklayarak yeni bir etkinlik oluşturabilirsiniz. Etkinlikleri yönetmek için, takvim sayfasına tıklayabilirsiniz.</h6>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-center py-3">
                                    <span class="badge bg-primary rounded-pill me-3">v0.4</span>
                                    <div>
                                        <div class="text-muted small">24 Mart 2025</div>
                                        <h3>Öneri özelliği eklendi.</h3>
                                        <h6>Fikirlerinizi paylaşmanız için bir alan oluşturuldu. Programda bulunması gereken veya önerdiğiniz bir özellik varsa, lütfen benimle paylaşın.</h6>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-center py-3">
                                    <span class="badge bg-primary rounded-pill me-3">v0.3</span>
                                    <div>
                                        <div class="text-muted small">11 Mart 2025</div>
                                        <h3>Saat ücreti güncelleme özelliği eklendi.</h3>
                                        <h6>Profil sayfasında resminizin altında bulunan ( <i class="text-danger text-bold bi bi-pencil-square"></i> ) işaretine tıklayarak yeni saat ücretinizi update edebilirsiniz. </h6>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary py-3">
                            <h3 class="card-title text-white mb-0">
                                <i class="bi bi-lightbulb me-2"></i>Önerileriniz
                            </h3>
                        </div>
                        <div class="card-body">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars(@$error) ?>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($success)): ?>
                                <div class="alert alert-success" role="alert">
                                    <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                                </div>
                            <?php endif; ?>
                            <form method="POST" class="suggestion-form">
                                <div class="mb-3">
                                    <label for="suggestionTitle" class="form-label">Öneri Başlığı</label>
                                    <input type="text" class="form-control" id="suggestionTitle" name="title" required 
                                           placeholder="Öneriniz için kısa bir başlık">
                                </div>
                                <div class="mb-3">
                                    <label for="suggestionText" class="form-label">Öneriniz</label>
                                    <textarea class="form-control" id="suggestionText" name="suggestion" rows="4" required
                                            placeholder="Sistemde görmek istediğiniz yenilikler, değişiklikler veya iyileştirmeler neler?"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send me-2"></i>Öneri Gönder
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>