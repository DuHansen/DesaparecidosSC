<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');
include 'includes/headerUser.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Painel Administrativo - Desaparecidos SC</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Desaparecidos</h2>
      <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
        <a href="cadastro.php" class="btn btn-primary">
          <i class="fas fa-plus me-1"></i> Novo Cadastro
        </a>
      <?php endif; ?>
    </div>
  </div>
  <div class="container">
  <div class="card-body">
    <table id="desaparecidosTable" class="table table-striped">
      <thead>
        <tr>
          <th>Foto</th>
          <th>Nome</th>
          <th>Idade</th>
          <th>Data Desap.</th>
          <th>Cidade</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody id="tabela-body">
        <!-- Os dados são inseridos pelo JavaScript -->
      </tbody>
    </table>
    <nav>
      <ul class="pagination justify-content-center" id="paginacao"></ul>
    </nav>
  </div>
</div>


  <!-- Modal de Visualização -->
  <div class="modal fade" id="visualizarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Detalhes do Desaparecido</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-4 text-center">
              <img id="viewFoto" src="" class="img-fluid rounded mb-3" alt="Foto">
            </div>
            <div class="col-md-8">
              <h4 id="viewNome"></h4>
              <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Idade:</strong> <span id="viewIdade"></span></li>
                <li class="list-group-item"><strong>Desaparecido em:</strong> <span id="viewData"></span></li>
                <li class="list-group-item"><strong>Cidade:</strong> <span id="viewCidade"></span></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Edição -->
  <div class="modal fade" id="editarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Editar Desaparecido</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="formEditar">
            <input type="hidden" id="editIndex">
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="editNome" class="form-label">Nome Completo</label>
                <input type="text" class="form-control" id="editNome" name="nome_completo" required>
              </div>
              <div class="col-md-6">
                <label for="editIdade" class="form-label">Idade</label>
                <input type="number" class="form-control" id="editIdade" name="idade" required>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="editData" class="form-label">Data Desaparecimento</label>
                <input type="date" class="form-control" id="editData" name="desaparecidoEm" required>
              </div>
              <div class="col-md-6">
                <label for="editCidade" class="form-label">Cidade</label>
                <input type="text" class="form-control" id="editCidade" name="cidade" required>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-12">
                <label for="editFoto" class="form-label">URL da Foto</label>
                <input type="url" class="form-control" id="editFoto" name="foto" required>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" id="btnSalvarEdicao">Salvar Alterações</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Confirmação de Exclusão -->
  <div class="modal fade" id="confirmarExclusaoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirmar Exclusão</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Tem certeza que deseja excluir este registro? Esta ação não pode ser desfeita.</p>
          <input type="hidden" id="excluirIndex">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-danger" id="btnConfirmarExclusao">Excluir</button>
        </div>
      </div>
    </div>
  </div>
  <br>
<section class="bg-light border-top border-3 border-danger shadow-sm">
  <div class="container-fluid py-4">
    <div class="row">
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2 class="text-danger fw-bold">
            <i class="bi bi-exclamation-circle-fill me-2"></i> Denúncias Registradas
          </h2>
          <span class="badge bg-secondary">Atualizado em <?= date('d/m/Y') ?></span>
        </div>

        <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
          <i class="bi bi-info-circle-fill me-2"></i>
          Esta seção exibe todas as denúncias com dados do denunciante e da pessoa desaparecida.
        </div>

        <div id="cardsContainer" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
          <!-- Os cards serão inseridos aqui via JavaScript -->
        </div>
      </main>
    </div>
  </div>
</section>

  <!-- Função para calcular idade -->
  <?php
  function calcularIdade($dataNascimento) {
      if (empty($dataNascimento)) return 'Não informada';
      $hoje = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
      $nascimento = new DateTime($dataNascimento);
      $idade = $nascimento->diff($hoje)->y;
      return $idade . ' anos';
  }
  ?>

