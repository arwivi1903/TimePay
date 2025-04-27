# Maaş Yönetim Sistemi

PHP ile geliştirilmiş, kuruluşların çalışan maaşlarını, vardiyalarını ve ilgili hesaplamaları yönetmelerine yardımcı olan kapsamlı bir web tabanlı maaş yönetim sistemi.

## Özellikler

- Kullanıcı Kimlik Doğrulama Sistemi
  - Kayıt ve Giriş
  - Şifre Sıfırlama İşlevi
  - Kullanıcı Profil Yönetimi

- Maaş Yönetimi
  - Maaş Hesaplama
  - Vardiya Yönetimi
  - Fazla Mesai Takibi
  - Zam Yönetimi
  - Saat Ücreti Güncelleme

- Ek Özellikler
  - Eczane Bilgi Yönetimi
  - Tatil Takvimi Takibi
  - Öneri Sistemi
  - Ziyaretçi İstatistikleri
  - Kullanıcı Yönetimi

## Teknik Altyapı

- PHP
- MySQL Veritabanı
- AdminLTE Şablonu (Arayüz için)
- PHPMailer (E-posta İşlevselliği için)
- Composer (Bağımlılık Yönetimi için)

## Dizin Yapısı

```
├── classes/           # Temel PHP sınıfları
├── config/            # Yapılandırma dosyaları
├── dist/              # Önyüz varlıkları
├── functions/         # Yardımcı fonksiyonlar
├── src/               # Kaynak dosyalar
├── vendor/            # Bağımlılıklar
```

## Kurulum

1. Projeyi web sunucusu dizininize klonlayın
2. PHP ve MySQL'in kurulu olduğundan emin olun
3. Composer ile bağımlılıkları yükleyin:
   ```
   composer install
   ```
4. Veritabanı ayarlarınızı yapılandırın
5. E-posta yapılandırmanızı `config/mail.php` dosyasında ayarlayın

## Önemli Dosyalar

- `classes/Database.class.php`: Veritabanı bağlantı yönetimi
- `classes/MaasHesaplayici.class.php`: Maaş hesaplama mantığı
- `classes/Registration.class.php`: Kullanıcı kayıt işlemleri
- `functions/allFunctions.php`: Genel yardımcı fonksiyonlar

## Güvenlik Özellikleri

- Şifre Şifreleme
- SQL Enjeksiyon Önleme
- XSS Koruması
- Güvenli Oturum Yönetimi

## Katkıda Bulunma

Katkılarınızı bekliyoruz! Lütfen Pull Request göndermekten çekinmeyin.

## Lisans

Bu proje özel ve gizlidir.