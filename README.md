
# 💼 Maaş Yönetim Sistemi

PHP ile geliştirilmiş, kuruluşların çalışan maaşlarını, vardiyalarını ve ilgili hesaplamaları verimli bir şekilde yönetmelerine olanak tanıyan kapsamlı bir web tabanlı maaş yönetim sistemi.

---

## 🚀 Özellikler

### Kullanıcı Yönetimi
- Kullanıcı kayıt ve giriş sistemi
- Şifre sıfırlama işlemleri
- Profil düzenleme ve güncelleme

### Maaş ve Vardiya Yönetimi
- Maaş hesaplama modülü
- Vardiya planlama ve yönetimi
- Fazla mesai takibi
- Zam ve saat ücreti güncellemeleri

### Ekstra Özellikler
- Eczane bilgi yönetimi
- Tatil günleri takibi
- Öneri ve geri bildirim sistemi
- Ziyaretçi istatistik raporlaması
- Detaylı kullanıcı yönetimi

---

## 🛠️ Teknik Altyapı

- **Backend:** PHP
- **Veritabanı:** MySQL
- **Arayüz:** AdminLTE şablonu
- **E-posta Yönetimi:** PHPMailer
- **Bağımlılık Yönetimi:** Composer

---

## 📁 Proje Dizin Yapısı

```
├── classes/           # Temel PHP sınıfları
├── config/            # Yapılandırma dosyaları
├── dist/              # Ön yüz varlıkları (CSS, JS, img)
├── functions/         # Yardımcı fonksiyonlar
├── src/               # Uygulama kaynak dosyaları
├── vendor/            # Composer bağımlılıkları
```

---

## ⚙️ Kurulum Adımları

1. Projeyi web sunucu dizininize klonlayın.
2. PHP ve MySQL servislerinin kurulu olduğundan emin olun.
3. Terminal üzerinden proje klasörüne gelip bağımlılıkları yükleyin:
   ```bash
   composer install
   ```
4. Veritabanı ayarlarınızı `config/` dizinindeki dosyalardan yapılandırın.
5. E-posta sunucu ayarlarınızı `config/mail.php` dosyasında düzenleyin.

---

## 📌 Önemli Dosyalar

- `classes/Database.class.php` → Veritabanı bağlantı yönetimi
- `classes/MaasHesaplayici.class.php` → Maaş hesaplama mantığı
- `classes/Registration.class.php` → Kullanıcı kayıt ve oturum yönetimi
- `functions/allFunctions.php` → Yardımcı fonksiyonlar ve araçlar

---

## 🔒 Güvenlik Önlemleri

- Şifreler güvenli bir şekilde hash'lenir.
- SQL enjeksiyon saldırılarına karşı koruma.
- XSS (Cross-Site Scripting) önleyici önlemler.
- Güvenli ve yönetilebilir oturum işlemleri.

---

## 🤝 Katkıda Bulunmak

Projeye katkıda bulunmak ister misiniz?  
Pull Request (PR) göndermekten çekinmeyin!  
Her türlü katkı ve geri bildirim değerlidir. 🎉

---

## 📄 Lisans

Bu proje **özel** olup, sürekli geliştirme ve iyileştirme sürecindedir.
