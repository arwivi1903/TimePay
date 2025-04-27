<?php
// declare(strict_types=1); // PHP 7.2'de strict_types kullanmayalım

class Database {
    // Singleton PDO bağlantısı
    private static $instance = null;

    // Veritabanı yapılandırma ayarları
    private static $config = array();

    // Sorgu günlükleri
    private $queryLog = array();

    // Önbellek mekanizması
    private $queryCache = array();
    private $cacheDuration = 3600; // Önbellek süresi (saniye)

    /**
     * Veritabanı yapılandırma ayarlarını yükler.
     * @param array $settings Yapılandırma ayarları.
     */
    public static function configure($settings) {
        self::$config = array_merge(array(
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'zam',
            'username'  => 'root',
            'password'  => '',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'port'      => 3306,
            'options'   => array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            )
        ), $settings);
    }

    /**
     * Veritabanı bağlantısını oluşturur veya mevcut bağlantıyı döndürür.
     * @return PDO Veritabanı bağlantısı.
     */
    public static function connection() {
        if (empty(self::$config)) {
            throw new DatabaseConnectionException("Veritabanı yapılandırması eksik. Önce configure() çağrılmalı.");
        }

        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    "%s:host=%s;dbname=%s;port=%d;charset=%s",
                    self::$config['driver'],
                    self::$config['host'],
                    self::$config['database'],
                    self::$config['port'],
                    self::$config['charset']
                );

                self::$instance = new PDO(
                    $dsn,
                    self::$config['username'],
                    self::$config['password'],
                    self::$config['options']
                );
            } catch (PDOException $e) {
                self::logError('Veritabanı Bağlantı Hatası', array(
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ));
                throw new DatabaseConnectionException("Veritabanı bağlantısı başarısız: " . $e->getMessage());
            }
        }

        return self::$instance;
    }

     /**
     * Yeni bir veritabanı oluşturur.
     * @param string $databaseName Oluşturulacak veritabanının adı.
     * @return bool Veritabanı başarıyla oluşturulduysa true.
     * @throws Exception Veritabanı oluşturma hatası durumunda fırlatılır.
     */
    public function createDB(string $databaseName): bool {
        try {
            // Karakter seti ve collation ekle
            $query = sprintf(
                'CREATE DATABASE `%s` CHARACTER SET %s COLLATE %s',
                $databaseName,
                self::$config['charset'],
                self::$config['collation']
            );

            self::connection()->exec($query); // exec kullanımı veritabanı oluşturma için uygun
            return true;
        } catch (PDOException $e) {
            throw new Exception("Veritabanı oluşturulurken hata oluştu: " . $e->getMessage());
        }
    }

    /**
     * Tablo işlemleri (oluşturma, değiştirme vb.) için genel bir metod.
     * @param string $query Tablo üzerinde çalıştırılacak SQL sorgusu.
     * @return bool İşlem başarılıysa true.
     * @throws Exception Tablo işlemleri sırasında bir hata oluşursa fırlatılır.
     */
    public function tableOperations(string $query): bool {
        try {
            self::connection()->exec($query); // exec metodu tablo işlemleri için uygundur
            return true;
        } catch (PDOException $e) {
            throw new Exception("Tablo operasyonu sırasında hata oluştu: " . $e->getMessage());
        }
    }

    /**
     * Tüm tablolar için bakım işlemleri (tablo kontrolü, analiz, onarma, optimizasyon).
     * @return void Bakım işlemi sırasında çıktı üretir.
     * @throws Exception Bakım sırasında bir hata oluşursa fırlatılır.
     */
    public function maintenance(): void {
        try {
            // Tüm tablo isimlerini al
            $tables = self::connection()->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                // Bakım işlemlerini sırayla çalıştır
                $check = self::connection()->query("CHECK TABLE $table")->fetch(PDO::FETCH_ASSOC);
                $analyze = self::connection()->query("ANALYZE TABLE $table")->fetch(PDO::FETCH_ASSOC);
                $repair = self::connection()->query("REPAIR TABLE $table")->fetch(PDO::FETCH_ASSOC);
                $optimize = self::connection()->query("OPTIMIZE TABLE $table")->fetch(PDO::FETCH_ASSOC);

                // Bakım sonuçlarını kontrol et ve çıktı üret
                if ($check && $analyze && $repair && $optimize) {
                    echo "{$table} adlı tablonun bakımı başarıyla tamamlandı.<br>";
                } else {
                    echo "{$table} tablosunun bakımında bir hata oluştu.<br>";
                }
            }
        } catch (PDOException $e) {
            throw new Exception('Bakım işlemleri sırasında hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Veritabanında bir sorgu çalıştırır.
     * @param string $query Çalıştırılacak SQL sorgusu.
     * @param array $params Sorgu parametreleri.
     * @return PDOStatement Sorgu sonucu.
     */
    public function query($query, $params = array()) {
        $stmt = self::connection()->prepare($query);
        $start = microtime(true);

        if (!$stmt->execute($params)) {
            throw new DatabaseQueryException("Sorgu çalıştırılamadı");
        }

        $this->logQuery($query, $params, microtime(true) - $start);
        return $stmt;
    }

    /**
     * Veritabanından çoklu veri çeker.
     * @param string $query Çalıştırılacak SQL sorgusu.
     * @param array $params Sorgu parametreleri.
     * @return array Sonuçlar dizisi.
     */
    public function getRows($query, $params = array()) {
        return $this->query($query, $params)->fetchAll();
    }

    /**
     * Veritabanından tek bir kayıt çeker.
     * @param string $query Çalıştırılacak SQL sorgusu.
     * @param array $params Sorgu parametreleri.
     * @return ?object Tek bir kayıt veya null.
     */
    public function getRow($query, $params = array()) {
        $result = $this->query($query, $params)->fetch();
        return $result ?: null;
    }

    /**
     * Tek bir sütun değeri döndürür.
     * @param string $query Çalıştırılacak SQL sorgusu.
     * @param array|null $params Sorgu parametreleri (isteğe bağlı).
     * @return mixed Sütun değeri veya false (eğer sonuç yoksa).
     */
    public function getColumn($query, $params = null) {
        try {
            return $this->query($query, $params)->fetchColumn();
        } catch (PDOException $e) {
            throw new Exception("Sütun değeri alınırken hata oluştu: " . $e->getMessage());
        }
    }

    /**
     * Sorgudan dönen satır sayısını döndürür.
     * @param string $query Çalıştırılacak SQL sorgusu.
     * @param array|null $params Sorgu parametreleri (isteğe bağlı).
     * @return int Sorgudan dönen satır sayısı.
     */
    public function getRowCount(string $query, ?array $params = null): int {
        try {
            return $this->query($query, $params)->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Satır sayısı alınırken hata oluştu: " . $e->getMessage());
        }
    }

    /**
     * Veritabanına yeni bir kayıt ekler.
     * @param string $table Tablo adı.
     * @param array $data Eklenmek istenen veri.
     * @return string Eklenen kaydın ID'si.
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $query = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";

        $this->query($query, array_values($data));
        return self::connection()->lastInsertId();
    }

    /**
     * Veritabanına toplu kayıt ekler.
     * @param string $table Tablo adı.
     * @param array $records Eklenmek istenen kayıtlar.
     * @return int Eklenen kayıt sayısı.
     */
    public function insertBatch($table, $records) {
        if (empty($records)) return 0;

        $columns = implode(', ', array_keys($records[0]));
        $placeholders = '(' . implode(', ', array_fill(0, count($records[0]), '?')) . ')';
        $query = "INSERT INTO {$table} ({$columns}) VALUES " . implode(', ', array_fill(0, count($records), $placeholders));

        $flattenedData = array();
        foreach ($records as $record) {
            $flattenedData = array_merge($flattenedData, array_values($record));
        }

        $this->query($query, $flattenedData);
        return count($records);
    }

    /**
     * Bir tabloyu günceller.
     * @param string $table Tablo adı.
     * @param array $data Güncellenmek istenen veri.
     * @param array $where Güncelleme koşulları.
     * @return int Güncellenen satır sayısı.
     */
    public function update($table, $data, $where) {
        $setClauses = implode(', ', array_map(function($k) {
            return "$k = ?";
        }, array_keys($data)));
        $whereClauses = implode(' AND ', array_map(function($k) {
            return "$k = ?";
        }, array_keys($where)));

        $query = "UPDATE {$table} SET {$setClauses} WHERE {$whereClauses}";
        $params = array_merge(array_values($data), array_values($where));

        return $this->query($query, $params)->rowCount();
    }

    /**
     * Bir tablodan satır siler.
     * @param string $table Tablo adı.
     * @param array $where Silme koşulları.
     * @return int Silinen satır sayısı.
     */
    public function delete($table, $where) {
        $conditions = implode(' AND ', array_map(function($k) {
            return "$k = ?";
        }, array_keys($where)));
        $query = "DELETE FROM {$table} WHERE {$conditions}";

        return $this->query($query, array_values($where))->rowCount();
    }

    /**
     * Transaction işlemleri için modüler başlatma fonksiyonu.
     * @return bool Transaction başarılı şekilde başlatıldıysa true.
     */
    public function beginTransaction(): bool {
        return self::connection()->beginTransaction();
    }

    /**
     * Transaction işlemini başarıyla tamamlar.
     * @return bool Transaction başarılı şekilde tamamlandıysa true.
     */
    public function commit(): bool {
        return self::connection()->commit();
    }

    /**
     * Transaction işlemini geri alır.
     * @return bool Transaction geri alındıysa true.
     */
    public function rollBack(): bool {
        return self::connection()->rollBack();
    }

    /**
     * Bir transactionın aktif olup olmadığını kontrol eder.
     * @return bool Eğer bir transaction aktifse true, değilse false.
     */
    public function inTransaction(): bool {
        return self::connection()->inTransaction();
    }

    /**
     * Transaction işlemini bir callback ile kapsar.
     * @param callable $callback Transaction içinde çalıştırılacak kodlar.
     * @return mixed Callback işleminin sonucu.
     * @throws Exception Eğer callback sırasında bir hata oluşursa fırlatılır.
     */
    public function executeInTransaction(callable $callback) {
        $this->beginTransaction();
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Transaction işlemini otomatik olarak başlatır ve tamamlar.
     * @param callable $callback Transaction içinde çalıştırılacak kodlar.
     * @return mixed Callback işleminin sonucu.
     * @throws Throwable Hata oluşursa işlem geri alınır ve hata fırlatılır.
     */
    public function transaction(callable $callback): mixed {
        try {
            self::connection()->beginTransaction();
            $result = $callback($this);
            self::connection()->commit();
            return $result;
        } catch (Throwable $e) {
            self::connection()->rollBack();
            self::logError('Transaction Hatası', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Bir tablo üzerinde toplu güncelleme işlemi yapar.
     * @param string $table Güncellenecek tablo adı.
     * @param array $data Güncellenmek istenen veriler (her satır bir dizi).
     * @param string $whereColumn Güncelleme için kullanılan koşul sütunu (örneğin: id).
     * @return int Güncellenen satır sayısı.
     * @throws Exception Hata durumunda fırlatılır.
     */
    public function bulkUpdate(string $table, array $data, string $whereColumn): int {
        if (empty($data)) {
            return 0; // Veri yoksa işlem yapılmaz
        }

        try {
            $cases = [];
            $params = [];
            $ids = [];

            // Verileri işleme
            foreach ($data as $item) {
                if (!isset($item[$whereColumn])) {
                    throw new Exception("Her veri satırı '$whereColumn' anahtarını içermelidir.");
                }

                $id = $item[$whereColumn];
                $ids[] = $id;
                unset($item[$whereColumn]);

                foreach ($item as $column => $value) {
                    $cases[$column][] = "WHEN :id_$id THEN :value_{$column}_$id";
                    $params[":id_$id"] = $id;
                    $params[":value_{$column}_$id"] = $value;
                }
            }

            // SQL sorgusu oluşturma
            $sql = "UPDATE $table SET ";
            foreach ($cases as $column => $caseStatements) {
                $sql .= "$column = CASE $whereColumn " . implode(' ', $caseStatements) . " ELSE $column END, ";
            }
            $sql = rtrim($sql, ', ');
            $sql .= " WHERE $whereColumn IN (" . implode(',', array_fill(0, count($ids), '?')) . ")";

            // Parametreleri ve ID'leri birleştirerek sorguyu çalıştırma
            $stmt = self::connection()->prepare($sql);
            $stmt->execute(array_merge(array_values($params), $ids));

            return $stmt->rowCount(); // Güncellenen satır sayısını döndür
        } catch (PDOException $e) {
            throw new Exception("Toplu güncelleme sırasında hata oluştu: " . $e->getMessage());
        }
    }

    /**
     * Veri arama ve filtreleme için limitli bir sorgu çalıştırır.
     * @param string $query Çalıştırılacak SQL sorgusu.
     * @param int $p1 Başlangıç satırı (OFFSET) veya limit değeri.
     * @param int|null $p2 Satır sayısı (LIMIT) (isteğe bağlı).
     * @return array Sorgudan dönen veriler.
     * @throws Exception Hata durumunda fırlatılır.
     */
    public function limit(string $query, int $p1 = 1, ?int $p2 = null): array {
        try {
            $stmt = self::connection()->prepare($query);

            // İlk parametre her zaman zorunlu
            $stmt->bindValue(1, $p1, PDO::PARAM_INT);

            // İkinci parametre varsa bağla
            if (!is_null($p2)) {
                $stmt->bindValue(2, $p2, PDO::PARAM_INT);
            }

            // Sorguyu çalıştır
            $stmt->execute();

            // Sonuçları döndür
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Limitli sorgu çalıştırılırken hata oluştu: " . $e->getMessage());
        }
    }

    /**
     * Belirtilen sütuna göre bir satır döndürür.
     * @param string $table Tablo adı.
     * @param string $column Sorgulanacak sütun adı.
     * @param mixed $value Aranan değer.
     * @return ?object Bulunan kayıt veya null.
     * @throws Exception Hata durumunda fırlatılır.
     */
    public function findByColumn(string $table, string $column, mixed $value): ?object {
        try {
            $query = "SELECT * FROM $table WHERE $column = :value LIMIT 1";
            $stmt = self::connection()->prepare($query);
            $stmt->bindValue(':value', $value);
            $stmt->execute();
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            throw new Exception("Kayıt bulunurken hata oluştu: " . $e->getMessage());
        }
    }

    /**
     * Belirtilen sütuna göre tüm kayıtları döndürür.
     * @param string $table Tablo adı.
     * @param string $column Sorgulanacak sütun adı.
     * @param mixed $value Aranan değer.
     * @return array Bulunan kayıtlar dizisi.
     * @throws Exception Hata durumunda fırlatılır.
     */
    public function findAllByColumn(string $table, string $column, mixed $value): array {
        try {
            $query = "SELECT * FROM $table WHERE $column = :value";
            $stmt = self::connection()->prepare($query);
            $stmt->bindValue(':value', $value);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Kayıtlar bulunurken hata oluştu: " . $e->getMessage());
        }
    }

    /**
     * Belirtilen sütunlarda arama yapar ve sonuçları döndürür.
     * @param string $table Tablo adı.
     * @param array $searchColumns Arama yapılacak sütunlar.
     * @param string $searchTerm Arama terimi.
     * @param int $limit Sonuçların sınırı.
     * @return array Bulunan kayıtlar dizisi.
     * @throws Exception Hata durumunda fırlatılır.
     */
    public function search(string $table, array $searchColumns, string $searchTerm, int $limit = 10): array {
        try {
            $conditions = implode(' OR ', array_map(function($col) {
                return "$col LIKE :search";
            }, $searchColumns));
            $query = "SELECT * FROM $table WHERE $conditions LIMIT :limit";

            $stmt = self::connection()->prepare($query);
            $stmt->bindValue(':search', "%$searchTerm%", PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Arama sırasında hata oluştu: " . $e->getMessage());
        }
    }

    /**
     * Veritabanı sorgusuna sayfalama uygular ve sonuçları döndürür.
     * @param string $query Sayfalama yapılacak SQL sorgusu.
     * @param array $params Sorgu parametreleri.
     * @param int $page Mevcut sayfa numarası.
     * @param int $perPage Sayfa başına kayıt sayısı.
     * @return array Sayfalama sonuçları ve ilgili meta veriler.
     * @throws Exception Hata durumunda fırlatılır.
     */
    public function paginate($query, $params = array(), $page = 1, $perPage = 20) {
        try {
            $totalQuery = "SELECT COUNT(*) as total FROM ($query) as subquery";
            $total = $this->getColumn($totalQuery, $params);

            $offset = ($page - 1) * $perPage;

            $query .= " LIMIT :offset, :perPage";
            $params[':offset'] = $offset;
            $params[':perPage'] = $perPage;

            $data = $this->getRows($query, $params);

            return array(
                'data' => $data,
                'total' => $total,
                'page' => $page,
                'perPage' => $perPage,
                'lastPage' => ceil($total / $perPage)
            );
        } catch (PDOException $e) {
            throw new Exception("Sayfalama sırasında hata oluştu: " . $e->getMessage());
        }
    }

    /**
     * Sorgu sonuçlarını CSV formatında dışa aktarır.
     * @param string $query Çalıştırılacak SQL sorgusu.
     * @param array|null $params Sorgu parametreleri (isteğe bağlı).
     * @return string CSV formatında veri.
     */
    public function exportToCSV(string $query, ?array $params = null): string {
        $results = $this->getRows($query, $params);
        if (empty($results)) {
            return '';
        }

        $output = fopen('php://temp', 'r+');

        // Başlıklar
        fputcsv($output, array_keys((array)$results[0]));

        // Veriler
        foreach ($results as $row) {
            fputcsv($output, (array)$row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Sorgu sonuçlarını Excel formatında dışa aktarır.
     * @param string $query Çalıştırılacak SQL sorgusu.
     * @param array|null $params Sorgu parametreleri (isteğe bağlı).
     * @return string Excel dosya yolu.
     */
    public function exportToExcel(string $query, ?array $params = null): string {
        $results = $this->getRows($query, $params);
        if (empty($results)) {
            throw new Exception("Dışa aktarılacak veri bulunamadı.");
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Başlıklar
        $headers = array_keys((array)$results[0]);
        $sheet->fromArray($headers, null, 'A1');

        // Veriler
        $rowIndex = 2;
        foreach ($results as $row) {
            $sheet->fromArray(array_values((array)$row), null, "A$rowIndex");
            $rowIndex++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filePath = 'exports/data_' . date('Ymd_His') . '.xlsx';
        $writer->save($filePath);

        return $filePath;
    }

    /**
     * Sorgu sonuçlarını PDF formatında dışa aktarır.
     * @param string $query Çalıştırılacak SQL sorgusu.
     * @param array|null $params Sorgu parametreleri (isteğe bağlı).
     * @return string PDF dosya yolu.
     */
    public function exportToPDF(string $query, ?array $params = null): string {
        $results = $this->getRows($query, $params);
        if (empty($results)) {
            throw new Exception("Dışa aktarılacak veri bulunamadı.");
        }

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);

        // Başlıklar
        $headers = array_keys((array)$results[0]);
        foreach ($headers as $header) {
            $pdf->Cell(40, 10, $header, 1);
        }
        $pdf->Ln();

        // Veriler
        foreach ($results as $row) {
            foreach ((array)$row as $value) {
                $pdf->Cell(40, 10, (string)$value, 1);
            }
            $pdf->Ln();
        }

        $filePath = 'exports/data_' . date('Ymd_His') . '.pdf';
        $pdf->Output('F', $filePath);

        return $filePath;
    }

    /**
     * CSV dosyasını tabloya aktarır.
     * @param string $table Tablo adı.
     * @param string $filePath CSV dosyasının yolu.
     * @param array $columnMap CSV sütunlarını veritabanı sütunlarına eşlemek için harita.
     * @return int Başarıyla eklenen satır sayısı.
     * @throws Exception Hata durumunda fırlatılır.
     */
    public function importFromCSV(string $table, string $filePath, array $columnMap = []): int {
        if (!file_exists($filePath)) {
            throw new Exception("CSV dosyası bulunamadı: $filePath");
        }

        $successCount = 0;
        $handle = fopen($filePath, 'r');

        // Başlıklar
        $headers = fgetcsv($handle);

        while (($data = fgetcsv($handle)) !== false) {
            $row = array_combine($headers, $data);
            if (!empty($columnMap)) {
                $mappedRow = [];
                foreach ($columnMap as $csvColumn => $dbColumn) {
                    if (isset($row[$csvColumn])) {
                        $mappedRow[$dbColumn] = $row[$csvColumn];
                    }
                }
                $row = $mappedRow;
            }

            $this->insert($table, $row);
            $successCount++;
        }

        fclose($handle);
        return $successCount;
    }

    /**
     * Bir tablodaki enum sütununun olası değerlerini döndürür.
     * @param string $table Tablo adı.
     * @param string $column Enum sütunu adı.
     * @return array Enum değerleri.
     */
    public function getEnum(string $table, string $column): array {
        try {
            $query = "SHOW COLUMNS FROM $table WHERE Field = :column";
            $stmt = self::connection()->prepare($query);
            $stmt->execute([':column' => $column]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && preg_match("/^enum\((.*)\)$/", $result['Type'], $matches)) {
                $enum = str_getcsv($matches[1], ',', "'");
                return array_map('trim', $enum);
            }
        } catch (PDOException $e) {
            throw new Exception("Enum değerleri alınırken hata oluştu: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Belirtilen tabloların SQL yedeğini oluşturur.
     * @param string|array $tables Yedeklenecek tablolar (* ise tüm tablolar).
     * @return string Yedek SQL komutları.
     */
    public function backup($tables = '*'): string {
        $output = '';

        if ($tables == '*') {
            $tables = $this->getRows("SHOW TABLES");
            $tables = array_column($tables, 'Tables_in_' . self::$config['database']);
        } else {
            $tables = is_array($tables) ? $tables : explode(',', $tables);
        }

        foreach ($tables as $table) {
            // Tabloyu düşür
            $output .= "DROP TABLE IF EXISTS `$table`;\n";

            // Tablo oluşturma sorgusu
            $createTable = $this->getRow("SHOW CREATE TABLE `$table`");
            $output .= $createTable->{'Create Table'} . ";\n\n";

            // Verileri ekle
            $rows = $this->getRows("SELECT * FROM `$table`");
            foreach ($rows as $row) {
                $values = array_map(function($value) {
                    return is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                }, (array)$row);
                $output .= "INSERT INTO `$table` VALUES (" . implode(',', $values) . ");\n";
            }

            $output .= "\n\n";
        }

        return $output;
    }

    /**
     * Önbellek kullanarak bir sorgu çalıştırır.
     * @param string $query SQL sorgusu.
     * @param array|null $params Sorgu parametreleri (isteğe bağlı).
     * @param int|null $duration Önbellek süresi (isteğe bağlı).
     * @return array Sorgu sonuçları.
     */
    public function getCachedQuery(string $query, ?array $params = null, ?int $duration = null): array {
        $cacheKey = md5($query . serialize($params));
        $duration = $duration ?? $this->cacheDuration;

        if (isset($this->queryCache[$cacheKey]) && time() - $this->queryCache[$cacheKey]['timestamp'] < $duration) {
            return $this->queryCache[$cacheKey]['data'];
        }

        $data = $this->getRows($query, $params);
        $this->queryCache[$cacheKey] = array(
            'data' => $data,
            'timestamp' => time()
        );

        return $data;
    }

    /**
     * Veritabanı ile ilgili genel bilgileri döndürür.
     * @return array Veritabanı bilgileri (boyut, tablo sayısı, charset vb.).
     */
    public function getDatabaseInfo() {
        $query = "SELECT 
                    SUM(data_length + index_length) / 1024 / 1024 AS size_mb,
                    SUM(data_free) / 1024 / 1024 AS free_space_mb
                FROM information_schema.TABLES 
                WHERE table_schema = :dbName";

        $sizeInfo = $this->getRow($query, array(':dbName' => self::$config['database']));

        return array(
            'size_mb' => isset($sizeInfo->size_mb) ? $sizeInfo->size_mb : 0,
            'free_space_mb' => isset($sizeInfo->free_space_mb) ? $sizeInfo->free_space_mb : 0,
            'table_count' => $this->getRow(
                "SELECT COUNT(*) as count FROM information_schema.TABLES WHERE table_schema = :dbName",
                array(':dbName' => self::$config['database'])
            )->count,
            'charset' => $this->getRow(
                "SELECT DEFAULT_CHARACTER_SET_NAME as charset FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = :dbName",
                array(':dbName' => self::$config['database'])
            )->charset
        );
    }

    /**
     * Bir tablonun veri boyutunu döndürür.
     * @param string $table Tablo adı.
     * @return array Tablo boyutu (veri, indeks ve toplam boyut).
     */
    public function getTableSize($table) {
        $query = "SELECT 
                    data_length / 1024 / 1024 AS data_size_mb,
                    index_length / 1024 / 1024 AS index_size_mb,
                    (data_length + index_length) / 1024 / 1024 AS total_size_mb
                FROM information_schema.TABLES
                WHERE table_schema = :dbName AND table_name = :tableName";

        return $this->getRow($query, array(
            ':dbName' => self::$config['database'],
            ':tableName' => $table
        )) ?: array();
    }

    /**
     * Belirtilen tabloyu tamamen boşaltır.
     * @param string $table Tablo adı.
     * @return bool Başarılıysa true.
     */
    public function truncateTable(string $table): bool {
        try {
            $query = "TRUNCATE TABLE $table";
            return self::connection()->exec($query) !== false;
        } catch (PDOException $e) {
            throw new Exception("Tablo boşaltılırken hata oluştu: " . $e->getMessage());
        }
    }

    /**
     * Verilen veri dizisiyle bir SQL INSERT veya UPDATE sorgusu için alanlar ve yer tutucular oluşturur.
     * @param array $data Sorgu için kullanılacak veri dizisi (alanlar ve değerler).
     * @return string SQL sorgusu için alanlar ve yer tutucular.
     */
    private function buildInsertQuery($data) {
        return implode(', ', array_map(function($key) {
            return "$key = ?";
        }, array_keys($data)));
    }

    /**
     * Hata günlüğü kaydeder.
     * @param string $message Hata mesajı.
     * @param array $context Ek bağlam.
     */
    private static function logError($message, $context = array()) {
        error_log(json_encode(array(
            'message' => $message,
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s')
        )));
    }

    /**
     * Sorgu günlüğü kaydeder.
     * @param string $query Sorgu metni.
     * @param array $params Parametreler.
     * @param float $duration Sorgu süresi.
     */
    private function logQuery($query, $params, $duration) {
        $this->queryLog[] = array(
            'query' => $query,
            'params' => $params,
            'duration' => $duration
        );
    }

    /**
     * Tüm sorgu günlüklerini döndürür.
     * @return array Sorgu günlükleri.
     */
    public function getQueryLog() {
        return $this->queryLog;
    }

    /**
     * Önbelleği temizler.
     */
    public function clearCache() {
        $this->queryCache = array();
    }
}

// Özel hata sınıfları
class DatabaseConnectionException extends \Exception {}
class DatabaseQueryException extends \Exception {}