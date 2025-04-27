<?php 
require_once 'header.php';
require_once 'sidebar.php';

$baslangicTarihi = isset($_SESSION['StartDate']) && $_SESSION['StartDate'] ? new DateTime($_SESSION['StartDate']) : new DateTime();
$bitisTarihi     = $baslangicTarihi->diff(new DateTime());

$maasHesaplayici = new MaasHesaplayici($SaatUcreti);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ProfilGüncelle'])) 
{
    $inputShift     = $_POST['Shift'];  
    $inputStartDate = $_POST['StartDate'];  
    $inputPass      = isset($_POST['inputPass']) ? trim($_POST['inputPass']) : '';
    
    $dosyayolu = 'dist/assets/img/';
    $uploadOk = 1;
    $uploadedFilePath = null;

    if (isset($_FILES['inputPic']) && $_FILES['inputPic']['error'] == UPLOAD_ERR_OK) {
        $fileName = basename($_FILES["inputPic"]["name"]);
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newName = uniqid("user_") . "." . $fileType;
        $uploadedFilePath = $dosyayolu . $newName;

        if (!in_array($fileType, ["jpg", "jpeg", "png", "gif"])) {
            $uploadOk = 0;
            swallAlert('error', 'Geçersiz dosya formatı. Sadece JPG, JPEG, PNG veya GIF dosyaları yüklenebilir.', 'profil.php');
            exit;
        }

        if ($uploadOk && !move_uploaded_file($_FILES["inputPic"]["tmp_name"], $uploadedFilePath)) {
            $uploadOk = 0;
            swallAlert('error', 'Dosya yüklenemedi. Lütfen tekrar deneyiniz.', 'profil.php');
            exit;
        }
    }

    $data = [
        'Shift' => $inputShift,  
        'StartDate' => $inputStartDate,  
    ];

    if (!empty($inputPass)) {
        if (strlen($inputPass) < 6) {
            swallAlert('error', 'Şifre en az 6 karakter olmalıdır.', 'profil.php');
            exit;
        }
        $data['UserPass'] = password_hash($inputPass, PASSWORD_DEFAULT);
    }

    if ($uploadOk && $uploadedFilePath) {
        $data['UserPicture'] = $uploadedFilePath;
        $_SESSION['UserPicture'] = $uploadedFilePath;  
    }

    $where = ['UserID' => $_SESSION['UserID']];
    $update = $db->update('users', $data, $where);

    if ($update) {
        swallAlert('success', 'Profil başarıyla güncellendi.', 'profil.php');
        exit;
    } else {
        swallAlert('error', 'Profil güncelleme işlemi başarısız. Lütfen bir değişiklik yaptığınızdan emin olun.', 'profil.php');
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['UppSaatUcret']))
{
    $data = ['HourlyRate' => encryptData($_POST['NewPrice'])];
    $where = ['UserID' => $_SESSION['UserID']];
    $update = $db->update('users', $data, $where);

    if ($update) {
        swallAlert('success', 'Saat ücreti başarıyla güncellendi.', 'profil.php');
        exit;
    } else {
        swallAlert('error', 'Saat ücreti güncelleme işlemi başarısız. Lütfen bir değişiklik yaptığınızdan emin olun.', 'profil.php');
        exit;
    }    
}

