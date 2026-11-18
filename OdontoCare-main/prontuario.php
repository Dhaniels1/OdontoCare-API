<?php
session_start();
require_once __DIR__ . '/permissao.php';
if (!isset($_SESSION['usuario'])) { header('Location: login.php'); exit; }
if (!in_array($_SESSION['tipoUsuario'] ?? '', ['Dentista', 'Administrador'], true)) {
    http_response_code(403);
    require_once __DIR__ . '/api/DadosApi.php';
    exibirErroApiPagina('Seu perfil não tem acesso aos prontuários clínicos.', 403);
}
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) { header('Location: paciente.php'); exit; }
require_once __DIR__ . '/api/ApiClient.php';
$r = ApiClient::requisitar('GET', 'api/pacientes/' . $id . '/prontuario');
if (!$r['sucesso']) {
    require_once __DIR__ . '/api/DadosApi.php';
    exibirErroApiPagina($r['mensagem'], $r['status'] ?: 503);
}
$dados = $r['dados'];
header('Cache-Control: no-store, private');
?>
<!DOCTYPE html>
<html lang="pt-br"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Prontuário clínico - OdontoCare</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<?php include __DIR__ . '/navbar.php'; ?>
<main class="container mt-5"><div class="card p-4 shadow-sm">
<h1 class="h3">Prontuário clínico do paciente #<?= e($id) ?></h1>
<p class="text-muted">Consulta clínica somente para profissionais vinculados e administradores.</p>
<?php foreach (['historicoOdontologico'=>'Histórico odontológico','alergias'=>'Alergias','exames'=>'Exames','tratamentosAnteriores'=>'Tratamentos anteriores','prontuarioClinicoDigital'=>'Prontuário clínico digital'] as $campo=>$titulo): ?>
<section class="mb-3"><h2 class="h5"><?= e($titulo) ?></h2><p class="border rounded bg-light p-3" style="white-space:pre-wrap"><?= e($dados[$campo] ?? 'Não informado') ?></p></section>
<?php endforeach; ?>
<a class="btn btn-secondary" href="paciente.php">Voltar aos pacientes</a>
</div></main></body></html>
