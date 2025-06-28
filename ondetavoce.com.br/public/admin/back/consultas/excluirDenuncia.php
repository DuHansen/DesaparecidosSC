<?php
header('Content-Type: application/json');

// Inicia a sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado e tem permissão
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'moderador'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Obtém os dados do corpo da requisição
$input = json_decode(file_get_contents('php://input'), true);
$denunciaId = $input['id'] ?? null;
$motivo = $input['motivo'] ?? '';

if (!$denunciaId || !$motivo) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}
require_once '../../includes/db.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Atualiza o status da denúncia para REJEITADA e registra o motivo
    $stmt = $pdo->prepare("
        UPDATE denuncia 
        SET status = 'REJEITADA', descricao = CONCAT(descricao, '\n\nMotivo da rejeição: ', ?) 
        WHERE id = ?
    ");
    $stmt->execute([$motivo, $denunciaId]);

    echo json_encode(['success' => true, 'message' => 'Denúncia reprovada com sucesso']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao reprovar denúncia: ' . $e->getMessage()]);
}