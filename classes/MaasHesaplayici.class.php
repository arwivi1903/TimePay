<?php
class MaasHesaplayici
{
    private $saatUcret;
    private $calismaSaatleri;
    private $avansOrani;
    private $vergiOranlari;
    private $ikramiyeOrani;

    // Yapıcı metot: Sınıfı başlatırken gerekli parametreleri alır
    public function __construct($saatUcret = 0, $calismaSaatleri = 225, $avansOrani = 0.35, $ikramiyeOrani = 2)
    {
        $this->saatUcret = floatval($saatUcret);
        $this->calismaSaatleri = $calismaSaatleri;
        $this->avansOrani = $avansOrani;
        $this->ikramiyeOrani = $ikramiyeOrani;

        // Vergi oranlarını belirliyoruz
        $this->vergiOranlari = [
            'vergi15' => 0.15,
            'vergi20' => 0.20,
            'vergi27' => 0.27,
            'vergi35' => 0.35,
            'vergi40' => 0.40
        ];
    }

    // Brüt maaş hesaplama
    public function brutMaas()
    {
        return $this->calismaSaatleri * $this->saatUcret;
    }

    // Avans hesaplama
    public function netAvans()
    {
        return $this->brutMaas() * $this->avansOrani;
    }

    // İkramiyeli brüt maaş hesaplama
    public function ikramiyeDahilBrut()
    {
        return $this->brutMaas() + ($this->brutMaas() / 3);
    }

    // İkramiye hesaplama
    public function ikramiye()
    {
        return $this->brutMaas() * $this->ikramiyeOrani;
    }

    // Vergi hesaplama (vergi oranına göre)
    public function vergiHesapla($vergiOrani)
    {
        return $this->brutMaas() * $vergiOrani;
    }

    // Net maaş hesaplama (brüt maaş - vergi ve avans)
    public function netMaas()
    {
        $brut = $this->brutMaas();
        $vergi = $this->vergiHesapla($vergiOrani);
        return $brut - $vergi - $this->netAvans();
    }

    /**
     * Mesai ücretini hesaplar
     * @param float $saat Mesai saati
     * @param int $tur Mesai türü (1: Hafta içi, 2: Hafta sonu)
     * @return float Mesai ücreti
     */
    public function hesaplaMesaiUcreti(float $saat, int $tur): float 
    {
        $carpan = $tur == 1 ? 1.5 : 2;
        $ucret = floatval($saat) * $carpan * $this->saatUcret;
        return round($ucret, 2);
    }

    // Saat ücretini get/set metodları
    public function getSaatUcret(): float
    {
        return $this->saatUcret;
    }

    public function setSaatUcret(float $ucret): void
    {
        $this->saatUcret = $ucret;
    }
}
?>
