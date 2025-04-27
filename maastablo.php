<?php 
require_once 'header.php'; 
require_once 'sidebar.php';

$maasHesaplayici = new MaasHesaplayici($SaatUcreti);

// Vergi dilimleri
$vergiDilimleri = [
    15 => ['limit' => 158000],
    20 => ['limit' => 330000],
    27 => ['limit' => 1200000],
    35 => ['limit' => 4300000]
];

// Brüt maaş hesaplama
$brutMaas = $maasHesaplayici->brutMaas();
$ikramiyeDahilBrut = $maasHesaplayici->ikramiyeDahilBrut();
$ikramiye = $maasHesaplayici->ikramiye();
$avans = $maasHesaplayici->netAvans();

?>

<main class="app-main">
    <div class="container-fluid">
        <!-- Başlık -->
        <div class="col-12 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h4 class="mb-0">Maaş Tablosu</h4>
                    <small>Vergi dilimlerine göre detaylı maaş tablosu</small>
                </div>
            </div>
        </div>

        <!-- Filtreler -->
        <div class="col-12 mb-4">
            <div class="btn-group">
                <button type="button" class="btn btn-light" data-filter="all">Tümü</button>
                <button type="button" class="btn btn-success" data-filter="vergi-15">%15 Vergi</button>
                <button type="button" class="btn btn-warning" data-filter="vergi-20">%20 Vergi</button>
                <button type="button" class="btn btn-info" data-filter="vergi-27">%27 Vergi</button>
                <button type="button" class="btn btn-danger" data-filter="vergi-35">%35 Vergi</button>
            </div>
        </div>

        <!-- Maaş Tablosu -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Vergi Dilimi</th>
                                    <th>Net Maaş</th>
                                    <th>İkramiye Dahil</th>
                                    <th>İkramiyeli Net</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vergiDilimleri as $oran => $bilgi): 
                                    // Her vergi dilimi için hesaplamalar
                                    $vergiOrani = $oran / 100;
                                    
                                    // Normal maaş hesabı
                                    $vergiTutari = $brutMaas * $vergiOrani;
                                    $netMaas = $brutMaas - $vergiTutari - ($brutMaas * 0.14) - ($brutMaas * 0.01);
                                    
                                    // İkramiyeli hesaplar
                                    $ikramiyeliVergi = $ikramiyeDahilBrut * $vergiOrani;
                                    $ikramiyeliNet = $ikramiyeDahilBrut - $ikramiyeliVergi - ($ikramiyeDahilBrut * 0.14) - ($ikramiyeDahilBrut * 0.01);
                                    
                                    $tamIkramiyeVergi = $ikramiye * $vergiOrani;
                                    $tamIkramiyeNet = $ikramiye - $tamIkramiyeVergi - ($ikramiye * 0.14) - ($ikramiye * 0.01);
                                ?>
                                <tr class="vergi-<?= $oran ?>">
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-<?= $oran == 15 ? 'success' : ($oran == 20 ? 'warning' : ($oran == 27 ? 'info' : 'danger')) ?>" 
                                                 style="width: <?= 100 - $oran ?>%">
                                                %<?= $oran ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= paraFormatla($netMaas) ?></td>
                                    <td><?= paraFormatla($ikramiyeliNet) ?></td>
                                    <td><?= paraFormatla($tamIkramiyeNet) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bilgi Kartları -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5>Brüt Maaş</h5>
                        <h3><?= paraFormatla($brutMaas) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5>İkramiye Dahil</h5>
                        <h3><?= paraFormatla($ikramiyeDahilBrut) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5>Avans Miktarı</h5>
                        <h3><?= paraFormatla($avans) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h5>İkramiyeli Brüt</h5>
                        <h3><?= paraFormatla($ikramiye) ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notlar -->
        <div class="card mt-4">
            <div class="card-body">
                <h5>Önemli Notlar</h5>
                <ul>
                    <li>Vergi dilimleri <?= date('Y') ?> yılına aittir</li>
                    <li>Hesaplamalar yaklaşık değerlerdir</li>
                    <li>SGK kesintisi %14, işsizlik kesintisi %1 olarak hesaplanmıştır</li>
                </ul>
            </div>
        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filtreleme işlevi
    const filterButtons = document.querySelectorAll('[data-filter]');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.dataset.filter;
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                if (filter === 'all' || row.classList.contains(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Aktif buton stilini güncelle
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
        });
    });
});
</script> 