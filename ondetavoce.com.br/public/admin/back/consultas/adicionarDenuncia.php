<?php
header('Content-Type: application/json');

// Inicia a sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado e tem permissão
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado: sessão não iniciada']);
    exit;
}

if (!in_array($_SESSION['user']['role'], ['admin', 'moderador'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado: permissões insuficientes']);
    exit;
}

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Função para formatar características
function formatarCaracteristicas(array $denuncia): string {
    $caracteristicas = [];
    
    if (!empty($denuncia['caracteristicas'])) {
        $caracteristicas[] = $denuncia['caracteristicas'];
    }
    
    if (!empty($denuncia['vestimentas'])) {
        $caracteristicas[] = "Vestimentas: " . $denuncia['vestimentas'];
    }
    
    if (!empty($denuncia['circunstancias'])) {
        $caracteristicas[] = "Circunstâncias: " . $denuncia['circunstancias'];
    }
    
    return implode("\n\n", $caracteristicas);
}

// Função para formatar observações
function formatarObservacoes(array $denuncia, string $observacao): string {
    $obs = [];
    
    $obs[] = "Registro criado a partir da denúncia #" . $denuncia['id'];
    $obs[] = "Denunciante: " . ($denuncia['denunciante_nome'] ?? 'Não informado');
    
    if (!empty($denuncia['boletim_ocorrencia'])) {
        $obs[] = "BO: " . $denuncia['boletim_ocorrencia'];
    }
    
    if (!empty($observacao)) {
        $obs[] = "Observações do aprovador: " . $observacao;
    }
    
    return implode("\n", $obs);
}

// Obtém e valida os dados do corpo da requisição
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Dados JSON inválidos']);
    exit;
}

$denunciaId = $input['id'] ?? null;
$observacao = trim($input['observacao'] ?? '');

if (!$denunciaId || !is_numeric($denunciaId)) {
    echo json_encode(['success' => false, 'message' => 'ID da denúncia inválido']);
    exit;
}

require_once '../../includes/db.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // Inicia transação
    $pdo->beginTransaction();

    // 1. Busca os dados da denúncia com verificação de status
    $stmt = $pdo->prepare("SELECT * FROM denuncia WHERE id = ? AND status = 'PENDENTE'");
    $stmt->execute([$denunciaId]);
    $denuncia = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$denuncia) {
        throw new Exception('Denúncia não encontrada ou já processada');
    }

    // Validação dos dados obrigatórios
    $camposObrigatorios = ['nome_completo', 'data_desaparecimento', 'cidade'];
    foreach ($camposObrigatorios as $campo) {
        if (empty($denuncia[$campo])) {
            throw new Exception("Campo obrigatório '$campo' não informado na denúncia");
        }
    }

    // Prepara dados para inserção
    $fotoNome = null;
    if ($denuncia['foto']) {
        $fotoNome = 'denuncia_' . $denunciaId . '_' . time() . '.jpg';
        $fotoPath = '../../uploads/' . $fotoNome;
    }

    // 2. Insere na tabela desaparecidos
    $stmt = $pdo->prepare("
        INSERT INTO desaparecidos (
            nome_completo, apelido, data_nascimento, data_desaparecimento, 
            local_desaparecimento, cidade, estado, pais, altura, cor_pele, 
            caracteristicas, foto, contato_responsavel, telefone_contato, 
            email_contato, status, observacoes, data_cadastro, data_atualizacao,
            usuario_aprovador_id
        ) VALUES (
            :nome_completo, :apelido, :data_nascimento, :data_desaparecimento, 
            :ultimo_local, :cidade, :estado, 'Brasil', :altura, :cor_pele, 
            :caracteristicas, :foto, :contato_responsavel, :telefone_contato, 
            :email_contato, 'desaparecido', :observacoes, NOW(), NOW(),
            :usuario_aprovador_id
        )
    ");

    $dadosInsert = [
        ':nome_completo' => $denuncia['nome_completo'],
        ':apelido' => $denuncia['apelido'] ?? null,
        ':data_nascimento' => !empty($denuncia['data_nascimento']) ? $denuncia['data_nascimento'] : null,
        ':data_desaparecimento' => $denuncia['data_desaparecimento'],
        ':ultimo_local' => $denuncia['ultimo_local'] ?? null,
        ':cidade' => $denuncia['cidade'],
        ':estado' => substr($denuncia['cidade'], -2), // Assume que o estado está nas últimas 2 letras
        ':altura' => !empty($denuncia['altura']) ? ($denuncia['altura'] / 100) : null, // Convertendo cm para m
        ':cor_pele' => !empty($denuncia['cor_pele']) ? strtolower($denuncia['cor_pele']) : null,
        ':caracteristicas' => formatarCaracteristicas($denuncia),
        ':foto' => $fotoNome,
        ':contato_responsavel' => $denuncia['denunciante_nome'] ?? null,
        ':telefone_contato' => $denuncia['telefone_contato'] ?? null,
        ':email_contato' => $denuncia['denunciante_email'] ?? null,
        ':observacoes' => formatarObservacoes($denuncia, $observacao),
        ':usuario_aprovador_id' => $_SESSION['user']['id']
    ];

    if (!$stmt->execute($dadosInsert)) {
        throw new Exception('Erro ao inserir desaparecido');
    }

    $desaparecidoId = $pdo->lastInsertId();

    // 3. Atualiza o status da denúncia para RESOLVIDA
    $stmt = $pdo->prepare("
        UPDATE denuncia 
        SET 
            status = 'RESOLVIDA',
            data_atualizacao = NOW(),
            usuario_responsavel_id = ?,
            observacoes = CONCAT(IFNULL(observacoes, ''), '\n\nAprovada em ', NOW(), ' por ', ?, '. ', ?)
        WHERE id = ?
    ");
    $stmt->execute([
        $_SESSION['user']['id'],
        $_SESSION['user']['nome'],
        $observacao,
        $denunciaId
    ]);

    // 4. Se houver foto, salva no sistema de arquivos
    if ($denuncia['foto'] && $fotoNome) {
        if (!is_dir('../../uploads')) {
            mkdir('../../uploads', 0755, true);
        }
        
        if (file_put_contents($fotoPath, $denuncia['foto']) === false) {
            throw new Exception('Erro ao salvar foto no servidor');
        }
    }

    // Commit da transação
    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Denúncia aprovada com sucesso',
        'desaparecido_id' => $desaparecidoId
    ]);
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erro no banco de dados: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro no banco de dados']);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erro ao processar denúncia: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}