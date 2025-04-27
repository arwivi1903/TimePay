<?php 
require_once 'header.php';
require_once 'sidebar.php';

class TatilKontrolcu {
    private $ceviriTablosu;

    public function __construct() {
        $this->ceviriTablosu = [
            "New Year's Day" => "Yılbaşı",
            "Labour Day" => "Emek ve Dayanışma Günü",
            "Republic Day" => "Cumhuriyet Bayramı",
            "Victory Day" => "Zafer Bayramı",
            "Democracy and National Unity Day" => "Demokrasi ve Milli Birlik Günü",
            "Sacrifice Feast" => "Kurban Bayramı",
            "Eid al-Fitr" => "Ramazan Bayramı",
            "National Independence & Children's Day" => "Ulusal Egemenlik ve Çocuk Bayramı",
            "Atatürk Commemoration & Youth Day" => "Atatürk'ü Anma Gençlik ve Spor Bayramı"
        ];
    }

    public function cevirTatil($tatilAdi) {
        return isset($this->ceviriTablosu[$tatilAdi]) ? $this->ceviriTablosu[$tatilAdi] : $tatilAdi;
    }

    public function cevirAy($ayAdi) {
        $aylar = [
            "January" => "Ocak", "February" => "Şubat", "March" => "Mart",
            "April" => "Nisan", "May" => "Mayıs", "June" => "Haziran",
            "July" => "Temmuz", "August" => "Ağustos", "September" => "Eylül",
            "October" => "Ekim", "November" => "Kasım", "December" => "Aralık"
        ];
        return isset($aylar[$ayAdi]) ? $aylar[$ayAdi] : $ayAdi;
    }

    public function cevirHaftaGunu($gun) {
        $gunler = [
            "Monday" => "Pazartesi", "Tuesday" => "Salı", "Wednesday" => "Çarşamba",
            "Thursday" => "Perşembe", "Friday" => "Cuma", "Saturday" => "Cumartesi",
            "Sunday" => "Pazar"
        ];
        return isset($gunler[$gun]) ? $gunler[$gun] : $gun;
    }

    public function tatilleriGetir() {
        $yil = date('Y');
        // $apiUrl = "https://date.nager.at/api/v3/publicholidays/{$yil}/TR";
        $apiUrl = "https://calendarific.com/api/v2/holidays?&api_key=oZUzZRtBgIAdl2OTE6ZiZ8jPFZzh30I5&country=TR&year={$yil}";
        try {
            $response = @file_get_contents($apiUrl);
            if ($response === false) {
                throw new Exception("API'ye erişilemedi");
            }
            return json_decode($response, true);
        } catch (Exception $e) {
            return null;
        }
    }

    // Tatil tipine göre renk ve ikon belirleme
    public function getTatilStil($tatilAdi) {
        $stiller = [
            "Yılbaşı" => [
                "renk" => "success",
                "ikon" => "bi-calendar-heart",
                "arkaplan" => "bg-success bg-gradient"
            ],
            "Atatürk'ü Anma" => [
                "renk" => "dark",
                "ikon" => "bi-flag",
                "arkaplan" => "bg-dark bg-gradient"
            ],
            "Zafer Bayramı" => [
                "renk" => "danger",
                "ikon" => "bi-flag-fill",
                "arkaplan" => "bg-danger bg-gradient"
            ],
            "Cumhuriyet Bayramı" => [
                "renk" => "danger",
                "ikon" => "bi-flag-fill",
                "arkaplan" => "bg-danger bg-gradient"
            ],
            "Kurban Bayramı" => [
                "renk" => "info",
                "ikon" => "bi-moon-stars-fill",
                "arkaplan" => "bg-info bg-gradient"
            ],
            "Ramazan Bayramı" => [
                "renk" => "info",
                "ikon" => "bi-moon-stars",
                "arkaplan" => "bg-info bg-gradient"
            ],
            "Ulusal Egemenlik" => [
                "renk" => "primary",
                "ikon" => "bi-people-fill",
                "arkaplan" => "bg-primary bg-gradient"
            ],
            "Emek ve Dayanışma" => [
                "renk" => "warning",
                "ikon" => "bi-briefcase-fill",
                "arkaplan" => "bg-warning bg-gradient"
            ]
        ];

        foreach ($stiller as $anahtar => $stil) {
            if (strpos($tatilAdi, $anahtar) !== false) {
                return $stil;
            }
        }

        return [
            "renk" => "secondary",
            "ikon" => "bi-calendar-event",
            "arkaplan" => "bg-secondary bg-gradient"
        ];
    }
}

$tatilKontrolcu = new TatilKontrolcu();
$tatiller = $tatilKontrolcu->tatilleriGetir();
$bugun = date('Y-m-d');
?>

<main class="app-main">
    <div class="container-fluid">
        <!-- Başlık -->
        <div class="col-12 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h4 class="mb-0">Resmi Tatil Günleri</h4>
                    <small><?= date('Y') ?> yılı resmi tatil takvimi</small>
                </div>
            </div>
        </div>

        <!-- Tatil Kartları -->
        <div class="row">
            <?php if ($tatiller === null): ?>
                <div class="col-12">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i> 
                        Tatil bilgileri şu anda alınamıyor. Lütfen daha sonra tekrar deneyiniz.
                    </div>
                </div>
            <?php elseif (empty($tatiller)): ?>
                <div class="col-12">
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle-fill"></i> 
                        Bu yıl için tatil bilgisi bulunamadı.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($tatiller as $tatil): 
                    $tatilTarihi = $tatil['date'];
                    $tarihObj = new DateTime($tatilTarihi);
                    $durum = $tatilTarihi < $bugun ? 'geçti' : ($tatilTarihi == $bugun ? 'bugün' : 'gelecek');
                    $tatilAdi = $tatilKontrolcu->cevirTatil($tatil['name']);
                    $stil = $tatilKontrolcu->getTatilStil($tatilAdi);
                ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card h-100 <?= $durum == 'geçti' ? 'opacity-50' : '' ?> border-<?= $stil['renk'] ?> shadow-sm">
                        <div class="card-header <?= $stil['arkaplan'] ?> text-white">
                            <i class="bi <?= $stil['ikon'] ?> me-2"></i>
                            <?= $tatilAdi ?>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="mb-4">
                                <h5 class="card-title">
                                    <?= $tarihObj->format('d.m.Y') ?>
                                    <small class="text-muted d-block mt-1">
                                        <?= $tatilKontrolcu->cevirHaftaGunu($tarihObj->format('l')) ?>
                                    </small>
                                </h5>
                            </div>

                            <div class="mt-auto">
                                <?php if ($durum == 'bugün'): ?>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Bugün
                                    </span>
                                <?php elseif ($durum == 'geçti'): ?>
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-clock-history me-1"></i>
                                        Geçti
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-primary">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        <?php 
                                            $kalanGun = $tarihObj->diff(new DateTime())->days;
                                            echo $kalanGun . ' gün kaldı';
                                        ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($durum == 'bugün'): ?>
                        <div class="card-footer bg-success text-white">
                            <small><i class="bi bi-stars"></i> Bugün tatil!</small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Bugün tatilse bildirim göster
    <?php 
    if ($tatiller !== null) {
        foreach ($tatiller as $tatil) {
            if ($tatil['date'] === $bugun) {
                $tatilAdi = $tatilKontrolcu->cevirTatil($tatil['name']);
                echo "
                Swal.fire({
                    icon: 'info',
                    title: 'Bugün Tatil!',
                    text: 'Bugün {$tatilAdi} nedeniyle resmi tatil.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000
                });";
            }
        }
    }
    ?>
});
</script> 