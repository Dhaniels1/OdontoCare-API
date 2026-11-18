<?php
require_once __DIR__ . '/../OdontoCare-main/api/ApiClient.php';
$method = new ReflectionMethod(ApiClient::class, 'interpretar');
$checks = [
    ['ResultWrapper lista de erros', 400, ['success'=>false,'message'=>'Dados inválidos.','data'=>null,'errors'=>['Nome obrigatório.','CPF inválido.']], 'Nome obrigatório. CPF inválido.'],
    ['ResultWrapper sucesso', 200, ['success'=>true,'message'=>'OK','data'=>['id'=>1],'errors'=>[]], 'OK'],
    ['Validacao ASP.NET em dicionario', 400, ['errors'=>['Nome'=>['Informe o nome.'],'Email'=>['E-mail inválido.']]], 'Informe o nome. E-mail inválido.']
];
foreach ($checks as [$nome, $status, $body, $expected]) {
    $r = $method->invoke(null, $status, json_encode($body, JSON_UNESCAPED_UNICODE));
    if ($r['mensagem'] !== $expected) { fwrite(STDERR, "FAIL: $nome\n"); exit(1); }
    echo "PASS: $nome\n";
}
