<?php
class Registration {
    private $db;

    public function __construct() {
        Database::configure([]);
        $this->db = new Database();
    }

    private function formatPrice($price) {
        // Türkçe para birimi formatını temizle
        $price = str_replace(['₺', ' '], '', $price); // ₺ işaretini ve boşlukları kaldır
        $price = str_replace(',', '.', $price); // virgülü noktaya çevir
        return floatval($price);
    }

    private function validateInput($data) {
        $errors = [];
        
        // Zorunlu alan kontrolü
        if (empty($data['fullName']) || empty($data['email']) || empty($data['password']) || 
            empty($data['price']) || empty($data['startDate']) || empty($data['shift'])) {
            $errors[] = 'Lütfen tüm alanları doldurun.';
        }
        
        // E-posta doğrulama
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Geçerli bir email adresi giriniz.';
        }

        // Saat ücreti doğrulama - decryptData kullanmıyoruz çünkü henüz şifrelenmemiş veri
        if (isset($data['price']) && !is_string($data['price'])) {
            $price = $this->formatPrice($data['price']);
            if ($price <= 0) {
                $errors[] = 'Saat ücreti sıfırdan büyük olmalıdır.';
            }
        }

        // Tarih doğrulama
        if (!strtotime($data['startDate'])) {
            $errors[] = 'Geçerli bir tarih giriniz.';
        }

        // Vardiya doğrulama
        $validShifts = ['A', 'B', 'C', 'D', 'Gündüz'];
        if (!in_array($data['shift'], $validShifts)) {
            $errors[] = 'Geçersiz vardiya seçimi.';
        }

        // E-posta mevcut mu?
        $userExists = $this->db->getColumn(
            'SELECT COUNT(UserMail) FROM users WHERE UserMail = ?', 
            [$data['email']]
        );

        if ($userExists > 0) {
            $errors[] = 'Bu e-posta adresi zaten kayıtlı.';
        }

        return $errors;
    }

    public function register($data) {
        $result = [
            'success' => false,
            'message' => '',
            'redirect' => ''
        ];

        // Önce fiyatı formatla
        $formattedPrice = $this->formatPrice($data['Price']);

        // Kullanıcı verilerini temizle
        $sanitizedData = [
            'fullName'   => htmlspecialchars(trim($data['registerFullName'])),
            'email'      => htmlspecialchars(trim($data['registerEmail'])),
            'password'   => htmlspecialchars(trim($data['registerPassword'])),
            'price'      => $formattedPrice, // Henüz şifrelenmemiş fiyat
            'startDate'  => htmlspecialchars(trim($data['StartDate'])),
            'shift'      => htmlspecialchars(trim($data['Shift']))
        ];

        $errors = $this->validateInput($sanitizedData);

        if (!empty($errors)) {
            $result['message'] = $errors[0];
            return $result;
        }

        // Şifreyi hash'le
        $hashedPassword = password_hash($sanitizedData['password'], PASSWORD_BCRYPT);

        // Fiyatı şifrele
        $encryptedPrice = encryptData($sanitizedData['price']);

        // Kullanıcıyı veritabanına ekle
        $createUser = $this->db->insert('users', [
            'UserName'   => $sanitizedData['fullName'],
            'UserMail'   => $sanitizedData['email'],
            'UserPass'   => $hashedPassword,
            'HourlyRate' => $encryptedPrice, // Şifrelenmiş fiyat
            'StartDate'  => $sanitizedData['startDate'],
            'Shift'      => $sanitizedData['shift']
        ]);

        if ($createUser) {
            // Yeni kullanıcı için session başlat
            $_SESSION['UserMail']   = $sanitizedData['email'];
            $_SESSION['UserName']   = $sanitizedData['fullName'];
            $_SESSION['HourlyRate'] = $sanitizedData['price']; // Şifrelenmemiş fiyat
            $_SESSION['StartDate']  = $sanitizedData['startDate'];
            $_SESSION['Shift']      = $sanitizedData['shift'];

            $result['success'] = true;
            $result['message'] = 'Kayıt başarılı!';
            $result['redirect'] = 'profil';
        } else {
            $result['message'] = 'Kayıt sırasında bir hata oluştu.';
        }

        return $result;
    }
}
?>
