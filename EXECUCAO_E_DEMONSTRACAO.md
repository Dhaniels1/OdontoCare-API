# OdontoCare — execução local e demonstração

Este roteiro reúne a configuração do ambiente, os testes disponíveis e uma sequência para apresentar o projeto. Use um banco de demonstração com dados fictícios.

## 1. Preparar o ambiente

Requisitos validados nesta máquina:

- SDK .NET 10.0.403.
- PHP 8.2.12 com curl, PDO MySQL, mbstring e sessões. Os testes de HTML também usam DOM.
- MySQL 8.0.42 ou MariaDB 10.4.32 com tabelas InnoDB.

Na raiz do repositório, conferir:

```powershell
dotnet --list-sdks
php --version
php -m
```

Nesta máquina, o SDK foi instalado apenas na pasta de validação, sem alterar a instalação global. Para usá-lo a partir da raiz do clone:

```powershell
$dotnetExe = (Resolve-Path ..\.validation\dotnet\dotnet.exe).Path
& $dotnetExe --list-sdks
```

Em outra máquina com o SDK instalado normalmente, usar `$dotnetExe = 'dotnet'`.

## 2. Banco

Criar uma instância de demonstração separada e importar nesta ordem:

1. `database/odontocare.sql`.
2. `database/02_migracao_estoque.sql`.

Os scripts usam o nome `odontocare`. A separação dos testes foi feita por instância e porta, sem reutilizar um banco existente com esse nome. Não aplicar o roteiro de teste em um banco com dados reais.

Nesta máquina, a instância MySQL de teste já está preparada na pasta `..\.validation\mysql8`. Para iniciá-la, abrir um terminal na raiz do clone:

```powershell
$testData = (Resolve-Path ..\.validation\mysql8).Path
& 'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqld.exe' --no-defaults --basedir='C:\Program Files\MySQL\MySQL Server 8.0' --datadir="$testData" --port=33318 --bind-address=127.0.0.1 --mysqlx=OFF --console
```

Essa pasta contém registros sintéticos dos testes. Para uma apresentação com números limpos, preparar outra instância vazia e importar o esquema. Não apagar ou reinicializar uma pasta existente.

## 3. Iniciar a API

Em outro terminal PowerShell, na raiz do clone, configurar a conexão e uma chave JWT temporária. O exemplo de formato da conexão é `Server=127.0.0.1;Port=33318;Database=odontocare;User=USUARIO;Password=SENHA;`. Informar os valores locais no prompt, sem gravá-los no código.

```powershell
$connectionSecret = Read-Host 'Conexão completa do banco de demonstração' -AsSecureString
$env:ConnectionStrings__OdontoCare = [Net.NetworkCredential]::new('', $connectionSecret).Password
$env:Jwt__Key = [Convert]::ToBase64String([Security.Cryptography.RandomNumberGenerator]::GetBytes(48))
$env:Jwt__Issuer = 'OdontoCare.API'
$env:Jwt__Audience = 'OdontoCare.Cliente'
$env:ASPNETCORE_ENVIRONMENT = 'Development'
$env:Clinica__HorarioInicio = '08:00'
$env:Clinica__HorarioFim = '18:00'
$dotnetExe = (Resolve-Path ..\.validation\dotnet\dotnet.exe).Path
& $dotnetExe build OdontoCare.slnx
& $dotnetExe run --no-build --no-launch-profile --project OdontoCare.API --urls http://127.0.0.1:5240
```

Se usar o SDK global, substituir a definição de `$dotnetExe` por `'dotnet'`. O OpenAPI fica em `http://127.0.0.1:5240/openapi/v1.json` no ambiente Development. Não há Swagger UI embutida.

O endereço HTTP deste roteiro serve para a execução local. A configuração de publicação com HTTPS não foi homologada nesta validação.

## 4. Iniciar o PHP

Em um terceiro terminal, na raiz do clone:

```powershell
$env:ODONTOCARE_API_URL = 'http://127.0.0.1:5240'
$sessionDir = Join-Path $env:TEMP 'OdontoCare-demo-sessions'
New-Item -ItemType Directory -Path $sessionDir -Force | Out-Null
php -d "session.save_path=$sessionDir" -S 127.0.0.1:58080 -t OdontoCare-main
```

Abrir `http://127.0.0.1:58080/login.php`. O servidor PHP recebe somente a URL da API; não precisa de credenciais de banco.

Se o banco novo ainda não tiver administrador, configurar as variáveis de banco abaixo apenas no terminal do utilitário CLI e executar, antes de iniciar o servidor PHP:

