<?php
require_once "header.php";
require_once "sidebar.php";

define('API_KEY', '0ypkbVyW3cBqfCsejrcL5q:0mNWiLDReGDczqTuhtYWnN');

// API işlemlerini ayrı bir sınıfa taşıyalım
class EczaneAPI {
    private $apiKey;
    private $apiUrl;

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
        $this->apiUrl = "https://api.collectapi.com/health/dutyPharmacy";
    }

    public function getNobetciEczaneler($il) {
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiUrl . "?il=" . urlencode($il),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "authorization: apikey " . $this->apiKey,
                "content-type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            throw new Exception("API Hatası: " . $err);
        }

        return json_decode($response, true);
    }
}

// View kısmını güncelleyelim
function renderEczaneCards($eczaneler) {
    if (!$eczaneler || !isset($eczaneler['result'])) {
        return "<div class='alert alert-danger'>Veri bulunamadı.</div>";
    }

    $groupedPharmacies = [];
    foreach ($eczaneler['result'] as $eczane) {
        $district = $eczane['dist'];
        $groupedPharmacies[$district][] = $eczane;
    }
    ksort($groupedPharmacies);

    $result = $eczaneler['result'];
    usort($result, function($a, $b) {
        return strcmp($a['dist'], $b['dist']);
    });
    $eczaneler['result'] = $result;

    ob_start();
    ?>
    <div class="row clearfix">
        <?php foreach ($eczaneler['result'] as $eczane): ?>
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-3">
                <div class="card custom-card h-100">
                    <div class="card-header bg-primary text-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fa fa-plus-square mr-2"></i>
                                <?= htmlspecialchars($eczane['name']) ?>
                            </h5>
                            <h5 class="card-title mb-0">
                                <i class="fa fa-map-marker-alt mr-1"></i>
                                <?= htmlspecialchars($eczane['dist']) ?>
                            </h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="info-item mb-3">
                            <i class="fa fa-map-marker-alt text-muted mr-2"></i>
                            <span><?= htmlspecialchars($eczane['address']) ?></span>
                        </div>
                        <div class="info-item">
                            <i class="fa fa-phone text-muted mr-2"></i>
                            <a href="tel:<?= htmlspecialchars($eczane['phone']) ?>" 
                               class="text-primary font-weight-bold">
                                <?= htmlspecialchars($eczane['phone']) ?>
                            </a>
                        </div>
                    </div>
                    <div class="card-footer bg-light border-0">
                        <div class="row">
                            <div class="col-6">
                                <a href="tel:<?= htmlspecialchars($eczane['phone']) ?>" 
                                   class="btn btn-success btn-sm btn-block">
                                    <i class="fa fa-phone mr-2"></i>Telefonla Ara
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="https://maps.google.com/?q=<?= urlencode($eczane['address']) ?>" 
                                   target="_blank" 
                                   class="btn btn-primary btn-sm btn-block">
                                    <i class="fa fa-map mr-2"></i>Google Harita
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
?>
<main class="app-main">
<div id="main-content">
    <div class="container-fluid">
        <div class="row clearfix">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card bg-primary text-white">
                        <h2>Nöbetçi Eczaneler <small><?= date('d.m.Y') ?></small></h2>
                    </div>
                    <div class="card-body">
                        <?php
                        try {
                            $eczaneAPI = new EczaneAPI(API_KEY);
                            $data = $eczaneAPI->getNobetciEczaneler("KOCAELI");
                            echo renderEczaneCards($data);
                        } catch (Exception $e) {
                            echo "<div class='alert alert-danger'>" . htmlspecialchars($e->getMessage()) . "</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</main>
<?php require_once "footer.php"; ?>

<style>
.custom-card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    display: flex;
    flex-direction: column;
}

.custom-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.custom-card .card-header {
    border-radius: 10px 10px 0 0;
    border: none;
}

.custom-card .card-title {
    font-size: 1.1rem;
    line-height: 1.4;
    margin-bottom: 0;
}

.custom-card .card-header small {
    font-size: 0.85rem;
    opacity: 0.9;
}

.info-item {
    display: flex;
    align-items: start;
}

.info-item i {
    margin-top: 4px;
    font-size: 1.1rem;
    width: 20px;
}

.info-item span, 
.info-item a {
    flex: 1;
    line-height: 1.5;
}

.card-footer .btn {
    padding: 8px;
    font-weight: 500;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
}

.card-footer .btn i {
    font-size: 0.9rem;
}

.card-body {
    flex: 1 1 auto;
    max-height: none;
    overflow-y: visible;
    padding: 1rem;
}

.info-item {
    word-break: break-word;
}

.row.clearfix {
    margin-right: -10px;
    margin-left: -10px;
}

.row.clearfix > [class*="col-"] {
    padding-right: 10px;
    padding-left: 10px;
}

/* Mobil cihazlar için ek düzenlemeler */
@media (max-width: 576px) {
    .custom-card {
        margin-bottom: 15px;
    }
    
    .card-body {
        padding: 0.75rem;
    }
}
</style>

<script>
$(document).ready(function() {
    function equalizeCardHeights() {
        // Önce tüm card-body'lerin yüksekliğini sıfırlayalım
        $('.card-body').css('height', 'auto');
        
        // Her satır için maksimum yüksekliği bulalım
        $('.row.clearfix').each(function() {
            var currentRow = $(this);
            var maxHeight = 0;
            
            // Bu satırdaki tüm card-body'leri kontrol edelim
            currentRow.find('.card-body').each(function() {
                var currentHeight = $(this).outerHeight();
                maxHeight = Math.max(maxHeight, currentHeight);
            });
            
            // Maksimum yükseklik makul bir değer ise uygulayalım
            if (maxHeight > 0 && maxHeight < 500) { // 500px makul bir üst sınır
                currentRow.find('.card-body').height(maxHeight);
            }
        });
    }

    // Sayfa yüklendiğinde çalıştır
    equalizeCardHeights();
    
    // Pencere boyutu değiştiğinde yeniden hesapla
    var resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(equalizeCardHeights, 250);
    });
});
</script> 
