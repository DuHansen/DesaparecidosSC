<?php
// Inicializa sessão, caso ainda não esteja iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Detectar a página atual
$current_page = basename($_SERVER['PHP_SELF']);

// Usuários permitidos por perfil
$permitidos = ['admin', 'user'];

// Verifica se usuário está autenticado e tem papel autorizado
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], $permitidos)) {
    header('Location: ../index.php');
    exit;
}

// Redireciona para HTTPS apenas em ambiente de produção
if ($_SERVER['HTTP_HOST'] !== 'localhost' && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on')) {
    $https_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $https_url", true, 301);
    exit();
}

// Dados do usuário logado (sessão já validada)
$usuario = [
    'nome' => $_SESSION['user']['nome'],
    'email' => $_SESSION['user']['email'],
    'id' => $_SESSION['user']['id'],
    'avatar' => 'https://randomuser.me/api/portraits/men/32.jpg', // avatar temporário fixo
    'perfil' => $_SESSION['user']['role']
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Desaparecidos</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
        }
        .navbar-custom {
            background-color: var(--primary-color) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .nav-link-custom {
            color: white !important;
            transition: all 0.3s;
            margin: 0 5px;
            font-weight: 500;
        }
        .nav-link-custom:hover, .nav-link-custom.active {
            color: var(--secondary-color) !important;
            transform: translateY(-2px);
        }
        /* Adicione estas regras CSS */
        .dropdown-menu-custom {
            background-color: var(--primary-color) !important;
            border: 1px solid var(--secondary-color) !important;
        }
        
        .dropdown-item-custom {
            color: white !important;
            padding: 8px 15px;
        }
        
        .dropdown-item-custom:hover, 
        .dropdown-item-custom:focus {
            background-color: var(--secondary-color) !important;
            color: white !important;
        }
        
        .dropdown-item-custom.active {
            background-color: var(--secondary-color) !important;
            font-weight: bold;
        }
        
        /* Mostrar dropdowns ao passar o mouse */
        .nav-item.dropdown:hover .dropdown-menu {
            display: block;
        }
        
        .dropdown-menu {
            margin-top: 0; /* Remove o espaço entre o botão e o dropdown */
        }
        .badge-notification {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.6rem;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--secondary-color);
            transition: all 0.3s;
        }
        .user-avatar:hover {
            transform: scale(1.1);
        }
        .search-box {
            position: relative;
            width: 250px;
        }
        .search-input {
            background-color: rgba(255,255,255,0.1);
            border: none;
            color: white;
            padding-left: 35px;
        }
        .search-input::placeholder {
            color: rgba(255,255,255,0.6);
        }
        .search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.6);
        }
        .feature-icon {
            font-size: 1.2rem;
            margin-right: 8px;
            color: var(--secondary-color);
        }
    </style>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="home.php">
                <i class="fas fa-search-location feature-icon"></i>
                <span class="ms-2">Sistema Desaparecidos</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <!-- Menu Principal -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom <?php echo ($current_page == 'home.php') ? 'active' : ''; ?>" href="home.php">
                            <i class="fas fa-home feature-icon"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom <?php echo ($current_page == 'analise-dados.php') ? 'active' : ''; ?>" href="analise-dados.php">
                            <i class="fas fa-chart-bar feature-icon"></i> Análise de Dados
                        </a>
                    </li>
                    <<li class="nav-item dropdown">
                    <a class="nav-link nav-link-custom dropdown-toggle <?php echo (in_array($current_page, ['reconhecimento-facial.php', 'biometria.php'])) ? 'active' : ''; ?>" 
                    href="#" 
                    id="inteligenciaDropdown" 
                    role="button" 
                    data-bs-toggle="dropdown" 
                    aria-expanded="false"> <!-- Adicione aria-expanded -->
                        <i class="fas fa-brain feature-icon"></i> Inteligência Artificial
                    </a>
                    <ul class="dropdown-menu dropdown-menu-custom" aria-labelledby="inteligenciaDropdown">
                        <li><a class="dropdown-item dropdown-item-custom <?php echo ($current_page == 'reconhecimento-facial.php') ? 'active' : ''; ?>" 
                            href="reconhecimento-facial.php">
                            <i class="fas fa-eye feature-icon"></i> Reconhecimento Facial
                        </a></li>
                        <li><a class="dropdown-item dropdown-item-custom <?php echo ($current_page == 'biometria.php') ? 'active' : ''; ?>" 
                            href="biometria.php">
                            <i class="fas fa-fingerprint feature-icon"></i> Biometria
                        </a></li>
                    </ul>
                </li>
                </ul>
                <!-- Barra de Pesquisa -->
                <div class="search-box me-3">
                    <i class="fas fa-search search-icon"></i>
                    <input class="form-control search-input" type="search" placeholder="Pesquisar desaparecidos..." id="pesquisaInput">
                </div>

                <!-- Menu do Usuário -->
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" 
                        href="#" 
                        id="userDropdown" 
                        role="button" 
                        data-bs-toggle="dropdown" 
                        aria-expanded="false">
                            <img src="<?php echo $usuario['avatar']; ?>" class="user-avatar me-2">
                            <span class="text-white"><?php echo $usuario['nome']; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-custom" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item dropdown-item-custom <?php echo ($current_page == 'perfil.php') ? 'active' : ''; ?>" href="perfil.php">
                                <i class="fas fa-user-circle me-2"></i> Meu Perfil
                            </a></li>
                            <li><a class="dropdown-item dropdown-item-custom <?php echo ($current_page == 'configuracoes.php') ? 'active' : ''; ?>" href="configuracoes.php">
                                <i class="fas fa-cog me-2"></i> Configurações
                            </a></li>
                            <li><hr class="dropdown-divider bg-secondary"></li>
                            <li><a class="dropdown-item dropdown-item-custom <?php echo ($current_page == 'suporte.php') ? 'active' : ''; ?>" href="suporte.php">
                                <i class="fas fa-headset me-2"></i> Suporte
                                <span class="badge bg-danger badge-notification">3</span>
                            </a></li>
                           <li>
                            <a class="dropdown-item dropdown-item-custom" href="#" onclick="apiLogout(); return false;">
                                <i class="fas fa-sign-out-alt me-2"></i> Sair
                            </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link position-relative <?php echo ($current_page == 'notificacoes.php') ? 'active' : ''; ?>" href="notificacoes.php">
                            <i class="fas fa-bell text-white"></i>
                            <span class="badge bg-danger badge-notification">5</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<!-- Scripts -->