```powershell
$env:ODONTOCARE_DB_HOST = Read-Host 'Host e porta, por exemplo 127.0.0.1;port=33318'
$env:ODONTOCARE_DB_NAME = 'odontocare'
$env:ODONTOCARE_DB_USER = Read-Host 'Usuário do banco'
$dbSecret = Read-Host 'Senha do banco' -AsSecureString
$env:ODONTOCARE_DB_PASS = [Net.NetworkCredential]::new('', $dbSecret).Password
$env:ODONTOCARE_ADMIN_LOGIN = Read-Host 'Login do primeiro administrador'
$adminSecret = Read-Host 'Senha do administrador, no mínimo 12 caracteres' -AsSecureString
$env:ODONTOCARE_ADMIN_PASSWORD = [Net.NetworkCredential]::new('', $adminSecret).Password
php tools/criar_admin.php
Remove-Item Env:ODONTOCARE_ADMIN_PASSWORD
```

O utilitário recusa a criação se já existir administrador. Ele não redefine senhas existentes.

## 5. Repetir os testes

Os executores em `tools/validar_*.php` exigem `ODONTOCARE_VALIDACAO_ISOLADA=1` e as variáveis de banco/API. Essa marcação deve ser usada somente em ambiente descartável. Eles criam dados fictícios. O executor de segurança também exclui alguns dos próprios registros de teste para verificar os formulários; ele não deve ser apontado para um banco real.

| Executor | O que verifica |
|---|---|
| `validar_entrega_api.php` | Novos endpoints financeiros, recibo, painel, validação e estoque |
| `validar_aceite_daniel.php` | Perfis, senhas e contratos HTTP |
| `validar_cache.php` | Fonte, memória, invalidação e expiração real |
| `validar_integracao_estendida.php` | Estoque, equipamentos, permissões e agenda |
| `validar_financeiro.php` | Formulários financeiros, valores e recibos |
| `validar_seguranca_web.php` | CSRF, GET sem efeitos, escape de HTML, permissões e operações POST |
| `validar_concorrencia_agenda.php` | Criação, edição e cancelamento simultâneos |
| `validar_expiracao_jwt.php` | Token real antes e depois de vencer |

Definir `ODONTOCARE_WEB_URL=http://127.0.0.1:58080` para os testes web. `ODONTOCARE_VALIDACAO_RESULTADO` pode indicar um arquivo JSON de saída. Para o teste de expiração, usar uma API separada com `Jwt__ExpirationMinutes=1`; ele aguarda o vencimento e a tolerância de 15 segundos, sem alterar o relógio do computador.

Os testes opcionais de sessão estão descritos no relatório. A opção histórica de revogar permissões do usuário PHP no banco não se aplica mais: o cliente não acessa o banco. As respostas automatizadas não substituem uma conferência visual das telas.

## 6. Roteiro de demonstração

1. Entrar como administrador e apresentar os cadastros e o painel.
2. Criar um dentista e um paciente fictício. Mostrar a edição sem revelar senhas.
3. Cadastrar um procedimento com valor em centavos e agendar uma consulta no expediente.
4. Tentar repetir o horário do paciente ou profissional e mostrar o conflito.
5. Criar um orçamento, registrar um pagamento e abrir o recibo. Tentar um valor negativo para mostrar a validação.
6. Cadastrar um material, registrar entrada e saída e conferir saldo e alerta de estoque baixo.
7. Cancelar uma consulta e mostrar que o histórico continua disponível.
8. Entrar como dentista e demonstrar que operações de gestão são bloqueadas.
9. Apresentar o relatório, as evidências JSON e o resultado dos testes de concorrência.
10. Sair pelo botão do sistema e encerrar os serviços locais.

Os formulários agora usam tokens de sessão. Requisições copiadas sem o token retornam 403, e os antigos links de exclusão não executam alterações via GET.

## 7. Preparar a entrega

- Conferir visualmente formulários, gráficos, recibo e navegação em navegador.
- Revisar o diff e os resultados de `VALIDACAO_LOCAL_2026-11-02.md`.
- Não incluir pastas de banco, sessões, credenciais ou tokens na entrega.
- Separar as correções de agenda, validação financeira, segurança web e documentação em commits revisáveis. Os commits e o push dependem de confirmação do responsável.

Todos os fluxos do cliente utilizam a API; PDO fica restrito às ferramentas CLI. A validação local não equivale a uma liberação para uso com pacientes reais ou publicação em produção.
