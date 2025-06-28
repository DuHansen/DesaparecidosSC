<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user'])) {
  // Redireciona para o login
  header('Location: ../index.html');
  exit;
}

$user = $_SESSION['user'];
?>
<?php include 'includes/headerUser.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Perfil de <?= htmlspecialchars($user['name']) ?></title>
  <!-- Bootstrap LOCAL -->
  <link href="assets/css/bootstrap-5.3.6-dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="assets/css/bootstrap-5.3.6-dist/js/bootstrap.bundle.min.js"></script>
  <style>
    .profile-card {
      border-radius: 15px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      transition: transform 0.3s ease;
    }
    .profile-card:hover {
      transform: translateY(-5px);
    }
    .profile-header {
      background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
      color: white;
      padding: 2rem;
      text-align: center;
    }
    .profile-img {
      width: 150px;
      height: 150px;
      object-fit: cover;
      border: 5px solid white;
      margin-top: -75px;
    }
    .info-item {
      padding: 0.75rem 1.25rem;
      border-bottom: 1px solid rgba(0,0,0,.125);
    }
    .info-item:last-child {
      border-bottom: none;
    }
    .btn-edit {
      background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
      border: none;
    }
  </style>
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="profile-card bg-white mb-4">
          <div class="profile-header">
            <h1 class="display-5 fw-bold"><?= htmlspecialchars($user['name']) ?></h1>
            <p class="lead"><?= htmlspecialchars($user['role']) ?></p>
          </div>
          
          <div class="text-center">
            <img src="<?= htmlspecialchars($user['foto']) ?>" alt="Foto de Perfil" class="profile-img rounded-circle shadow">
          </div>
          
          <div class="p-4">
            <div class="d-flex justify-content-between mb-4">
              <button class="btn btn-edit text-white fw-bold" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                <i class="bi bi-pencil-square"></i> Editar Perfil
              </button>
              
              <form action="../api/logout.php" method="post">
                <button type="submit" class="btn btn-outline-danger fw-bold">
                  <i class="bi bi-box-arrow-right"></i> Sair
                </button>
              </form>
            </div>
            
            <div class="list-group list-group-flush">
              <div class="info-item list-group-item">
                <div class="row">
                  <div class="col-sm-4 fw-bold">ID do Usuário</div>
                  <div class="col-sm-8"><?= $user['id'] ?></div>
                </div>
              </div>
              <div class="info-item list-group-item">
                <div class="row">
                  <div class="col-sm-4 fw-bold">Nome</div>
                  <div class="col-sm-8"><?= htmlspecialchars($user['name']) ?></div>
                </div>
              </div>
              <div class="info-item list-group-item">
                <div class="row">
                  <div class="col-sm-4 fw-bold">Email</div>
                  <div class="col-sm-8"><?= htmlspecialchars($user['email']) ?></div>
                </div>
              </div>
              <div class="info-item list-group-item">
                <div class="row">
                  <div class="col-sm-4 fw-bold">Criado em</div>
                  <div class="col-sm-8"><?= $user['created_at'] ?></div>
                </div>
              </div>
              <div class="info-item list-group-item">
                <div class="row">
                  <div class="col-sm-4 fw-bold">Atualizado em</div>
                  <div class="col-sm-8"><?= $user['updated_at'] ?></div>
                </div>
              </div>
              <div class="info-item list-group-item">
                <div class="row">
                  <div class="col-sm-4 fw-bold">Função</div>
                  <div class="col-sm-8"><?= htmlspecialchars($user['role']) ?></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Edição -->
  <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="editProfileModalLabel">Editar Perfil</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <form action="editarPerfil.php" method="post">
            <div class="mb-3">
              <label for="name" class="form-label fw-bold">Nome</label>
              <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label fw-bold">Email</label>
              <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-success fw-bold">
                <i class="bi bi-check-circle"></i> Salvar Alterações
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>
</html>