<?php
require_once 'header.php';
require_once 'sidebar.php';
$verial = $db->getRow('SELECT sabitler.YanID, sabitler.YanCocuk, sabitler.YanYakacak, sabitler.YanAyakkabi, sabitler.YanRamazan, sabitler.YanKurban, sabitler.YanIzin FROM sabitler 
WHERE sabitler.YanID = 1');

if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
    $YanCocuk       = temizle($_POST['YanCocuk']);
    $YanYakacak     = temizle($_POST['YanYakacak']);
    $YanAyakkabi    = temizle($_POST['YanAyakkabi']);
    $YanRamazan     = temizle($_POST['YanRamazan']);
    $YanKurban      = temizle($_POST['YanKurban']);
    $YanIzin        = temizle($_POST['YanIzin']);
    $YanID          = $_POST['YanID'];

    if (empty($YanID)) {
        die("Hata: YanID boş olamaz!");
    }

    // Mevcut veriyi veritabanından al
    $mevcutVeri = $db->getRow("SELECT * FROM sabitler WHERE YanID = ?", [$YanID]);

    if ($mevcutVeri) {
        // Güncellenmiş verileri kontrol et
        $degisimVarMi = (
            $YanCocuk   != $mevcutVeri->YanCocuk ||
            $YanYakacak != $mevcutVeri->YanYakacak ||
            $YanAyakkabi != $mevcutVeri->YanAyakkabi ||
            $YanRamazan != $mevcutVeri->YanRamazan ||
            $YanKurban  != $mevcutVeri->YanKurban ||
            $YanIzin    != $mevcutVeri->YanIzin
        );

        if ($degisimVarMi) {
            // Değişiklik varsa güncelle
            $data = [
                'YanCocuk'      => $YanCocuk,
                'YanYakacak'    => $YanYakacak,
                'YanAyakkabi'   => $YanAyakkabi,
                'YanRamazan'    => $YanRamazan,
                'YanKurban'     => $YanKurban,
                'YanIzin'       => $YanIzin
            ];
            $where = ['YanID' => $YanID];
            $guncelle = $db->update('sabitler', $data, $where);

            if ($guncelle) {
                $_SESSION['alert'] = '
                <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
                    <strong>Başarılı!</strong> Yan haklar başarıyla güncellendi.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
                </div>';
            } else {
                $_SESSION['alert'] = '
                <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                    <strong>Hata!</strong> Yan Haklar Güncellenemedi. Lütfen tekrar deneyin.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
                </div>';
            }
        } else {
            // Değişiklik yoksa mesaj ver
            $_SESSION['alert'] = '
            <div class="alert alert-info alert-dismissible fade show mt-2" role="alert">
                <strong>Bilgi!</strong> Hiçbir değişiklik yapılmadı.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
            </div>';
        }
        header("Location: sabitler");
        exit();
    } else {
        // YanID bulunamazsa hata mesajı ver
        $_SESSION['alert'] = '
        <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
            <strong>Hata!</strong> İlgili kayıt bulunamadı. YanID: ' . htmlspecialchars($YanID) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
        </div>';
    }
}
?>
<main class="app-main">
    <div class="app-content-header">
        
    </div>
    <div class="app-content">
        <div class="container-fluid">
            <div class="row g-4">
                <div class="col-md-12">
                    <div class="card card-primary card-outline mb-4">
                        <div class="card-header">
                            <div class="card-title">Yan Haklar</div>
                        </div>
                        <form method="POST" action="">
                            <div class="card-body">
                                <div class="row">
                                    <!-- Sol Sütun -->
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="YanCocuk" class="form-label">Çocuk Parası</label>
                                            <input type="text" class="form-control" id="YanCocuk" name="YanCocuk"
                                                placeholder="Yan Çocuk" value="<?= paraFormatla($verial->YanCocuk) ?>"
                                                aria-describedby="YanCocuk">
                                            <div id="YanCocuk" class="form-text">
                                                Her bir çocuk için ayrı ayrı belirtilen miktar.
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="YanYakacak" class="form-label">Aylık Yakacak Parası</label>
                                            <input type="text" class="form-control" id="YanYakacak" name="YanYakacak"
                                                placeholder="Yan Yakacak"
                                                value="<?= paraFormatla($verial->YanYakacak) ?>"
                                                aria-describedby="YanYakacak">
                                            <div id="YanYakacak" class="form-text">
                                                Her ay düzenli verilir.
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="YanAyakkabi" class="form-label">Ayakkabı Parası</label>
                                            <input type="text" class="form-control" id="YanAyakkabi" name="YanAyakkabi"
                                                placeholder="Yan Ayakkabı"
                                                value="<?= paraFormatla($verial->YanAyakkabi) ?>"
                                                aria-describedby="YanAyakkabi">
                                            <div id="YanAyakkabi" class="form-text">
                                                Sene de iki defa (Mayıs-Ekim aylarında) verilir.
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sağ Sütun -->
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="YanRamazan" class="form-label">Ramazan Bayram Parası</label>
                                            <input type="text" class="form-control" id="YanRamazan" name="YanRamazan"
                                                placeholder="Yan Ramazan"
                                                value="<?= paraFormatla($verial->YanRamazan) ?>"
                                                aria-describedby="YanRamazan">
                                            <div id="YanRamazan" class="form-text">
                                                Ramazan ayında verilir.
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="YanKurban" class="form-label">Kurban Bayram Parası</label>
                                            <input type="text" class="form-control" id="YanKurban" name="YanKurban"
                                                placeholder="Yan Kurban" value="<?= paraFormatla($verial->YanKurban) ?>"
                                                aria-describedby="YanKurban">
                                            <div id="YanKurban" class="form-text">
                                                Kurban Bayramında verilir.
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="YanIzin" class="form-label">İzin Parası</label>
                                            <input type="text" class="form-control" id="YanIzin" name="YanIzin"
                                                placeholder="Yan İzin" value="<?= paraFormatla($verial->YanIzin) ?>"
                                                aria-describedby="YanIzin">
                                            <div id="YanIzin" class="form-text">
                                                İzin hak ediş zamanında verilir.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="YanID" value="<?= $verial->YanID ?>">
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Kaydet</button>
                                </div>
                            </div>
                        </form>
                        <?php alert(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require_once 'footer.php'; ?>