<script>
    document.getElementById('sidebarToggle')?.addEventListener('click', function() {
      document.getElementById('sidebar').classList.toggle('collapsed');
    });
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
  const itensPorPagina = 10;
  let dados = [];
  let paginaAtual = parseInt(new URLSearchParams(window.location.search).get('pagina')) || 1;
  let itemAtual = null;

  function carregarDados() {
  const termo = new URLSearchParams(window.location.search).get('q') || '';
  const pagina = parseInt(new URLSearchParams(window.location.search).get('pagina')) || 1;
  const baseUrl = 'http://localhost:8000'; // ajuste para sua porta correta
  const url = termo
    ? `${baseUrl}/api/desaparecidos?filtro=nome&valor=${encodeURIComponent(termo)}&page=${pagina}&limit=${itensPorPagina}`
    : `${baseUrl}/api/desaparecidos?page=${pagina}&limit=${itensPorPagina}`;


  $.ajax({
    url: url,
    type: 'GET',
    dataType: 'json',
    success: function(response) {
      dados = response.data;
      exibirPagina(pagina);         // ainda usa o array paginado
      criarPaginacao(response);     // agora funciona corretamente
    }
    ,
    error: function(xhr, status, error) {
      console.error("Erro:", status, error);
    }
  });}

  function exibirPagina(pagina) {
  paginaAtual = pagina;
  const inicio = (pagina - 1) * itensPorPagina;
  const fim = inicio + itensPorPagina;
  const itensPagina = dados.slice(0, itensPorPagina); // já paginado no backend

  const tbody = $('#tabela-body');
  tbody.empty();

  itensPagina.forEach((item, index) => {
    tbody.append(`
      <tr data-index="${inicio + index}">
        <td><img src="${item.foto}" width="50" height="50" style="object-fit:cover;"></td>
        <td>${item.nome_completo}</td>
        <td>${calcularIdade(item.data_nascimento)} anos</td>
        <td>${formatarData(item.data_desaparecimento)}</td>
        <td>${item.cidade}</td>
        <td>
          <button class="btn btn-sm btn-info visualizar"><i class="fas fa-eye"></i></button>
          <button class="btn btn-sm btn-primary editar"><i class="fas fa-edit"></i></button>
          <button class="btn btn-sm btn-danger excluir"><i class="fas fa-trash"></i></button>
        </td>
      </tr>
    `);
  });

  $('.visualizar').click(function() {
    const index = $(this).closest('tr').data('index') % itensPorPagina;
    mostrarModalVisualizar(dados[index]);
  });

  $('.editar').click(function() {
    const index = $(this).closest('tr').data('index') % itensPorPagina;
    mostrarModalEditar(dados[index]);
  });

  $('.excluir').click(function() {
    const index = $(this).closest('tr').data('index') % itensPorPagina;
    mostrarModalExcluir(dados[index]);
  });
}


  function criarPaginacao(response) {
  const totalPaginas = response.last_page;
  const paginaAtual = response.current_page;

  const termo = new URLSearchParams(window.location.search).get('q') || '';
  const filtroURL = termo ? `&q=${encodeURIComponent(termo)}` : '';

  let inicio = Math.max(1, paginaAtual - 2);
  let fim = Math.min(totalPaginas, paginaAtual + 2);

  let pagHtml = `
    <nav aria-label="Navegação de páginas" class="mt-5">
      <div class="d-flex justify-content-center overflow-auto px-2">
        <ul class="pagination d-flex flex-wrap justify-content-center gap-2 mb-0">
  `;

  if (paginaAtual > 1) {
    pagHtml += `
      <li class="page-item">
        <a class="page-link rounded-pill border-0 shadow-sm px-4 py-2 text-dark"
           href="?pagina=${paginaAtual - 1}${filtroURL}">
          <i class="bi bi-chevron-left"></i>
        </a>
      </li>`;
  }

  for (let i = inicio; i <= fim; i++) {
    pagHtml += `
      <li class="page-item ${i === paginaAtual ? 'active' : ''}">
        <a class="page-link rounded-pill border-0 px-4 py-2 ${i === paginaAtual ? 'bg-dark text-white shadow-sm' : 'bg-light text-dark'}"
           href="?pagina=${i}${filtroURL}">
          ${i}
        </a>
      </li>`;
  }

  if (paginaAtual < totalPaginas) {
    pagHtml += `
      <li class="page-item">
        <a class="page-link rounded-pill border-0 shadow-sm px-4 py-2 text-dark"
           href="?pagina=${paginaAtual + 1}${filtroURL}">
          <i class="bi bi-chevron-right"></i>
        </a>
      </li>`;
  }

  pagHtml += `
        </ul>
      </div>
    </nav>`;

  $('#paginacao').html(pagHtml);
}


  function calcularIdade(dataNascimento) {
    const nascimento = new Date(dataNascimento);
    const hoje = new Date();
    let idade = hoje.getFullYear() - nascimento.getFullYear();
    if (hoje.getMonth() < nascimento.getMonth() ||
        (hoje.getMonth() === nascimento.getMonth() && hoje.getDate() < nascimento.getDate())) {
      idade--;
    }
    return idade;
  }

  function mostrarModalVisualizar(item) {
    $('#viewFoto').attr('src', item.foto);
    $('#viewNome').text(item.nome);
    $('#viewIdade').text(calcularIdade(item.data_nascimento) + ' anos');
    $('#viewData').text(item.desaparecidoEm);
    $('#viewCidade').text(item.cidade);
    new bootstrap.Modal($('#visualizarModal')).show();
  }

