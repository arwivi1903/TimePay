<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sunucu Hatası</title>
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background-color: #f8f9fa;
            color: #343a40;
            line-height: 1.6;
            margin: 0;
        }
        .error-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        h1 {
            color: #dc3545;
            font-size: 2.5em;
            margin-bottom: 20px;
        }
        .error-icon {
            font-size: 60px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        p {
            color: #6c757d;
            font-size: 1.1em;
            margin: 10px 0;
        }
        .home-button {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 30px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .home-button:hover {
            background-color: #0056b3;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <h1>Sunucu Hatası</h1>
        <p>Üzgünüz, şu anda sistemde teknik bir sorun yaşanıyor.</p>
        <p>Lütfen daha sonra tekrar deneyiniz.</p>
        <a href="index.php" class="home-button">
            <i class="fas fa-home"></i> Ana Sayfaya Dön
        </a>
    </div>
</body>
</html> 