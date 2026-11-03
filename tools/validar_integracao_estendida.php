<?php
// Cria dados sintéticos. Execute somente em uma instância descartável de teste.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
if (getenv('ODONTOCARE_VALIDACAO_ISOLADA') !== '1') {
 fwrite(STDERR,"Defina ODONTOCARE_VALIDACAO_ISOLADA=1 somente para um ambiente isolado.\n"); exit(2);
}
foreach (['ODONTOCARE_API_URL','ODONTOCARE_DB_HOST','ODONTOCARE_DB_NAME','ODONTOCARE_DB_USER','ODONTOCARE_DB_PASS'] as $key) {
 if (getenv($key) === false) { fwrite(STDERR,"Configure $key.\n"); exit(2); }
}
require __DIR__.'/../OdontoCare-main/api/ApiClient.php';
session_start();
$host=getenv('ODONTOCARE_DB_HOST'); $database=getenv('ODONTOCARE_DB_NAME');
$db=new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4",getenv('ODONTOCARE_DB_USER'),getenv('ODONTOCARE_DB_PASS'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$results=[];
function note($name,$passed,$detail=null) {global $results; $results[]=['test'=>$name,'passed'=>$passed,'detail'=>$detail];}
function req($name,$method,$path,$body,$expected) {$r=ApiClient::requisitar($method,$path,$body); note($name,$r['status']===$expected,['expected'=>$expected,'actual'=>$r['status']]); return $r['dados'];}
function user($role) {global $db; $login='ext_'.bin2hex(random_bytes(5)); $password=bin2hex(random_bytes(16)); $salt=random_bytes(16); $hash='pbkdf2-sha256$210000$'.base64_encode($salt).'$'.base64_encode(hash_pbkdf2('sha256',$password,$salt,210000,32,true)); $db->prepare('INSERT INTO usuario(nome,login,senha,tipoUsuario) VALUES(?,?,?,?)')->execute(['Teste '.$role,$login,$hash,$role]); $id=(int)$db->lastInsertId(); $r=ApiClient::requisitar('POST','api/auth/login',['identificador'=>$login,'senha'=>$password]); return [$id,$r['dados']['token']??''];}
function parallel($path,$body,$n) {$multi=curl_multi_init(); $handles=[]; for($i=0;$i<$n;$i++){ $h=curl_init(ApiClient::baseUrl().'/'.$path); curl_setopt_array($h,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>30,CURLOPT_POSTFIELDS=>json_encode($body),CURLOPT_HTTPHEADER=>['Content-Type: application/json','Authorization: Bearer '.$_SESSION['jwt']]]); curl_multi_add_handle($multi,$h); $handles[]=$h; } do {curl_multi_exec($multi,$active); if($active) curl_multi_select($multi,1);} while($active); $codes=[]; foreach($handles as $h){$codes[]=curl_getinfo($h,CURLINFO_HTTP_CODE);curl_multi_remove_handle($multi,$h);curl_close($h);} curl_multi_close($multi); return $codes;}
[$admin,$token]=user('Administrador'); [$dentist,$dentToken]=user('Dentista'); [$otherDentist,$otherToken]=user('Dentista'); $_SESSION['jwt']=$token;
$name='Material teste '.bin2hex(random_bytes(4));
$m=req('Criar material','POST','api/materiais',['nomeMaterial'=>$name,'unidade'=>'un','estoqueMinimo'=>2],201); $mid=$m['idMaterial'];
note('Material inicia com saldo zero e alerta',$m['quantidade']===0 && $m['estoqueBaixo']===true);
req('Quantidade zero rejeitada','POST',"api/materiais/$mid/entradas",['quantidade'=>0],400);
req('Saldo insuficiente rejeitado','POST',"api/materiais/$mid/saidas",['quantidade'=>1],409);
req('Entrada estoque','POST',"api/materiais/$mid/entradas",['quantidade'=>10],200);
req('Saida estoque','POST',"api/materiais/$mid/saidas",['quantidade'=>3],200);
$list=req('Filtro de materiais','GET','api/materiais?busca='.urlencode($name),null,200);
note('Cache reflete saldo atualizado',$list['total']===1 && $list['items'][0]['quantidade']===7);
$history=req('Historico estoque','GET',"api/materiais/$mid/movimentacoes",null,200);
note('Historico preserva usuario e quantidades',count($history)===2 && array_sum(array_column($history,'quantidade'))===13 && count(array_filter($history,fn($x)=>$x['idUsuario']===$admin))===2);
req('Material com historico nao pode ser excluido','DELETE',"api/materiais/$mid",null,409);
req('Paginacao invalida','GET','api/materiais?pagina=0',null,400);
$codes=parallel("api/materiais/$mid/saidas",['quantidade'=>1],12);
$stock=(int)$db->query("SELECT quantidade FROM material WHERE idMaterial=$mid")->fetchColumn();
$hist=(int)$db->query("SELECT COUNT(*) FROM movimentacao_estoque WHERE idMaterial=$mid AND tipo='Saida'")->fetchColumn();
$success=count(array_filter($codes,fn($x)=>$x===200));
note('Saidas concorrentes preservam saldo e historico',$stock>=0 && $success+$stock===7 && $hist===$success+1,['http'=>$codes,'saldo'=>$stock,'sucessos'=>$success,'historicoSaidas'=>$hist]);
note('Saidas concorrentes nao geram erro interno',!in_array(500,$codes),$codes);
$e=req('Criar equipamento','POST','api/equipamentos',['nomeEquipamento'=>'Equipamento sintetico','statusEquipamento'=>'Ativo'],201); $eid=$e['idEquipamento'];
req('Atualizar equipamento','PUT',"api/equipamentos/$eid",['nomeEquipamento'=>'Equipamento atualizado','statusEquipamento'=>'Manutencao'],200);
req('Listar equipamentos','GET','api/equipamentos',null,200);
req('Equipamento invalido','POST','api/equipamentos',[],400);
req('Equipamento inexistente','GET','api/equipamentos/2147483647',null,404);
$_SESSION['jwt']=$dentToken;
req('Dentista nao altera estoque','POST',"api/materiais/$mid/entradas",['quantidade'=>1],403);
req('Dentista nao exclui equipamento','DELETE',"api/equipamentos/$eid",null,403);
$_SESSION['jwt']=$token;
$patient=req('Paciente para agenda','POST','api/pacientes',['nome'=>'Agenda sintetica','cpf'=>substr((string)hrtime(true),-11)],201);
$db->prepare('INSERT INTO procedimento(nomeProcedimento,valorProcedimento) VALUES(?,?)')->execute(['Procedimento sintetico',10]); $pid=(int)$db->lastInsertId();
$base=['dataConsulta'=>'2027-01-15','horarioConsulta'=>'09:00:00','statusConsulta'=>'Agendada','idUsuario'=>$dentist,'idPaciente'=>$patient['idPaciente'],'idProcedimento'=>$pid];
$c=req('Agendar consulta','POST','api/consultas',$base,201); $cid=$c['idConsulta'];
req('Conflito sequencial de agenda','POST','api/consultas',$base,409);
req('Horario fora do expediente','POST','api/consultas',array_replace($base,['horarioConsulta'=>'23:00:00']),409);
req('Relacionamento inexistente','POST','api/consultas',array_replace($base,['idPaciente'=>2147483647]),409);
$_SESSION['jwt']=$otherToken;
req('Dentista nao acessa consulta alheia','GET',"api/consultas/$cid",null,404);
$own=req('Lista restrita ao dentista','GET','api/consultas',null,200);
note('Lista nao vaza consulta alheia',count(array_filter($own,fn($x)=>$x['idConsulta']===$cid))===0);
$_SESSION['jwt']=$token;
req('Cancelar consulta','PATCH',"api/consultas/$cid/cancelar",null,200);
$cancel=req('Consulta cancelada preservada','GET',"api/consultas/$cid",null,200);
note('Status cancelado persistido',$cancel['statusConsulta']==='Cancelada');
req('Reagendar horario liberado','POST','api/consultas',$base,201);
$second=req('Segunda consulta em horario livre','POST','api/consultas',array_replace($base,['horarioConsulta'=>'10:00:00']),201);
req('Atualizacao nao invade horario ocupado','PUT','api/consultas/'.$second['idConsulta'],$base,409);
req('Atualizacao para horario livre','PUT','api/consultas/'.$second['idConsulta'],array_replace($base,['horarioConsulta'=>'10:30:00']),200);
$updated=req('Leitura da consulta atualizada','GET','api/consultas/'.$second['idConsulta'],null,200);
note('Atualizacao de horario persistida',$updated['horarioConsulta']==='10:30:00');
$race=array_replace($base,['horarioConsulta'=>'11:00:00']); $codes=parallel('api/consultas',$race,12);
$count=(int)$db->query("SELECT COUNT(*) FROM consulta WHERE idUsuario=$dentist AND dataConsulta='2027-01-15' AND horarioConsulta='11:00:00' AND statusConsulta<>'Cancelada'")->fetchColumn();
note('Concorrencia agenda permite somente uma reserva',$count===1 && count(array_filter($codes,fn($x)=>$x===201))===1,['http'=>$codes,'reservasPersistidas'=>$count]);
$output=['database'=>$db->query('SELECT VERSION()')->fetchColumn(),'results'=>$results];
$outputPath=getenv('ODONTOCARE_VALIDACAO_RESULTADO');
if ($outputPath !== false && $outputPath !== '') file_put_contents($outputPath,json_encode($output,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
foreach($results as $r) echo ($r['passed']?'PASS ':'FAIL ').$r['test'].' '.json_encode($r['detail'],JSON_UNESCAPED_UNICODE).PHP_EOL;
exit(count(array_filter($results,fn($r)=>!$r['passed']))?1:0);
