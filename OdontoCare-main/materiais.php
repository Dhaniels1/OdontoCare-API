<?php
session_start();
if (empty($_SESSION['usuario'])) { header('Location: login.php'); exit; }
require_once 'permissao.php';
require_once 'api/ApiClient.php';
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));

$mensagem = null;
$erro = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit('Requisição inválida.');
    }
    if (!podeEditar()) { http_response_code(403); exit('Sem permissão.'); }
    $acao = $_POST['acao'] ?? '';
    $id = intval($_POST['id'] ?? 0);
    if ($acao === 'criar') {
        $r = ApiClient::requisitar('POST','api/materiais', [
            'nomeMaterial' => trim($_POST['nomeMaterial'] ?? ''),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'estoqueMinimo' => intval($_POST['estoqueMinimo'] ?? 0),
            'unidade' => trim($_POST['unidade'] ?? '')
        ]);
    } elseif ($acao === 'entrada' || $acao === 'saida') {
        $tipo = $acao === 'entrada' ? 'entradas' : 'saidas';
        $r = ApiClient::requisitar('POST', 'api/materiais/' . $id . '/' . $tipo,
            ['quantidade' => intval($_POST['quantidade'] ?? 0)]);
    } else { $r = ['sucesso' => false, 'mensagem' => 'Ação inválida.']; }
    if ($r['sucesso']) $mensagem = 'Operação realizada com sucesso.';
    else $erro = $r['mensagem'];
}
$r = ApiClient::requisitar('GET','api/materiais?pagina=1&tamanho=100');
if (!$r['sucesso']) $erro = $r['mensagem'];
$itens = $r['dados']['items'] ?? [];
function e($valor) { return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!doctype html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Materiais - OdontoCare</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light"><?php require 'navbar.php'; ?>
<div class="container py-5"><h1>Materiais e Estoque</h1>
<?php if ($erro): ?><div class="alert alert-danger"><?=e($erro)?></div><?php endif; ?>
<?php if ($mensagem): ?><div class="alert alert-success"><?=e($mensagem)?></div><?php endif; ?>
<?php if (podeEditar()): ?>
<section class="card p-3 mb-4"><h2 class="h5">Cadastrar material</h2>
<form method="post" class="row g-2"><input type="hidden" name="csrf" value="<?=e($_SESSION['csrf'])?>"><input type="hidden" name="acao" value="criar">
<div class="col-md-4"><input class="form-control" name="nomeMaterial" placeholder="Nome" maxlength="200" required></div>
<div class="col-md-3"><input class="form-control" name="unidade" placeholder="Unidade (caixa, unidade...)" maxlength="30" required></div>
<div class="col-md-2"><input class="form-control" name="estoqueMinimo" type="number" min="0" placeholder="Mínimo" required></div>
<div class="col-md-3"><input class="form-control" name="descricao" placeholder="Descrição"></div>
<div class="col-12"><button class="btn btn-primary">Cadastrar</button></div></form></section>
<?php endif; ?>
<div class="table-responsive"><table class="table table-striped"><thead><tr><th>ID</th><th>Nome</th><th>Saldo</th><th>Mínimo</th><th>Unidade</th><th>Situação</th><?php if(podeEditar()):?><th>Movimentação</th><?php endif; ?></tr></thead><tbody>
<?php foreach($itens as $m): ?><tr><td><?=e($m['idMaterial'])?></td><td><?=e($m['nomeMaterial'])?></td><td><?=e($m['quantidade'])?></td><td><?=e($m['estoqueMinimo'])?></td><td><?=e($m['unidade'])?></td><td><?=!empty($m['estoqueBaixo']) ? '<span class="badge text-bg-warning">Estoque baixo</span>':'Normal'?></td>
<?php if(podeEditar()):?><td><form method="post" class="d-flex gap-1">
<input type="hidden" name="csrf" value="<?=e($_SESSION['csrf'])?>"><input type="hidden" name="id" value="<?=e($m['idMaterial'])?>">
<input type="number" class="form-control form-control-sm" name="quantidade" min="1" placeholder="Qtd" required style="max-width:85px">
<button class="btn btn-sm btn-success" name="acao" value="entrada">Entrada</button><button class="btn btn-sm btn-warning" name="acao" value="saida">Saída</button></form></td><?php endif; ?></tr><?php endforeach; ?>
</tbody></table></div>
<p class="text-muted">A lista apresenta até 100 materiais por página. Outras páginas estão disponíveis pela API.</p></div>
</body></html>
