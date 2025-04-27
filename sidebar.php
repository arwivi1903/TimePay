<?php
// Aktif sayfayı belirle
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Menü gruplarını ve alt sayfalarını tanımla
$menuGroups = [
    'dashboard' => ['index', 'profil', 'nealirim', 'maastablo', 'mesai', 'zam', 'ajanda'],
    'tools' => ['tatiller', 'vardiya', 'eczane'],
    'admin' => ['sabitler','uyeler','visitors']
];

// Hangi grupta olduğumuzu bul
$activeGroup = '';
foreach ($menuGroups as $group => $pages) {
    if (in_array($currentPage, $pages)) {
        $activeGroup = $group;
        break;
    }
}
?>

<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="index" class="brand-link">
            <img src="dist/assets/img/logo.jpg" alt="TimePay Logo" class="brand-image opacity-75 shadow">
            <span class="brand-text fw-light">TimePay</span>
        </a>
    </div>
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                <li class="nav-item <?= $activeGroup === 'dashboard' ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $activeGroup === 'dashboard' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-speedometer"></i>
                        <p>
                            Dashboard
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="index" class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-house-dash"></i>
                                <p>Sayfam</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="profil" class="nav-link <?= $currentPage === 'profil' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-person"></i>
                                <p>Profil</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="zam" class="nav-link">
                                <i class="nav-icon bi bi-tags-fill"></i>
                                <p>Zam Hesaplama</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="nealirim" class="nav-link <?= $currentPage === 'nealirim' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-cash-coin"></i>
                                <p>Ne Alırım</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="maastablo" class="nav-link <?= $currentPage === 'maastablo' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-calculator"></i>
                                <p>Maaş Hesaplama</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="mesai" class="nav-link <?= $currentPage === 'mesai' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-clock-history"></i>
                                <p>Mesai Takip</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="ajanda" class="nav-link <?= $currentPage === 'ajanda' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-calendar3"></i>
                                <p>Ajanda</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item <?= $activeGroup === 'tools' ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $activeGroup === 'tools' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-box-seam-fill"></i>
                        <p>
                            Araçlar
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="tatiller" class="nav-link <?= $currentPage === 'tatiller' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-airplane"></i>
                                <p>Tatiller</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="eczane" class="nav-link <?= $currentPage === 'eczane' ? 'active' : '' ?>">
                                <i class="nav-icon bi-heart-pulse"></i>
                                <p>Eczane</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="vardiya" class="nav-link <?= $currentPage === 'vardiya' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-clock-history"></i>
                                <p>Vardiya Takip</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php if (yetkili()): ?>
                <li class="nav-item <?= $activeGroup === 'admin' ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $activeGroup === 'admin' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-box-seam-fill"></i>
                        <p>
                            Admin Panel
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="uyeler" class="nav-link <?= $currentPage === 'uyeler' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-person"></i>
                                <p>Üyeler <small class="badge bg-primary"><?= $db->getColumn("SELECT COUNT(UserID) FROM users"); ?></small></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="visitors" class="nav-link <?= $currentPage === 'visitors' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-person-lines-fill"></i>
                                <p>Ziyaretçiler</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="sabitler" class="nav-link <?= $currentPage === 'sabitler' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Sabit Değerler</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="oneri" class="nav-link <?= $currentPage === 'oneri' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-lightbulb"></i>
                                <p>Öneriler <small class="badge bg-success"><?= $db->getColumn("SELECT COUNT(OneriID) FROM oneri WHERE Durum = 'pending'"); ?></small></p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
                
            </ul>
        </nav>
    </div>
</aside>