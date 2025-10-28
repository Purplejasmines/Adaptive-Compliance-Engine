<?php
header('Content-Type: application/json');

try {
    require __DIR__ . '/db_connect.php';
    $stmt = $pdo->query('SELECT 1 AS ok');
    $row = $stmt->fetch();
    echo json_encode([
        'context' => 'users',
        'connected' => true,
        'result' => $row['ok'] ?? null
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'context' => 'users',
        'connected' => false,
        'error' => $e->getMessage()
    ]);
}
<?php /* EOF */ ?>


