<?php
// Cria e exclui SOMENTE seus próprios registros sintéticos em ambiente descartável.
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
if(getenv('ODONTOCARE_VALIDACAO_ISOLADA')!=='1'){fwrite(STDERR,"Exige ambiente isolado.\n");exit(2);}
foreach(['ODONTOCARE_API_URL','ODONTOCARE_WEB_URL','ODONTOCARE_DB_HOST','ODONTOCARE_DB_NAME','ODONTOCARE_DB_USER','ODONTOCARE_DB_PASS'] as $key){if(getenv($key)===false){fwrite(STDERR,"Configure $key.\n");exit(2);}}
require __DIR__.'/../OdontoCare-main/api/ApiClient.php';
$db=new PDO('mysql:host='.getenv('ODONTOCARE_DB_HOST').';dbname='.getenv('ODONTOCARE_DB_NAME').';charset=utf8mb4',getenv('ODONTOCARE_DB_USER'),getenv('ODONTOCARE_DB_PASS'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$results=[];
function note($name,$passed,$detail=null){global $results;$results[]=['test'=>$name,'passed'=>$passed,'detail'=>$detail];}
function browser(){$h=curl_init();curl_setopt_array($h,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEFILE=>'',CURLOPT_FOLLOWLOCATION=>false,CURLOPT_TIMEOUT=>20]);return $h;}
function http($h,$path,$post=null){curl_setopt($h,CURLOPT_URL,rtrim(getenv('ODONTOCARE_WEB_URL'),'/').'/'.$path);if($post===null)curl_setopt($h,CURLOPT_HTTPGET,true);else curl_setopt($h,CURLOPT_POSTFIELDS,http_build_query($post));$html=curl_exec($h);return ['status'=>curl_getinfo($h,CURLINFO_HTTP_CODE),'html'=>$html===false?'':$html];}
function token($html){if(!preg_match('/name="csrf" value="([a-f0-9]{64})"/',$html,$m))throw new RuntimeException('Formulário sem token.');return $m[1];}
function insert($table,$data){global $db;$cols=implode(',',array_keys($data));$slots=implode(',',array_fill(0,count($data),'?'));$db->prepare("INSERT INTO $table ($cols) VALUES($slots)")->execute(array_values($data));return (int)$db->lastInsertId();}
function account($role,$name){$login='security_'.bin2hex(random_bytes(5));$pass=bin2hex(random_bytes(16));$salt=random_bytes(16);$hash='pbkdf2-sha256$210000$'.base64_encode($salt).'$'.base64_encode(hash_pbkdf2('sha256',$pass,$salt,210000,32,true));$id=insert('usuario',['nome'=>$name,'login'=>$login,'senha'=>$hash,'tipoUsuario'=>$role]);return ['id'=>$id,'login'=>$login,'pass'=>$pass];}
function signIn($h,$account){$r=http($h,'login.php');$old=token($r['html']);$r=http($h,'controller/LoginController.php',['csrf'=>$old,'btEntrar'=>1,'txtLogin'=>$account['login'],'txtSenha'=>$account['pass']]);if($r['status']!==302)throw new RuntimeException('Login de teste falhou.');$r=http($h,'index.php');return [$old,token($r['html'])];}
function row($table,$column,$id){global $db;$q=$db->prepare("SELECT * FROM $table WHERE $column=?");$q->execute([$id]);return $q->fetch(PDO::FETCH_ASSOC);}
$probe='"><b data-odontocare-probe="test">Teste & </script></b>';
$admin=account('Administrador',$probe);$dent=account('Dentista','Dentista sintetico');$targetUser=account('Dentista',$probe);
$patient=insert('paciente',['nome'=>$probe,'cpf'=>substr((string)hrtime(true),-11),'historicoOdontologico'=>$probe]);
$procedure=insert('procedimento',['nomeProcedimento'=>$probe,'descricao'=>$probe,'valorProcedimento'=>'10.00']);
$consult=insert('consulta',['dataConsulta'=>'2027-03-11','horarioConsulta'=>'09:00:00','statusConsulta'=>'Agendada','observacoes'=>$probe,'idPaciente'=>$patient,'idUsuario'=>$dent['id'],'idProcedimento'=>$procedure]);
$payment=insert('pagamento',['valorTotal'=>'10.00','formaPagamento'=>'Pix','statusPagamento'=>'Pago','dataPagamento'=>'2027-03-11','idConsulta'=>$consult]);
$budget=insert('orcamento',['valorTotal'=>'10.00','dataOrcamento'=>'2027-03-11','statusOrcamento'=>'Pendente','observacoes'=>$probe,'idPaciente'=>$patient]);
$loosePatient=insert('paciente',['nome'=>'Exclusao sintetica','cpf'=>substr((string)hrtime(true),-11)]);
$looseProcedure=insert('procedimento',['nomeProcedimento'=>'Exclusao sintetica','valorProcedimento'=>'10.00']);
$h=browser();$other=browser();$guest=browser();$dh=browser();
$loginPage=http($guest,'login.php');$guestToken=token($loginPage['html']);
foreach([null,'invalid',['x']] as $value){$post=['btEntrar'=>1,'txtLogin'=>$admin['login'],'txtSenha'=>$admin['pass']];if($value!==null)$post['csrf']=$value;$r=http($guest,'controller/LoginController.php',$post);note('Login rejeita CSRF '.get_debug_type($value),$r['status']===403,['http'=>$r['status']]);}
[$old,$csrf]=signIn($h,$admin);[, $otherCsrf]=signIn($other,$admin);[, $dentCsrf]=signIn($dh,$dent);
note('Login troca token CSRF',$old!==$csrf);
session_start();$auth=ApiClient::requisitar('POST','api/auth/login',['identificador'=>$admin['login'],'senha'=>$admin['pass']]);$_SESSION['jwt']=$auth['dados']['token'];
$users=ApiClient::requisitar('GET','api/usuarios');note('Administrador lista usuarios sem senha',$users['status']===200 && count(array_filter($users['dados'],fn($u)=>array_key_exists('senha',$u)))===0,['http'=>$users['status'],'login'=>$auth['status']]);
$user=ApiClient::requisitar('GET','api/usuarios/'.$targetUser['id']);note('Administrador busca usuario sem senha',$user['status']===200 && $user['dados']['nome']===$probe && !array_key_exists('senha',$user['dados']),['http'=>$user['status']]);
$missing=ApiClient::requisitar('GET','api/usuarios/2147483647');note('Usuario inexistente retorna 404',$missing['status']===404,['http'=>$missing['status']]);
$auth=ApiClient::requisitar('POST','api/auth/login',['identificador'=>$dent['login'],'senha'=>$dent['pass']]);$_SESSION['jwt']=$auth['dados']['token'];$denied=ApiClient::requisitar('GET','api/usuarios');note('Dentista nao lista usuarios na API',$denied['status']===403,['http'=>$denied['status']]);unset($_SESSION['jwt']);
$r=http($h,'controller/PagamentoController.php',['csrf'=>$old,'btGravar'=>1]);note('Token anterior ao login rejeitado',$r['status']===403);
$fixtures=[['Paciente','paciente','idPaciente',$loosePatient,'id'],['Consulta','consulta','idConsulta',$consult,'id'],['Usuario','usuario','idUsuario',$targetUser['id'],'del'],['Pagamento','pagamento','idPagamento',$payment,'id'],['Orcamento','orcamento','idOrcamento',$budget,'id'],['Procedimento','procedimento','idProcedimento',$looseProcedure,'id']];
foreach($fixtures as [$controller,$table,$column,$id,$get]){
 $path='controller/'.$controller.'Controller.php';$before=row($table,$column,$id);
 $r=http($h,$path.'?'.$get.'='.$id);note('GET nao altera '.$controller,$r['status']===405 && row($table,$column,$id)===$before,['http'=>$r['status']]);
 foreach([null,'invalid',[$csrf],$otherCsrf] as $i=>$value){$post=['acao'=>'excluir','id'=>$id];if($value!==null)$post['csrf']=$value;$r=http($h,$path,$post);note('CSRF invalido '.$controller.' caso '.$i,$r['status']===403 && row($table,$column,$id)===$before,['http'=>$r['status']]);}
 $r=http($guest,$path,['csrf'=>$guestToken,'acao'=>'excluir','id'=>$id]);note('Visitante nao exclui '.$controller,$r['status']===302 && row($table,$column,$id)===$before);
 $r=http($dh,$path,['csrf'=>$dentCsrf,'acao'=>'excluir','id'=>$id]);note('Dentista nao exclui '.$controller,$r['status']===302 && row($table,$column,$id)===$before);
}
$pages=['index.php','agenda.php','paciente.php','consulta.php','usuario.php','procedimento.php','orcamento.php','pagamento.php','materiais.php',"recibo.php?id=$payment","formPaciente.php?id=$patient","formConsulta.php?id=$consult",'formUsuario.php?id='.$targetUser['id'],"formProcedimento.php?id=$procedure","formOrcamento.php?id=$budget","formPagamento.php?id=$payment"];
$encoded=htmlspecialchars($probe,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
foreach($pages as $page){$r=http($h,$page);note('HTML seguro '.$page,$r['status']===200 && !str_contains($r['html'],$probe) && str_contains($r['html'],$encoded),['http'=>$r['status']]);
 if($r['html']===''){note('Formularios protegidos '.$page,false,['http'=>$r['status']]);continue;}
 $dom=new DOMDocument();@$dom->loadHTML($r['html']);$forms=$dom->getElementsByTagName('form');$safe=true;$count=0;
 foreach($forms as $form){if(strtolower($form->getAttribute('method'))!=='post')continue;$count++;$found=false;foreach($form->getElementsByTagName('input') as $input){if($input->getAttribute('name')==='csrf' && hash_equals($csrf,$input->getAttribute('value')))$found=true;}if(!$found)$safe=false;}
 note('Formularios protegidos '.$page,$safe && ($count>0 || str_starts_with($page,'recibo.php')));
 if(str_starts_with($page,'formUsuario.php')){$xpath=new DOMXPath($dom);$name=$xpath->query('//input[@name="txtNome"]')->item(0);$password=$xpath->query('//input[@name="txtSenha"]')->item(0);note('Edicao carrega usuario e mantem senha vazia',$name && $name->getAttribute('value')===$probe && $password && $password->getAttribute('value')==='');}
 if($page==='index.php'){preg_match('/labels:\s*(\[.*?\]),/s',$r['html'],$m);$labels=isset($m[1])?json_decode($m[1],true):null;note('JSON do grafico preserva texto e nao fecha script',is_array($labels) && in_array($probe,$labels,true) && !str_contains($m[1]??'','</script>'));}
}
$patientPayload=['csrf'=>$csrf,'btGravar'=>1,'txtIdPaciente'=>$patient,'txtNome'=>$probe,'txtCpf'=>row('paciente','idPaciente',$patient)['cpf'],'txtTelefone'=>'','txtEmail'=>'','txtCep'=>'','txtLogradouro'=>'','txtNumero'=>'','txtBairro'=>'','txtCidade'=>'','txtEstado'=>'','txtDataNascimento'=>'','txtHistoricoOdontologico'=>'Historico atualizado','txtAlergias'=>'','txtExames'=>'','txtTratamentosAnteriores'=>'','txtProntuarioClinicoDigital'=>''];
$r=http($h,'controller/PacienteController.php',$patientPayload);note('Edicao paciente com CSRF valido',$r['status']===302 && row('paciente','idPaciente',$patient)['historicoOdontologico']==='Historico atualizado');
$r=http($h,'controller/ConsultaController.php',['csrf'=>$csrf,'btGravar'=>1,'txtIdConsulta'=>$consult,'txtDataConsulta'=>'2027-03-11','txtHorarioConsulta'=>'10:00','txtStatusConsulta'=>'Agendada','txtObservacoes'=>'Observacao atualizada','cbUsuario'=>$dent['id'],'cbPaciente'=>$patient,'cbProcedimento'=>$procedure]);note('Edicao consulta com CSRF valido',$r['status']===302 && row('consulta','idConsulta',$consult)['observacoes']==='Observacao atualizada');
$r=http($h,'controller/UsuarioController.php',['csrf'=>$csrf,'btGravar'=>1,'txtIdUsuario'=>$targetUser['id'],'txtNome'=>'Usuario atualizado','txtEmail'=>'teste@example.test','txtLogin'=>$targetUser['login'],'txtSenha'=>'','txtTipoUsuario'=>'Dentista']);note('Edicao usuario com CSRF valido',$r['status']===302 && row('usuario','idUsuario',$targetUser['id'])['nome']==='Usuario atualizado');
foreach(['materiais.php','logout.php'] as $path){$r=http($h,$path,['csrf'=>[$csrf],'acao'=>'criar']);note('CSRF array rejeitado '.$path,$r['status']===403);}
$r=http($h,'logout.php');note('GET nao encerra sessao',$r['status']===405 && http($h,'index.php')['status']===200);
// Testes positivos usam apenas os IDs criados por este executor.
foreach($fixtures as [$controller,$table,$column,$id,$unused]){$r=http($h,'controller/'.$controller.'Controller.php',['csrf'=>$csrf,'acao'=>'excluir','id'=>$id]);$after=row($table,$column,$id);$ok=$controller==='Consulta'?($after && $after['statusConsulta']==='Cancelada'):$after===false;note('POST autorizado '.$controller,$r['status']===302 && $ok);}
$r=http($h,'logout.php',['csrf'=>$csrf]);note('Logout POST encerra sessao',$r['status']===302 && http($h,'index.php')['status']===302);
foreach([$h,$other,$guest,$dh] as $ch)curl_close($ch);
$output=['database'=>$db->query('SELECT VERSION()')->fetchColumn(),'results'=>$results];if(getenv('ODONTOCARE_VALIDACAO_RESULTADO'))file_put_contents(getenv('ODONTOCARE_VALIDACAO_RESULTADO'),json_encode($output,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
foreach($results as $result)echo ($result['passed']?'PASS ':'FAIL ').$result['test'].' '.json_encode($result['detail'],JSON_UNESCAPED_UNICODE).PHP_EOL;
exit(count(array_filter($results,fn($r)=>!$r['passed']))?1:0);