function mostrarModalEditar(item) {
  itemAtual = item;

  $('#editIndex').val(item.id); // <- garante o ID
  $('#editNome').val(item.nome_completo); // não item.nome
  $('#editIdade').val(calcularIdade(item.data_nascimento));
  $('#editData').val(new Date(item.data_desaparecimento || item.data_nascimento).toISOString().split('T')[0]);
  $('#editCidade').val(item.cidade);
  $('#editFoto').val(item.foto);

  new bootstrap.Modal($('#editarModal')).show();
}
const baseUrl = 'http://localhost:8000';

$('#btnSalvarEdicao').click(function () {
  const id = $('#editIndex').val();
  const nome_completo = $('#editNome').val();
  const cidade = $('#editCidade').val();
  const data_desaparecimento = $('#editData').val();
  const foto = $('#editFoto').val();

  const payload = {
    nome_completo,
    cidade,
    data_desaparecimento,
    foto
  };

  $.ajax({
    url: `${baseUrl}/api/desaparecidos/${id}`,
    type: 'PUT',
    contentType: 'application/json', // IMPORTANTE
    data: JSON.stringify(payload),   // Envio como JSON puro
    success: function (response) {
      alert('Alterações salvas com sucesso!');
      carregarDados();
      bootstrap.Modal.getInstance($('#editarModal')).hide();
    },
    error: function (xhr) {
      console.error(xhr);
      alert('Erro ao editar: ' + (xhr.responseJSON?.message || 'Erro desconhecido'));
    }
  });
});




  function mostrarModalExcluir(item) {
    itemAtual = item;
    new bootstrap.Modal($('#confirmarExclusaoModal')).show();
  }

  $('#btnConfirmarExclusao').click(function() {
    $.ajax({
      url: 'back/consultas/excluirDesaparecido.php',
      type: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({ id: itemAtual.id }),
      success: function(response) {
        alert(response.sucesso);
        carregarDados();
        bootstrap.Modal.getInstance($('#confirmarExclusaoModal')).hide();
      },
      error: function(xhr) {
        alert('Erro ao excluir: ' + xhr.responseJSON.erro);
      }
    });
  });

  carregarDados();
});
</script>
<script>
async function carregarDenuncias() {
  const container = document.getElementById('cardsContainer');
  container.innerHTML = '<div class="col-12 text-center"><div class="spinner-border" role="status"></div></div>';

  try {
    const response = await fetch('back/listas/todasDenuncias.php');
    const result = await response.json();

    if (!result.success || !result.data.length) {
      container.innerHTML = `
        <div class="col-12">
          <div class="alert alert-info">Nenhuma denúncia pendente encontrada.</div>
        </div>`;
      return;
    }

    container.innerHTML = '';

    result.data.forEach(p => {
      const card = document.createElement('div');
      card.className = 'col-md-4 mb-4';

      const imagem = p.foto?.startsWith('data:image') ? p.foto : `../uploads/${p.foto}`;
      const statusClasse = p.status === 'RESOLVIDA' ? 'success' : 
                          p.status === 'REJEITADA' ? 'danger' : 
                          p.status === 'EM_ANALISE' ? 'warning' : 'info';
      const nome = p.nome_completo ?? 'Desconhecido';

      card.innerHTML = `
        <div class="card h-100 position-relative">
          <span class="badge bg-${statusClasse} position-absolute top-0 end-0 m-2">
            ${p.status.replace('_', ' ')}
          </span>
          ${p.foto ? `<img src="${imagem}" class="card-img-top" alt="${nome}" style="height: 200px; object-fit: cover;">` : ''}
          <div class="card-body">
            <h5 class="card-title">${nome}</h5>
            <p class="card-text">
              <strong>Apelido:</strong> ${p.apelido || '-'}<br>
              <strong>Idade:</strong> ${p.idade || calcularIdade(p.data_nascimento)} anos<br>
              <strong>Desaparecido em:</strong> ${formatarData(p.data_desaparecimento)}<br>
              <strong>Cidade:</strong> ${p.cidade}<br>
              <strong>Último local visto:</strong> ${p.ultimo_local || '-'}<br>
              <strong>Altura:</strong> ${p.altura ? p.altura + ' cm' : '-'}<br>
              <strong>Peso:</strong> ${p.peso ? p.peso + ' kg' : '-'}<br>
              <strong>Cor da pele:</strong> ${p.cor_pele || '-'}
            </p>
            <hr>
            <p class="card-text text-muted small">
              <strong>Denunciado por:</strong> ${p.denunciante_nome}<br>
              <strong>Contato:</strong> ${p.denunciante_email}
            </p>
          </div>
          <div class="card-footer bg-transparent d-flex justify-content-between">
            <button class="btn btn-success btn-sm" onclick="abrirModalAprovar(${p.id})">
              <i class="fas fa-check"></i> Aprovar
            </button>
            <button class="btn btn-danger btn-sm" onclick="abrirModalReprovar(${p.id})">
              <i class="fas fa-times"></i> Reprovar
            </button>
          </div>
        </div>
      `;
      container.appendChild(card);
    });

    // Adiciona os modais ao final do body
    document.body.insertAdjacentHTML('beforeend', `
      <!-- Modal Aprovar -->
      <div class="modal fade" id="modalAprovar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header bg-success text-white">
              <h5 class="modal-title">Confirmar Aprovação</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p>Tem certeza que deseja aprovar esta denúncia? O caso será movido para a lista de desaparecidos.</p>
              <textarea id="observacaoAprovar" class="form-control mt-3" placeholder="Observações (opcional)"></textarea>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="button" class="btn btn-success" onclick="aprovarDenuncia()">Confirmar Aprovação</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Reprovar -->
      <div class="modal fade" id="modalReprovar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header bg-danger text-white">
              <h5 class="modal-title">Confirmar Reprovação</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p>Tem certeza que deseja reprovar esta denúncia? Informe o motivo abaixo:</p>
              <textarea id="motivoReprovar" class="form-control mt-3" placeholder="Motivo da reprovação" required></textarea>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="button" class="btn btn-danger" onclick="reprovarDenuncia()">Confirmar Reprovação</button>
            </div>
          </div>
        </div>
      </div>
    `);

  } catch (error) {
    container.innerHTML = `
      <div class="col-12">
        <div class="alert alert-danger">Erro ao carregar denúncias.</div>
      </div>`;
    console.error(error);
  }
}

