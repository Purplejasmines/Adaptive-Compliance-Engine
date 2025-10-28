<?php
header('Content-Type: application/json');

try {
    require __DIR__ . '/db_connect.php'; // provides $pdo or dies on failure
    // If we got here, connection established. Run a trivial query to verify.
    $stmt = $pdo->query('SELECT 1 AS ok');
    $row = $stmt->fetch();
    echo json_encode([
        'context' => 'admin/src',
        'connected' => true,
        'result' => $row['ok'] ?? null
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'context' => 'admin/src',
        'connected' => false,
        'error' => $e->getMessage()
    ]);
}
<?php /* EOF */ ?>