<script>
  const inputBusca = document.getElementById('pesquisaInput');
  let timeout = null;

  inputBusca.addEventListener('input', function () {
    clearTimeout(timeout);
    const valor = this.value.trim();

    // Começa a buscar com 2+ letras
    if (valor.length >= 2) {
      timeout = setTimeout(() => {
        window.location.href = `home.php?q=${encodeURIComponent(valor)}`;
      }, 700); // tempo para evitar redirecionar a cada letra
    }
  });
</script>
<script>
async function apiLogout() {
  try {
    // 1. Obter o token CSRF (já deve estar disponível se o login funcionou)
    const xsrfToken = document.cookie
      .split('; ')
      .find(row => row.startsWith('XSRF-TOKEN='))
      ?.split('=')[1];
    
    if (!xsrfToken) {
      throw new Error('Token CSRF não encontrado nos cookies');
    }

    // 2. Fazer a requisição de logout
    const response = await fetch('http://localhost:8000/logout', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-XSRF-TOKEN': decodeURIComponent(xsrfToken)
      },
      credentials: 'include' // Importante para enviar cookies
    });

    // 3. Processar a resposta
    if (response.redirected) {
      window.location.href = response.url;
      return;
    }

    const data = await response.json();
    
    if (response.ok && data.success) {
      window.location.href = '../index.php';
    } else {
      alert('Erro ao fazer logout: ' + (data.message || 'Tente novamente'));
    }
  } catch (error) {
    console.error("Erro no logout:", error);
    alert("Falha ao se desconectar: " + error.message);
  }
}
</script>
<script src="/assets/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/custom.js"></script>
</body>
</html>