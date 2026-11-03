<?php
// API e banco exclusivos de teste; leva aproximadamente três minutos.
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
if(getenv('ODONTOCARE_VALIDACAO_ISOLADA')!=='1'){fwrite(STDERR,"Exige ambiente isolado.\n");exit(2);}
foreach(['ODONTOCARE_API_URL','ODONTOCARE_DB_HOST','ODONTOCARE_DB_NAME','ODONTOCARE_DB_USER','ODONTOCARE_DB_PASS','ODONTOCARE_CACHE_LOG'] as $key){if(getenv($key)===false){fwrite(STDERR,"Configure $key.\n");exit(2);}}
require __DIR__.'/../OdontoCare-main/api/ApiClient.php';
$db=new PDO('mysql:host='.getenv('ODONTOCARE_DB_HOST').';dbname='.getenv('ODONTOCARE_DB_NAME').';charset=utf8mb4',getenv('ODONTOCARE_DB_USER'),getenv('ODONTOCARE_DB_PASS'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$results=[];
function note($name,$ok,$details=null){global $results;$results[]=['test'=>$name,'passed'=>$ok,'detail'=>$details];echo ($ok?'PASS ':'FAIL ').$name.PHP_EOL;}
function events(){clearstatcache();$text=file_get_contents(getenv('ODONTOCARE_CACHE_LOG'));preg_match_all('/CACHE (HIT|MISS|INVALIDADO):/',$text,$m);return $m[1];}
function delta($before){usleep(150000);return array_slice(events(),$before);}
function listing(){global $name;$r=ApiClient::requisitar('GET','api/materiais?busca='.urlencode($name));if($r['status']!==200 || $r['dados']['total']!==1)throw new RuntimeException('Listagem de teste falhou.');return $r['dados']['items'][0];}
$login='cache_'.bin2hex(random_bytes(5));$password=bin2hex(random_bytes(16));$salt=random_bytes(16);$hash='pbkdf2-sha256$210000$'.base64_encode($salt).'$'.base64_encode(hash_pbkdf2('sha256',$password,$salt,210000,32,true));
$db->prepare('INSERT INTO usuario(nome,login,senha,tipoUsuario) VALUES(?,?,?,?)')->execute(['Teste cache',$login,$hash,'Administrador']);
$r=ApiClient::requisitar('POST','api/auth/login',['identificador'=>$login,'senha'=>$password]);if($r['status']!==200)throw new RuntimeException('Login de teste falhou.');session_start();$_SESSION['jwt']=$r['dados']['token'];
$name='Cache teste '.bin2hex(random_bytes(4));$body=['nomeMaterial'=>$name,'descricao'=>'Fonte original','estoqueMinimo'=>2,'unidade'=>'un'];
$r=ApiClient::requisitar('POST','api/materiais',$body);if($r['status']!==201)throw new RuntimeException('Criação falhou.');$id=$r['dados']['idMaterial'];
$before=count(events());$item=listing();$log=delta($before);note('Primeira leitura: MISS e dados da fonte',$item['descricao']==='Fonte original' && $log===['MISS'],$log);
$db->prepare('UPDATE material SET descricao=? WHERE idMaterial=?')->execute(['Alteracao direta de teste',$id]);
$before=count(events());$item=listing();$log=delta($before);note('Segunda leitura: HIT e dados preservados em memoria',$item['descricao']==='Fonte original' && $log===['HIT'],$log);
$body['descricao']='Atualizacao pela API';$before=count(events());$r=ApiClient::requisitar('PUT','api/materiais/'.$id,$body);$item=listing();$log=delta($before);note('Atualizacao invalida cache e recarrega a fonte',$r['status']===200 && $item['descricao']==='Atualizacao pela API' && $log===['INVALIDADO','MISS'],$log);
$before=count(events());$r=ApiClient::requisitar('POST',"api/materiais/$id/entradas",['quantidade'=>5]);$item=listing();$log=delta($before);note('Movimentacao invalida cache e atualiza saldo',$r['status']===200 && $item['quantidade']===5 && $log===['INVALIDADO','MISS'],$log);
echo "Aguardando expiração deslizante real de 30 segundos.\n";sleep(32);
$before=count(events());listing();$log=delta($before);note('Expiracao deslizante recarrega a fonte apos inatividade',$log===['MISS'],$log);
// Reaquecer a entrada a cada 20s: impede expiração deslizante, mas não a absoluta.
$start=microtime(true);$hits=[];
foreach([20,40,60,80,100] as $offset){while(microtime(true)-$start<$offset)usleep(250000);$before=count(events());listing();$hits[]=delta($before);echo "Cache mantido em uso: $offset segundos.\n";}
note('Leituras frequentes renovam prazo deslizante',count(array_filter($hits,fn($e)=>$e===['HIT']))===5,$hits);
while(microtime(true)-$start<123)usleep(250000);
$before=count(events());listing();$log=delta($before);note('Expiracao absoluta ocorre mesmo com leituras frequentes',$log===['MISS'],['eventos'=>$log,'segundos'=>round(microtime(true)-$start,1)]);
$output=['database'=>$db->query('SELECT VERSION()')->fetchColumn(),'results'=>$results];if(getenv('ODONTOCARE_VALIDACAO_RESULTADO'))file_put_contents(getenv('ODONTOCARE_VALIDACAO_RESULTADO'),json_encode($output,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));exit(count(array_filter($results,fn($r)=>!$r['passed']))?1:0);