?>
<main class="app-main">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <img class="profile-user-img img-fluid img-circle" src="<?= $_SESSION['UserPicture'] ?>"
                                alt="User profile picture">
                        </div>
                        <h3 class="profile-username text-center"><?= $_SESSION['UserName'] ?></h3>
                        <p class="text-muted text-center"> Vardiyam : <?= $_SESSION['Shift'] ?> </p>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-cash-coin me-2"></i><b>Saat Ücreti:</b></span>
                                <span class="badge bg-primary rounded-pill"><?= paraFormatla($SaatUcreti) ?> &nbsp;&nbsp;&nbsp; <i class="bi bi-pencil-square" data-bs-toggle="modal" data-bs-target="#editSaatUcretModal"></i></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-calendar-date me-2"></i><b>İş Başı Tarihi:</b></span>
                                <span class="text-muted"><?= tr_date($_SESSION['StartDate']) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-hourglass-split me-2"></i><b>Kıdem:</b></span>
                                <span class="text-success">
                                    <?= $bitisTarihi->y ?> Yıl - <?= $bitisTarihi->m ?> Ay - <?= $bitisTarihi->d ?> Gün
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-clock-history me-2"></i><b>Üye Tarihi:</b></span>
                                <span class="text-muted"><?= tr_datetime($_SESSION['CreatedDate']) ?></span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card card-primary mt-2">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Kullanıcı Bilgileri</h5>
                        </div>
                        <div class="card-body">
                            <!-- Email Adresi -->
                            <div class="mb-3">
                                <h6><i class="far fa-file-alt me-2"></i>Email Adresi</h6>
                                <p class="text-muted"><?= $_SESSION['UserMail'] ?></p>
                            </div>
                            <hr>

                            <!-- Brüt Maaş -->
                            <div class="mb-3">
                                <h6><i class="fas fa-book me-2"></i>Brüt Maaş</h6>
                                <p class="text-muted">
                                    <?= paraFormatla($maasHesaplayici->brutMaas()); ?>
                                </p>
                            </div>
                            <hr>

                            <!-- Avans -->
                            <div class="mb-3">
                                <h6><i class="fas fa-wallet me-2"></i>Avans</h6>
                                <p class="text-muted">
                                    <?= paraFormatla($maasHesaplayici->netAvans()); ?>
                                </p>
                            </div>
                            <hr>

                            <!-- İkramiye Dahil Brüt -->
                            <div class="mb-3">
                                <h6><i class="fas fa-money-check-alt me-2"></i>İkramiye Dahil Brüt</h6>
                                <p class="text-muted">
                                    <?= paraFormatla($maasHesaplayici->ikramiyeDahilBrut()); ?>
                                </p>
                            </div>
                            <hr>

                            <!-- Vergi Dilimleri -->
                            <div class="mb-3">
                                <h6><i class="fas fa-calculator me-2"></i>Vergi Dilimleri</h6>
                                <ul class="list-unstyled text-muted">
                                    <li>158.000₺'e kadar : <strong>%15</strong></li>
                                    <li>330.000₺'e kadar : <strong>%20</strong></li>
                                    <li>1.200.000₺'e kadar : <strong>%27</strong></li>
                                    <li>4.300.000₺'e kadar : <strong>%35</strong></li>
                                    <li>4.300.000₺ üstü : <strong>%40</strong></li>
                                </ul>
                            </div>
                            <hr>

                            <!-- Notlar -->
                            <div class="text-muted small">
                                <ul>
                                    <li>İkramiye ve Avanslar brüt maaşa dahil edilmiştir.</li>
                                    <li>Vergi dilimleri <?= date("Y") ?> yılına aittir.</li>
                                    <li>Rakamlar yaklaşık olarak hesaplanmıştır, sadece bilgi amaçlıdır.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            <li class="nav-item btn btn-primary">Profil Düzenleme</li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="active tab-pane">
                                <form class="form-horizontal" method="post" action="" enctype="multipart/form-data">
                                    <div class="form-group row">
                                        <label for="inputName" class="col-sm-2 col-form-label">Ad Soyad</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="inputName" name="inputName"
                                                value="<?= $_SESSION['UserName'] ?>" placeholder="Name" disabled
                                                readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row mt-2">
                                        <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                                        <div class="col-sm-10">
                                            <input type="email" class="form-control" id="inputEmail" name="inputEmail"
                                                value="<?= $_SESSION['UserMail'] ?>" placeholder="Email" disabled
                                                readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row mt-2">
                                        <label for="inputPass" class="col-sm-2 col-form-label">Şifre</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="inputPass" name="inputPass"
                                                placeholder="Şifrenizi Değiştirmek İçin Yazınız">
                                        </div>
                                    </div>
                                    <div class="form-group row mt-2">
                                        <label for="Price" class="col-sm-2 col-form-label">Saat Ücreti</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="Price" name="Price"
                                                value="<?= paraFormatla($SaatUcreti) ?>" placeholder="Saat Ücreti"
                                                disabled readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row mt-2">
                                        <label for="Shift" class="col-sm-2 col-form-label">Vardiyanız</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" id="Shift" name="Shift">
                                                <option value="A" <?= ($_SESSION['Shift'] === 'A') ? 'selected' : '' ?>>
                                                    A</option>
                                                <option value="B" <?= ($_SESSION['Shift'] === 'B') ? 'selected' : '' ?>>
                                                    B</option>
                                                <option value="C" <?= ($_SESSION['Shift'] === 'C') ? 'selected' : '' ?>>
                                                    C</option>
                                                <option value="D" <?= ($_SESSION['Shift'] === 'D') ? 'selected' : '' ?>>
                                                    D</option>
                                                <option value="G" <?= ($_SESSION['Shift'] === 'G') ? 'selected' : '' ?>>
                                                    G</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mt-2">
                                        <label for="StartDate" class="col-sm-2 col-form-label">İş Başı Tarihi</label>
                                        <div class="col-sm-10">
                                            <input type="date" class="form-control" id="StartDate" name="StartDate"
                                                value="<?= $_SESSION['StartDate'] ?>" placeholder="Vardiyam">
                                        </div>
                                    </div>
                                    <div class="form-group row mt-2">
                                        <label for="inputPic" class="col-sm-2 col-form-label">Resim</label>
                                        <div class="col-sm-10">
                                            <input type="file" class="form-control" id="inputPic" name="inputPic"
                                                placeholder="Resim Seçiniz">
                                        </div>
                                    </div>
                                    <input type="hidden" id="UserID" name="UserID" value="<?= $_SESSION['UserID'] ?>">
                                    <div class="form-group row">
                                        <div class="offset-sm-2 col-sm-10 mt-3">
                                            <input type="submit" class="btn btn-primary" name="ProfilGüncelle" value="Güncelle">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Saat Ücreti Düzenleme Modal -->
    <div class="modal fade" id="editSaatUcretModal" tabindex="-1" aria-labelledby="editSaatUcretModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSaatUcretModalLabel">Saat Ücreti Düzenleme</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editSaatUcretForm" action="" method="post">
                        <div class="mb-3">
                            <label for="editSaatUcret" class="form-label">Saat Ücreti</label>
                            <input type="text" min="0" pattern="^₺?\s*\d+([.,]\d{0,2})?$" class="form-control" id="editSaatUcret" name="NewPrice" value="<?= $SaatUcreti ?>">
                            <label for="editSaatUcret" class="form-label">Saat ücretini (100.00) olarak giriniz.</label>  
                        </div>
                        <button type="submit" class="btn btn-primary" name="UppSaatUcret">Güncelle</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
include 'footer.php';
?>

<script>
<?php
if (isset($_SESSION['swall'])) {
    $alert = $_SESSION['swall'];
    unset($_SESSION['swall']);
    ?>
    Swal.fire({
        icon: '<?php echo $alert['type']; ?>',
        title: '<?php echo $alert['type'] == 'success' ? 'Başarılı!' : 'Hata!'; ?>',
        text: '<?php echo $alert['message']; ?>',
        <?php if ($alert['type'] == 'success') { ?>
        showConfirmButton: false,
        timer: 1500
        <?php } else { ?>
        confirmButtonText: 'Tamam'
        <?php } ?>
    });
    <?php
}
?>
</script>