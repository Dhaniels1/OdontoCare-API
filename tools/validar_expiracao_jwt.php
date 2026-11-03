<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
if(getenv('ODONTOCARE_VALIDACAO_ISOLADA')!=='1'){fwrite(STDERR,"Exige ambiente isolado.\n");exit(2);}
foreach(['ODONTOCARE_API_URL','ODONTOCARE_DB_HOST','ODONTOCARE_DB_NAME','ODONTOCARE_DB_USER','ODONTOCARE_DB_PASS'] as $key){if(getenv($key)===false){fwrite(STDERR,"Configure $key.\n");exit(2);}}
require __DIR__.'/../OdontoCare-main/api/ApiClient.php';
$db=new PDO('mysql:host='.getenv('ODONTOCARE_DB_HOST').';dbname='.getenv('ODONTOCARE_DB_NAME').';charset=utf8mb4',getenv('ODONTOCARE_DB_USER'),getenv('ODONTOCARE_DB_PASS'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$login='expiry_'.bin2hex(random_bytes(5));$password=bin2hex(random_bytes(16));$salt=random_bytes(16);$hash='pbkdf2-sha256$210000$'.base64_encode($salt).'$'.base64_encode(hash_pbkdf2('sha256',$password,$salt,210000,32,true));
$db->prepare('INSERT INTO usuario(nome,login,senha,tipoUsuario) VALUES(?,?,?,?)')->execute(['Teste expiracao',$login,$hash,'Administrador']);
$r=ApiClient::requisitar('POST','api/auth/login',['identificador'=>$login,'senha'=>$password]);
if($r['status']!==200){fwrite(STDERR,"Login de teste falhou.\n");exit(1);}
$expires=strtotime($r['dados']['expiraEmUtc']);$wait=$expires-time()+17;
if($wait<1 || $wait>90){fwrite(STDERR,"Configure a API de teste com Jwt:ExpirationMinutes=1.\n");exit(2);}
session_start();$token=$r['dados']['token'];$_SESSION['jwt']=$token;
$before=ApiClient::requisitar('GET','api/pacientes');
echo "Token emitido pela API: status antes da expiracao = ".$before['status']."; aguardando validade e tolerancia.\n";
while(time()<$expires+17)sleep(1);
$_SESSION['jwt']=$token;$after=ApiClient::requisitar('GET','api/pacientes');
$results=[['test'=>'Token autentico valido antes da expiracao','passed'=>$before['status']===200,'actual'=>$before['status']],['test'=>'Mesmo token rejeitado apos expiracao e clock skew','passed'=>$after['status']===401,'actual'=>$after['status']],['test'=>'Cliente PHP limpa token expirado','passed'=>!isset($_SESSION['jwt'])]];
if(getenv('ODONTOCARE_VALIDACAO_RESULTADO'))file_put_contents(getenv('ODONTOCARE_VALIDACAO_RESULTADO'),json_encode(['results'=>$results],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
foreach($results as $result)echo ($result['passed']?'PASS ':'FAIL ').$result['test'].PHP_EOL;
exit(count(array_filter($results,fn($r)=>!$r['passed']))?1:0);
