<?php
// Execute apenas contra banco/API/PHP descartáveis: cria dados sintéticos.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
if (getenv('ODONTOCARE_VALIDACAO_ISOLADA') !== '1') { fwrite(STDERR,"Exige ODONTOCARE_VALIDACAO_ISOLADA=1.\n"); exit(2); }
foreach (['ODONTOCARE_API_URL','ODONTOCARE_WEB_URL','ODONTOCARE_DB_HOST','ODONTOCARE_DB_NAME','ODONTOCARE_DB_USER','ODONTOCARE_DB_PASS'] as $key) {
 if (getenv($key) === false) { fwrite(STDERR,"Configure $key.\n"); exit(2); }
}
require __DIR__.'/../OdontoCare-main/api/ApiClient.php';
$db=new PDO('mysql:host='.getenv('ODONTOCARE_DB_HOST').';dbname='.getenv('ODONTOCARE_DB_NAME').';charset=utf8mb4',getenv('ODONTOCARE_DB_USER'),getenv('ODONTOCARE_DB_PASS'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$results=[];
function note($name,$ok,$detail=null) {global $results; $results[]=['test'=>$name,'passed'=>$ok,'detail'=>$detail];}
function account($role) {global $db; $login='fin_'.bin2hex(random_bytes(5)); $password=bin2hex(random_bytes(16)); $salt=random_bytes(16); $hash='pbkdf2-sha256$210000$'.base64_encode($salt).'$'.base64_encode(hash_pbkdf2('sha256',$password,$salt,210000,32,true)); $db->prepare('INSERT INTO usuario(nome,login,senha,tipoUsuario) VALUES(?,?,?,?)')->execute(['Teste '.$role,$login,$hash,$role]); return ['id'=>(int)$db->lastInsertId(),'login'=>$login,'password'=>$password];}
function browser() {$h=curl_init();curl_setopt_array($h,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEFILE=>'',CURLOPT_FOLLOWLOCATION=>false,CURLOPT_TIMEOUT=>20]);return $h;}
function web($h,$path,$data=null) {
 $key=spl_object_id($h);
 curl_setopt($h,CURLOPT_URL,rtrim(getenv('ODONTOCARE_WEB_URL'),'/').'/'.$path);
 if($data===null) curl_setopt($h,CURLOPT_HTTPGET,true);
 else {if(isset($GLOBALS['webTokens'][$key]) && !array_key_exists('csrf',$data))$data['csrf']=$GLOBALS['webTokens'][$key];curl_setopt($h,CURLOPT_POSTFIELDS,http_build_query($data));}
 $body=curl_exec($h);$body=$body===false?'':$body;
 if(preg_match('/name="csrf" value="([a-f0-9]+)"/',$body,$match))$GLOBALS['webTokens'][$key]=$match[1];
 return ['status'=>curl_getinfo($h,CURLINFO_HTTP_CODE),'redirect'=>curl_getinfo($h,CURLINFO_REDIRECT_URL),'body'=>$body];
}
function login($h,$a) {web($h,'login.php');$r=web($h,'controller/LoginController.php',['btEntrar'=>1,'txtLogin'=>$a['login'],'txtSenha'=>$a['password']]);web($h,'index.php');return $r;}
function countRows($table) {global $db;return (int)$db->query("SELECT COUNT(*) FROM $table")->fetchColumn();}
$admin=account('Administrador');$dent=account('Dentista');$h=browser();$guest=browser();$dh=browser();
$r=web($guest,'pagamento.php');note('Pagamento exige login',$r['status']===302 && str_ends_with($r['redirect'],'/login.php'));
$r=login($h,$admin);note('Login administrador web',$r['status']===302 && str_ends_with($r['redirect'],'/index.php'));
$api=ApiClient::requisitar('POST','api/auth/login',['identificador'=>$admin['login'],'senha'=>$admin['password']]);
session_start();$_SESSION['jwt']=$api['dados']['token'];
$marker='Financeiro '.bin2hex(random_bytes(4));
$p=ApiClient::requisitar('POST','api/pacientes',['nome'=>$marker,'cpf'=>substr((string)hrtime(true),-11)]);$patient=$p['dados']['idPaciente'];
$proc=['btGravar'=>1,'txtIdProcedimento'=>'','txtNomeProcedimento'=>$marker,'txtDescricao'=>'Teste sintetico','txtValorProcedimento'=>'123.45'];
$r=web($h,'controller/ProcedimentoController.php',$proc);note('Criar procedimento web',$r['status']===302 && str_ends_with($r['redirect'],'/procedimento.php'));
$q=$db->prepare('SELECT * FROM procedimento WHERE nomeProcedimento=?');$q->execute([$marker]);$procedure=$q->fetch(PDO::FETCH_ASSOC);$pid=(int)$procedure['idProcedimento'];note('Procedimento preserva centavos',$procedure['valorProcedimento']==='123.45');
$proc['txtIdProcedimento']=$pid;$proc['txtValorProcedimento']='150.75';$r=web($h,'controller/ProcedimentoController.php',$proc);note('Atualizar procedimento',$r['status']===302 && $db->query("SELECT valorProcedimento FROM procedimento WHERE idProcedimento=$pid")->fetchColumn()==='150.75');
$c=ApiClient::requisitar('POST','api/consultas',['dataConsulta'=>'2027-02-10','horarioConsulta'=>'09:00:00','statusConsulta'=>'Agendada','idUsuario'=>$dent['id'],'idPaciente'=>$patient,'idProcedimento'=>$pid]);$cid=$c['dados']['idConsulta'];note('Consulta API usa procedimento criado no PHP',$c['status']===201);
$orc=['btGravar'=>1,'txtIdOrcamento'=>'','txtValorTotal'=>'123.45','txtDataOrcamento'=>'2027-02-10','txtStatusOrcamento'=>'Pendente','txtObservacoes'=>'Teste sintetico','cbPaciente'=>$patient];
$r=web($h,'controller/OrcamentoController.php',$orc);note('Criar orcamento web',$r['status']===302 && str_ends_with($r['redirect'],'/orcamento.php'));
$o=$db->query("SELECT * FROM orcamento WHERE idPaciente=$patient ORDER BY idOrcamento DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);$oid=(int)$o['idOrcamento'];note('Orcamento preserva valor e paciente',$o['valorTotal']==='123.45' && (int)$o['idPaciente']===$patient);
$orc['txtIdOrcamento']=$oid;$orc['txtValorTotal']='150.75';$orc['txtStatusOrcamento']='Aprovado';$r=web($h,'controller/OrcamentoController.php',$orc);note('Atualizar orcamento',$r['status']===302 && $db->query("SELECT valorTotal FROM orcamento WHERE idOrcamento=$oid")->fetchColumn()==='150.75');
$pay=['btGravar'=>1,'txtIdPagamento'=>'','txtValorTotal'=>'123.45','txtFormaPagamento'=>'Pix','txtStatusPagamento'=>'Pago','txtDataPagamento'=>'2027-02-10','cbConsulta'=>$cid];
$r=web($h,'controller/PagamentoController.php',$pay);note('Criar pagamento web',$r['status']===302 && str_ends_with($r['redirect'],'/pagamento.php'));
$pg=$db->query("SELECT * FROM pagamento WHERE idConsulta=$cid ORDER BY idPagamento DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);$pgid=(int)$pg['idPagamento'];note('Pagamento preserva valor e consulta',$pg['valorTotal']==='123.45' && (int)$pg['idConsulta']===$cid);
$pay['txtIdPagamento']=$pgid;$pay['txtValorTotal']='150.75';$r=web($h,'controller/PagamentoController.php',$pay);note('Atualizar pagamento',$r['status']===302 && $db->query("SELECT valorTotal FROM pagamento WHERE idPagamento=$pgid")->fetchColumn()==='150.75');
foreach (['procedimento.php','orcamento.php','pagamento.php',"recibo.php?id=$pgid"] as $page) {$r=web($h,$page);note('Renderizar '.$page,$r['status']===200 && str_contains($r['body'],$marker) && !str_contains($r['body'],'Fatal error'));}
$r=web($h,"recibo.php?id=$pgid");note('Recibo apresenta valor brasileiro',str_contains($r['body'],'150,75'));
$r=web($h,'recibo.php?id=2147483647');note('Recibo inexistente redireciona',$r['status']===302 && str_ends_with($r['redirect'],'/pagamento.php'));
foreach ([['procedimento',$proc,'txtIdProcedimento','txtValorProcedimento'],['orcamento',$orc,'txtIdOrcamento','txtValorTotal'],['pagamento',$pay,'txtIdPagamento','txtValorTotal']] as [$table,$payload,$idField,$amountField]) {
 $payload[$idField]='';$payload[$amountField]='-1.00';$before=countRows($table);$r=web($h,'controller/'.ucfirst($table).'Controller.php',$payload);
 note('Rejeitar valor negativo '.$table,$r['status']===400 && countRows($table)===$before,['http'=>$r['status'],'registrosNovos'=>countRows($table)-$before]);
}
$bad=$pay;$bad['txtIdPagamento']='';$bad['txtValorTotal']='12.345';$before=countRows('pagamento');$r=web($h,'controller/PagamentoController.php',$bad);note('Pagamento rejeita fracao de centavo',$r['status']===400 && countRows('pagamento')===$before,['http'=>$r['status']]);
$bad=$orc;$bad['txtIdOrcamento']='';$bad['txtDataOrcamento']='2027-02-30';$before=countRows('orcamento');$r=web($h,'controller/OrcamentoController.php',$bad);note('Orcamento rejeita data impossivel',$r['status']===400 && countRows('orcamento')===$before,['http'=>$r['status']]);
$bad=$pay;$bad['txtIdPagamento']='';$bad['cbConsulta']=2147483647;$before=countRows('pagamento');$r=web($h,'controller/PagamentoController.php',$bad);note('Pagamento rejeita consulta inexistente sem SQL',$r['status']===400 && countRows('pagamento')===$before && !str_contains($r['body'],'SQLSTATE'),['http'=>$r['status']]);
foreach (['999999999.99','1e3','abc', ['10.00']] as $value) {
 $bad=$pay;$bad['txtIdPagamento']='';$bad['txtValorTotal']=$value;$before=countRows('pagamento');$r=web($h,'controller/PagamentoController.php',$bad);
 note('Pagamento rejeita valor malformado '.json_encode($value),$r['status']===400 && countRows('pagamento')===$before,['http'=>$r['status']]);
}
$bad=$pay;$bad['txtValorTotal']='-1.00';$r=web($h,'controller/PagamentoController.php',$bad);note('Atualizacao invalida preserva valor',$r['status']===400 && $db->query("SELECT valorTotal FROM pagamento WHERE idPagamento=$pgid")->fetchColumn()==='150.75');
$bad=$pay;$bad['txtIdPagamento']='';$bad['txtStatusPagamento']='Invalido';$before=countRows('pagamento');$r=web($h,'controller/PagamentoController.php',$bad);note('Status invalido rejeitado',$r['status']===400 && countRows('pagamento')===$before);
$bad=$pay;$bad['txtIdPagamento']='';$bad['txtDataPagamento']='';$before=countRows('pagamento');$r=web($h,'controller/PagamentoController.php',$bad);note('Data pagamento obrigatoria no servidor',$r['status']===400 && countRows('pagamento')===$before);
$bad=$pay;$bad['txtIdPagamento']='';$bad['txtValorTotal']='99999999.99';$before=countRows('pagamento');$r=web($h,'controller/PagamentoController.php',$bad);note('Limite DECIMAL(10,2) aceito sem arredondar',$r['status']===302 && countRows('pagamento')===$before+1 && $db->query("SELECT valorTotal FROM pagamento WHERE idConsulta=$cid ORDER BY idPagamento DESC LIMIT 1")->fetchColumn()==='99999999.99');
login($dh,$dent);$before=countRows('pagamento');$bad=$pay;$bad['txtIdPagamento']='';$r=web($dh,'controller/PagamentoController.php',$bad);note('Dentista nao cria pagamento',$r['status']===302 && countRows('pagamento')===$before);
if (getenv('ODONTOCARE_VALIDACAO_SESSOES')) {
 $cookies=curl_getinfo($h,CURLINFO_COOKIELIST);$sid=null;
 foreach($cookies as $cookie) {$parts=explode("\t",$cookie);if(($parts[5]??'')==='PHPSESSID')$sid=$parts[6];}
 session_write_close();ini_set('session.save_path',getenv('ODONTOCARE_VALIDACAO_SESSOES'));session_id($sid);session_start();$_SESSION['expiraEmUtc']='2000-01-01T00:00:00Z';session_write_close();
 foreach(["recibo.php?id=$pgid",'index.php','pagamento.php'] as $page){$r=web($h,$page);note('Sessao expirada bloqueia '.$page,$r['status']===302 && str_ends_with($r['redirect'],'/login.php'),['http'=>$r['status']]);}
}
foreach([$h,$guest,$dh] as $ch) curl_close($ch);
$output=['database'=>$db->query('SELECT VERSION()')->fetchColumn(),'results'=>$results];
if(getenv('ODONTOCARE_VALIDACAO_RESULTADO'))file_put_contents(getenv('ODONTOCARE_VALIDACAO_RESULTADO'),json_encode($output,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
foreach($results as $r)echo ($r['passed']?'PASS ':'FAIL ').$r['test'].' '.json_encode($r['detail'],JSON_UNESCAPED_UNICODE).PHP_EOL;
exit(count(array_filter($results,fn($r)=>!$r['passed']))?1:0);
