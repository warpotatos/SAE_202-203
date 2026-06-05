<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'stagify');
define('DB_USER', 'root');
define('DB_PASS', '');          
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;padding:2rem;background:#fef2f2;color:#991b1b;border-radius:8px;max-width:600px;margin:2rem auto;">
                <h2>❌ Erreur de connexion à la base de données</h2>
                <p style="margin-top:.75rem;">' . htmlspecialchars($e->getMessage()) . '</p>
                <p style="margin-top:.75rem;font-size:.875rem;">Vérifiez que XAMPP est lancé et que la base <strong>stagify</strong> existe.</p>
            </div>');
        }
    }
    return $pdo;
}
