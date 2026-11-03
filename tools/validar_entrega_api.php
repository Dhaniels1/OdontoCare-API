<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
if(getenv('ODONTOCARE_VALIDACAO_ISOLADA')!=='1'){fwrite(STDERR,"Exige ambiente isolado.\n");exit(2);}
foreach(['ODONTOCARE_API_URL','ODONTOCARE_DB_HOST','ODONTOCARE_DB_NAME','ODONTOCARE_DB_USER','ODONTOCARE_DB_PASS'] as $key){if(getenv($key)===false){fwrite(STDERR,"Configure $key.\n");exit(2);}}
$base=rtrim(getenv('ODONTOCARE_API_URL'),'/');$db=new PDO('mysql:host='.getenv('ODONTOCARE_DB_HOST').';dbname='.getenv('ODONTOCARE_DB_NAME').';charset=utf8mb4',getenv('ODONTOCARE_DB_USER'),getenv('ODONTOCARE_DB_PASS'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);$results=[];
function note($name,$ok,$detail=null){global $results;$results[]=['test'=>$name,'passed'=>$ok,'detail'=>$detail];echo ($ok?'PASS ':'FAIL ').$name.PHP_EOL;}
function raw($method,$path,$body=null,$token=null,$otherBase=null){global $base;$h=curl_init(($otherBase??$base).'/'.$path);$headers=['Accept: application/json'];if($token)$headers[]='Authorization: Bearer '.$token;if($body!==null)$headers[]='Content-Type: application/json';curl_setopt_array($h,[CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>15,CURLOPT_HTTPHEADER=>$headers]);if($body!==null)curl_setopt($h,CURLOPT_POSTFIELDS,json_encode($body));$text=curl_exec($h);$code=curl_getinfo($h,CURLINFO_HTTP_CODE);$type=curl_getinfo($h,CURLINFO_CONTENT_TYPE);curl_close($h);return ['code'=>$code,'json'=>is_string($text)?json_decode($text,true):null,'type'=>$type??'','raw'=>$text?:''];}
function wrapped($name,$response,$expected){$j=$response['json'];$valid=is_array($j)&&isset($j['success'],$j['message'],$j['errors'])&&array_key_exists('data',$j)&&is_bool($j['success'])&&is_string($j['message'])&&is_array($j['errors'])&&$j['success']===($expected<300)&&str_starts_with($response['type'],'application/json');note($name,$response['code']===$expected&&$valid,['expected'=>$expected,'actual'=>$response['code']]);return $j['data']??null;}

function account($role) {global $db; $login='entrega_'.bin2hex(random_bytes(6));$password=bin2hex(random_bytes(16));$salt=random_bytes(16);$hash='pbkdf2-sha256$210000$'.base64_encode($salt).'$'.base64_encode(hash_pbkdf2('sha256',$password,$salt,210000,32,true));$db->prepare('INSERT INTO usuario(nome,login,senha,tipoUsuario) VALUES(?,?,?,?)')->execute(['Entrega '.$role,$login,$hash,$role]);$id=(int)$db->lastInsertId();$auth=raw('POST','api/auth/login',['identificador'=>$login,'senha'=>$password]);if($auth['code']!==200)throw new RuntimeException('Login falhou');return [$id,$auth['json']['data']['token']];}
[$aid,$admin]=account('Administrador');[$sid,$secretary]=account('Secretária');[$did,$dentist]=account('Dentista');
$marker='Entrega '.bin2hex(random_bytes(4));
$p=wrapped('Paciente de teste',raw('POST','api/pacientes',['nome'=>$marker,'cpf'=>substr((string)hrtime(true),-11)],$admin),201);
$procedure=['nomeProcedimento'=>$marker,'descricao'=>'Somente teste','valorProcedimento'=>12.34];
$proc=wrapped('Secretaria cria procedimento',raw('POST','api/procedimentos',$procedure,$secretary),201);$pid=$proc['idProcedimento'];
$c=wrapped('Consulta de teste',raw('POST','api/consultas',['dataConsulta'=>'2027-04-05','horarioConsulta'=>'09:00:00','statusConsulta'=>'Agendada','idUsuario'=>$did,'idPaciente'=>$p['idPaciente'],'idProcedimento'=>$pid],$admin),201);
$cases=[['procedimentos','idProcedimento','valorProcedimento',$procedure],['orcamentos','idOrcamento','valorTotal',['valorTotal'=>12.34,'dataOrcamento'=>'2027-04-05','statusOrcamento'=>'Pendente','observacoes'=>$marker,'idPaciente'=>$p['idPaciente']]],['pagamentos','idPagamento','valorTotal',['valorTotal'=>12.34,'dataPagamento'=>'2027-04-05','statusPagamento'=>'Pago','formaPagamento'=>'Pix','idConsulta'=>$c['idConsulta']]]];
foreach($cases as [$route,$idKey,$money,$body]) {
 wrapped("$route exige JWT",raw('GET','api/'.$route),401);
 wrapped("Dentista nao grava $route",raw('POST','api/'.$route,$body,$dentist),403);
 $item=wrapped("Criar $route",raw('POST','api/'.$route,$body,$secretary),201);$id=$item[$idKey];
 $got=wrapped("Buscar $route",raw('GET',"api/$route/$id",null,$admin),200);note("Centavos preservados $route",abs($got[$money]-12.34)<0.00001);
 wrapped("Listar $route",raw('GET',"api/$route",null,$secretary),200);
 foreach([-1,12.345,100000000] as $invalid){$bad=$body;$bad[$money]=$invalid;wrapped("Valor invalido $route: $invalid",raw('PUT',"api/$route/$id",$bad,$admin),400);}
 $bad=$body;unset($bad[$money]);wrapped("Valor ausente $route",raw('POST',"api/$route",$bad,$admin),400);
 $got=raw('GET',"api/$route/$id",null,$admin);note("Rejeicao nao altera $route",abs($got['json']['data'][$money]-12.34)<0.00001);
 $body[$money]=99.99;wrapped("Atualizar $route",raw('PUT',"api/$route/$id",$body,$admin),200);
 wrapped("Dentista nao exclui $route",raw('DELETE',"api/$route/$id",null,$dentist),403);
 if($route==='pagamentos'){$receipt=wrapped('Recibo pela API',raw('GET',"api/pagamentos/$id/recibo",null,$admin),200);note('Recibo identifica paciente e valor',($receipt['cpf']??'')===$p['cpf']&&$receipt['pagamento']['valorTotal']===99.99);}
 wrapped("Excluir $route",raw('DELETE',"api/$route/$id",null,$secretary),200);
 wrapped("Excluido retorna 404 $route",raw('GET',"api/$route/$id",null,$admin),404);
 wrapped("Atualizar inexistente $route",raw('PUT',"api/$route/$id",$body,$admin),404);
}
wrapped('Procedimento em uso preservado',raw('DELETE','api/procedimentos/'.$pid,null,$admin),409);
$bad=$cases[1][3];$bad['idPaciente']=2147483647;wrapped('Paciente inexistente no orcamento',raw('POST','api/orcamentos',$bad,$admin),400);
$bad=$cases[2][3];$bad['idConsulta']=2147483647;wrapped('Consulta inexistente no pagamento',raw('POST','api/pagamentos',$bad,$admin),400);
$bad=$cases[2][3];$bad['statusPagamento']='Invalido';wrapped('Status invalido na API',raw('POST','api/pagamentos',$bad,$admin),400);
$bad=$cases[2][3];$bad['dataPagamento']='2027-02-30';wrapped('Data impossivel na API',raw('POST','api/pagamentos',$bad,$admin),400);
$panel=wrapped('Painel administrador',raw('GET','api/painel',null,$admin),200);note('Painel tem contadores',isset($panel['totalPacientes'],$panel['totalConsultas'],$panel['valorRecebido']));
$panel=wrapped('Painel dentista',raw('GET','api/painel',null,$dentist),200);note('Painel dentista isola agenda e financeiro',$panel['totalConsultas']===1&&$panel['totalPagamentos']===0&&$panel['valorRecebido']==0&&count(array_filter($panel['proximasConsultas'],fn($x)=>$x['idUsuario']!==$did))===0);
$m=['nomeMaterial'=>$marker,'descricao'=>'Teste de limite','estoqueMinimo'=>2,'unidade'=>'un'];$material=wrapped('Criar material vazio',raw('POST','api/materiais',$m,$admin),201);$mid=$material['idMaterial'];
$m['descricao']='Atualizado';wrapped('Atualizar material',raw('PUT',"api/materiais/$mid",$m,$secretary),200);
$material=wrapped('Entrada acima do minimo',raw('POST',"api/materiais/$mid/entradas",['quantidade'=>3],$secretary),200);note('Acima do minimo sem alerta',!$material['estoqueBaixo']);
$material=wrapped('Saida ate o minimo',raw('POST',"api/materiais/$mid/saidas",['quantidade'=>1],$secretary),200);note('No minimo gera alerta',$material['estoqueBaixo']);
wrapped('Secretaria nao exclui material',raw('DELETE',"api/materiais/$mid",null,$secretary),403);
$empty=wrapped('Outro material vazio',raw('POST','api/materiais',$m,$admin),201)['idMaterial'];wrapped('Excluir material sem historico',raw('DELETE',"api/materiais/$empty",null,$admin),200);wrapped('Material excluido 404',raw('GET',"api/materiais/$empty",null,$admin),404);
$out=['database'=>$db->query('SELECT VERSION()')->fetchColumn(),'results'=>$results];if(getenv('ODONTOCARE_VALIDACAO_RESULTADO'))file_put_contents(getenv('ODONTOCARE_VALIDACAO_RESULTADO'),json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));exit(count(array_filter($results,fn($r)=>!$r['passed']))?1:0);
