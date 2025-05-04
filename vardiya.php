<?php 
require_once 'header.php';
require_once 'sidebar.php';

// Kullanıcı kontrolü
if ($_SESSION['Shift'] === 'D' || $_SESSION['Shift'] === 'G') {
    $mesaj = $_SESSION['Shift'] === 'D' ? '4\'lü vardiya sistemi için çalışıyorum, Vardiya planınız yakın zamanda hazırlanacaktır.' : 'Vardiya planınız yakın zamanda hazırlanacaktır.';
    ?>
    <main class="app-main">
        <div class="container-fluid">
            <div class="col-12">
                <div class="card bg-info text-white">
                    <div class="card-body text-center py-5">
                        <h2 class="mb-4"><?= $_SESSION['UserName'] ?></h2>
                        <p class="lead mb-4"><?= $mesaj ?></p>
                        <div class="d-flex justify-content-center">
                            <div class="spinner-border text-light" role="status">
                                <span class="visually-hidden">Yükleniyor...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php
    require_once 'footer.php';
    exit;
}

// Vardiya bilgilerini al
$vardiyalar = [
    'A' => [
        'saat' => '00:00 - 08:00',
        'baslik' => 'Vardiya (00:00 - 08:00)',
        'renk' => 'primary',
        'ikon' => 'bi-moon-stars-fill'
    ],
    'B' => [
        'saat' => '08:00 - 16:00',
        'baslik' => 'Vardiya (08:00 - 16:00)',
        'renk' => 'success',
        'ikon' => 'bi-sun-fill'
    ],
    'C' => [
        'saat' => '16:00 - 00:00',
        'baslik' => 'Vardiya (16:00 - 00:00)',
        'renk' => 'warning',
        'ikon' => 'bi-sunset-fill'
    ]
];

// Aktif vardiya
$aktifVardiya = $_SESSION['Shift'];

// Vardiya değişim zamanları
$vardiyaDegisimZamanlari = [
    'A' => ['baslangic' => '00:00', 'bitis' => '07:59'],
    'B' => ['baslangic' => '08:00', 'bitis' => '15:59'],
    'C' => ['baslangic' => '16:00', 'bitis' => '23:59']
];

// Şu anki vardiyayı bul
$simdikiSaat = date('H:i');
$aktifCalismaSaati = null;
foreach ($vardiyaDegisimZamanlari as $vardiya => $zaman) {
    if ($simdikiSaat >= $zaman['baslangic'] && $simdikiSaat < $zaman['bitis']) {
        $aktifCalismaSaati = $vardiya;
        break;
    }
}

// Haftanın başlangıç ve bitiş tarihlerini hesaplayan fonksiyon
function getHaftaninBaslangici($tarih = null) {
    if ($tarih === null) {
        $tarih = new DateTime();
    } else if (is_string($tarih)) {
        $tarih = new DateTime($tarih);
    }
    
    // Pazartesi gününe ayarla (1 = Pazartesi, 7 = Pazar)
    $gun = $tarih->format('N');
    if ($gun != 1) {
        $tarih->modify('-' . ($gun - 1) . ' days');
    }
    
    return $tarih;
}

// Vardiya planını güncelle
function getVardiyaPlan($baslangicVardiya, $baslangicTarihi, $haftaSayisi = 52) {
    try {
        $plan = [];
        // Bu haftanın başlangıcını al
        $tarih = getHaftaninBaslangici();
        $mevcutVardiya = $baslangicVardiya;
        
        // Başlangıç vardiyasını hesapla
        $baslangicHaftasi = getHaftaninBaslangici($baslangicTarihi);
        $haftaFarki = floor($tarih->diff($baslangicHaftasi)->days / 7);
        
        // Vardiya sırası
        $vardiyaSirasi = [
            'C' => 'B',
            'B' => 'A',
            'A' => 'C'
        ];
        
        // Hata logları: geliştirme modunda kontrol edilebilir, üretimde devre dışı bırakıldı
        // Başlangıç vardiyasından şu anki vardiyayı hesapla
        for ($i = 0; $i < abs($haftaFarki); $i++) {
            $mevcutVardiya = $vardiyaSirasi[$mevcutVardiya] ?? $mevcutVardiya;
            // error_log("Hafta " . $i . " Vardiya: " . $mevcutVardiya);
        }
        
        // Planı oluştur
        for ($hafta = 0; $hafta < $haftaSayisi; $hafta++) {
            $haftaBaslangic = clone $tarih;
            $haftaBitis = (clone $tarih)->modify('+6 days');
            
            $plan[] = [
                'baslangic' => $haftaBaslangic,
                'bitis' => $haftaBitis,
                'vardiya' => $mevcutVardiya
            ];
            
            $tarih->modify('+7 days');
            $mevcutVardiya = $vardiyaSirasi[$mevcutVardiya] ?? $mevcutVardiya;
        }
        
        return $plan;
    } catch (Exception $e) {
        error_log("getVardiyaPlan Hatası: " . $e->getMessage());
        return [];
    }
}