// Variáveis globais para armazenar o ID da denúncia sendo manipulada
let denunciaIdAtual = null;

function abrirModalAprovar(id) {
  denunciaIdAtual = id;
  const modal = new bootstrap.Modal(document.getElementById('modalAprovar'));
  modal.show();
}

function abrirModalReprovar(id) {
  denunciaIdAtual = id;
  const modal = new bootstrap.Modal(document.getElementById('modalReprovar'));
  modal.show();
}

async function aprovarDenuncia() {
  const observacao = document.getElementById('observacaoAprovar').value;
  
  try {
    const response = await fetch('back/consultas/adicionarDenuncia.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        id: denunciaIdAtual,
        observacao: observacao
      })
    });

    const result = await response.json();
    
    if (result.success) {
      alert('Denúncia aprovada com sucesso!');
      carregarDenuncias();
      bootstrap.Modal.getInstance(document.getElementById('modalAprovar')).hide();
    } else {
      alert('Erro ao aprovar denúncia: ' + (result.message || 'Erro desconhecido'));
    }
  } catch (error) {
    console.error(error);
    alert('Erro ao aprovar denúncia');
  }
}

async function reprovarDenuncia() {
  const motivo = document.getElementById('motivoReprovar').value;
  
  if (!motivo) {
    alert('Por favor, informe o motivo da reprovação');
    return;
  }

  try {
    const response = await fetch('back/consultas/excluirDenuncia.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        id: denunciaIdAtual,
        motivo: motivo
      })
    });

    const result = await response.json();
    
    if (result.success) {
      alert('Denúncia reprovada com sucesso!');
      carregarDenuncias();
      bootstrap.Modal.getInstance(document.getElementById('modalReprovar')).hide();
    } else {
      alert('Erro ao reprovar denúncia: ' + (result.message || 'Erro desconhecido'));
    }
  } catch (error) {
    console.error(error);
    alert('Erro ao reprovar denúncia');
  }
}

// Funções auxiliares permanecem as mesmas
function formatarData(dataStr) {
  const data = new Date(dataStr);
  return isNaN(data) ? '-' : data.toLocaleDateString('pt-BR');
}

function calcularIdade(dataNascimento) {
  if (!dataNascimento) return '-';
  const nasc = new Date(dataNascimento);
  const hoje = new Date();
  let idade = hoje.getFullYear() - nasc.getFullYear();
  const m = hoje.getMonth() - nasc.getMonth();
  if (m < 0 || (m === 0 && hoje.getDate() < nasc.getDate())) idade--;
  return idade;
}

window.addEventListener('DOMContentLoaded', carregarDenuncias);
</script>
</body>
</html>
