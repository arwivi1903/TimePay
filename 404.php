<?php 
include 'header.php'; 
include 'sidebar.php';
?>

<div class="container-fluid">
    <div class="card card-primary mt-2">
        <section class="content">
            <div class="error-page">
                <h2 class="headline text-warning">404</h2>

                <div class="error-content">
                    <h3><i class="fas fa-exclamation-triangle text-warning"></i> Oops! Sayfa bulunmadı.</h3>
                    <br>
                    <p>  <?= $_SESSION['UserName']?> 'den uyarı.... <br>
                        Aradığınız sayfayı bulamadık.
                        Buradan <a href="index" t><b>ANASAYFA </b></a> dönebilirsiniz.
                        Teknik destek için <a target="_blank" href="https://api.whatsapp.com/send?phone=905327398000&text=Ekolay%20Mutfak%20Sayfada%20Bir%20Sorun%20Var">0532 739 80 00</a> numarayı arayanız.
                    </p>
                </div>
            </div>
        </section>
    </div>
</div>


<?php
include 'footer.php';
?>