// Planı oluştur
$plan = getVardiyaPlan($_SESSION['Shift'], $_SESSION['StartDate'], 52);
?>

<main class="app-main">
    <div class="container-fluid">
        <!-- Başlık -->
        <div class="col-12 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h4 class="mb-0">Vardiya Takip</h4>
                    <small>Vardiya planı ve çalışma saatleri</small>
                </div>
            </div>
        </div>

        <!-- Vardiya Kartları -->
        <div class="row">
            <?php foreach ($vardiyalar as $kod => $vardiya): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 <?= $aktifVardiya === $kod ? 'border-' . $vardiya['renk'] . ' shadow' : '' ?>">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-lg bg-<?= $vardiya['renk'] ?> bg-opacity-10 rounded-circle">
                                    <i class="bi <?= $vardiya['ikon'] ?> text-<?= $vardiya['renk'] ?> fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-0"><?= $vardiya['baslik'] ?></h5>
                                <small class="text-muted"><?= $vardiya['saat'] ?></small>
                            </div>
                            <?php if ($aktifVardiya === $kod): ?>
                            <div class="badge bg-<?= $vardiya['renk'] ?> p-2">
                                <i class="bi bi-person-check-fill me-1"></i>
                                Sizin Vardiyanız
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="progress" style="height: 8px;">
                            <?php
                            // Vardiya ilerleme durumu
                            $baslangic = strtotime($vardiyaDegisimZamanlari[$kod]['baslangic']);
                            $bitis = strtotime($vardiyaDegisimZamanlari[$kod]['bitis']);
                            $simdi = strtotime($simdikiSaat);
                            
                            if ($simdi >= $baslangic && $simdi <= $bitis) {
                                $ilerleme = (($simdi - $baslangic) / ($bitis - $baslangic)) * 100;
                            } else {
                                $ilerleme = 0;
                            }
                            ?>
                            <div class="progress-bar bg-<?= $vardiya['renk'] ?>" 
                                 role="progressbar" 
                                 style="width: <?= $ilerleme ?>%" 
                                 aria-valuenow="<?= $ilerleme ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                        <div class="text-end mt-1">
                            <small class="text-muted"><?= number_format($ilerleme, 1) ?>%</small>
                        </div>

                        <?php if ($kod === $aktifCalismaSaati): ?>
                        <div class="mt-3">
                            <span class="badge bg-<?= $vardiya['renk'] ?>">
                                <i class="bi bi-clock-fill me-1"></i>
                                Aktif Çalışma Saati
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Vardiya Planı -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Vardiya Planı</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php 
                            // Planı bir kere oluştur ve kullan
                            foreach ($plan as $index => $hafta): 
                                $buHafta = $hafta['baslangic'] <= new DateTime() && $hafta['bitis'] >= new DateTime();
                                $vardiyaBilgi = $vardiyalar[$hafta['vardiya']];
                            ?>
                            <div class="col-md-3 mb-3">
                                <div class="card <?= $buHafta ? 'border-' . $vardiyaBilgi['renk'] . ' shadow' : '' ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="badge bg-<?= $vardiyaBilgi['renk'] ?> px-3 py-2">
                                                <?= $index === 0 ? 'Önümüzdeki Hafta' : ($index + 1) . '. Hafta Sonra' ?>
                                            </span>
                                            <div class="avatar avatar-sm bg-<?= $vardiyaBilgi['renk'] ?> bg-opacity-10 rounded-circle">
                                                <i class="bi <?= $vardiyaBilgi['ikon'] ?> text-<?= $vardiyaBilgi['renk'] ?>"></i>
                                            </div>
                                        </div>
                                        <h6 class="mb-2">
                                            <?= $hafta['baslangic']->format('d.m.Y') ?> - 
                                            <?= $hafta['bitis']->format('d.m.Y') ?>
                                        </h6>
                                        <div class="d-flex align-items-center">
                                            <strong><?= $vardiyalar[$hafta['vardiya']]['baslik'] ?></strong>
                                            <small class="ms-2 text-muted"><?= $vardiyalar[$hafta['vardiya']]['saat'] ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vardiya renk güncellemesi -->
        <style>
        .bg-gradient-primary {
            background: linear-gradient(45deg, #4e73df 0%, #224abe 100%);
        }

        .avatar {
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-sm {
            width: 2rem;
            height: 2rem;
        }

        .avatar-lg {
            width: 3.5rem;
            height: 3.5rem;
        }
        </style>

        <script>
        // Vardiya bilgilerini JavaScript'te kullanabilmek için
        const vardiyalar = <?= json_encode($vardiyalar) ?>;
        </script>
    </div>
</main>

<?php require_once 'footer.php'; ?> 