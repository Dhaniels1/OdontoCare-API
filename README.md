# OdontoCare — como baixar e rodar

Sistema acadêmico de gestão odontológica, com cliente PHP, API C# e MySQL. Inclui pacientes, agenda, usuários, procedimentos, orçamentos, pagamentos, recibos, materiais e equipamentos.

Equipe: Daniel, Bruna, Glaucio e Adla. Conferir nomes completos e matrículas antes da entrega acadêmica.

## 1. Baixar o projeto

O repositório oficial é https://github.com/Dhaniels1/OdontoCare-API. Para baixar pelo Git:

```powershell
git clone https://github.com/Dhaniels1/OdontoCare-API.git
cd OdontoCare-API
```

Também é possível usar **Code > Download ZIP** no GitHub e extrair. Abra a pasta extraída que contém este README e a solução `OdontoCare.slnx`. Se recebeu o pacote `OdontoCare-Entrega-Limpa.zip`, extraia e abra a pasta `OdontoCare`.

Todos os comandos abaixo devem ser executados na raiz do projeto. No Windows, use PowerShell 7. Não é necessário instalar o SDK em uma pasta específica do computador de Daniel.

## 2. Instalar os pré-requisitos

- .NET **SDK 10**, e não somente o Runtime.
- PHP **8.2**, com `curl`, `mbstring`, `pdo_mysql` e sessões habilitados. Para os testes de HTML, também `dom`.
- MySQL **8.0**, iniciado localmente, e um usuário com acesso ao banco.
- Git somente se optar por clonar.

Confira os programas no terminal:

```powershell
dotnet --list-sdks
php --version
php -m
```

Se um comando não for reconhecido, inclua a pasta do programa no PATH e abra outro terminal. No XAMPP, o PHP normalmente fica em `C:\xampp\php`; confira as extensões no `php.ini` dessa instalação.

## 3. Preparar o banco

Use uma instalação de desenvolvimento sem um banco `odontocare` com dados que precisem ser preservados. No MySQL Workbench, abra e execute os arquivos nesta ordem:

1. `database/odontocare.sql`
2. `database/02_migracao_estoque.sql`

Os scripts criam/usam o banco `odontocare`. Não é necessário importar dados de demonstração nem receber senhas de outra pessoa.

## 4. Criar o primeiro administrador

Em um terminal separado, configure os dados da sua instalação. O exemplo de host inclui a porta padrão; ajuste se necessário.

```powershell
$env:ODONTOCARE_DB_HOST = '127.0.0.1;port=3306'
$env:ODONTOCARE_DB_NAME = 'odontocare'
$env:ODONTOCARE_DB_USER = Read-Host 'Usuario do MySQL'
$dbSecret = Read-Host 'Senha do MySQL' -AsSecureString
$env:ODONTOCARE_DB_PASS = [Net.NetworkCredential]::new('', $dbSecret).Password
$env:ODONTOCARE_ADMIN_LOGIN = Read-Host 'Login que deseja criar'
$adminSecret = Read-Host 'Senha do administrador (minimo 12 caracteres)' -AsSecureString
$env:ODONTOCARE_ADMIN_PASSWORD = [Net.NetworkCredential]::new('', $adminSecret).Password
php tools/criar_admin.php
```

Depois da mensagem de sucesso, feche esse terminal. O utilitário recusa uma segunda inicialização quando já existe administrador. Nesse caso, use a conta já criada. Não há senha padrão.

## 5. Iniciar a API

Abra outro terminal na raiz do projeto. Quando solicitado, informe a conexão completa usando os seus dados, neste formato:

`Server=127.0.0.1;Port=3306;Database=odontocare;Uid=SEU_USUARIO;Pwd=SUA_SENHA;`

```powershell
$connectionSecret = Read-Host 'Conexao completa do MySQL' -AsSecureString
$env:ConnectionStrings__OdontoCare = [Net.NetworkCredential]::new('', $connectionSecret).Password
$env:Jwt__Key = [Convert]::ToBase64String([Security.Cryptography.RandomNumberGenerator]::GetBytes(48))
$env:Jwt__Issuer = 'OdontoCare.API'
$env:Jwt__Audience = 'OdontoCare.Cliente'
$env:ASPNETCORE_ENVIRONMENT = 'Development'
dotnet build OdontoCare.slnx
dotnet run --no-build --no-launch-profile --project OdontoCare.API --urls http://127.0.0.1:5240
```

Mantenha o terminal aberto. Ao gerar outra chave e reiniciar a API, faça login novamente. O contrato OpenAPI fica em http://127.0.0.1:5240/openapi/v1.json; não existe Swagger UI embutida.

## 6. Iniciar o cliente

Abra mais um terminal na raiz do projeto:

```powershell
$env:ODONTOCARE_API_URL = 'http://127.0.0.1:5240'
$sessionDir = Join-Path $env:TEMP 'OdontoCare-sessions'
New-Item -ItemType Directory -Path $sessionDir -Force | Out-Null
php -d "session.save_path=$sessionDir" -S 127.0.0.1:58080 -t OdontoCare-main
```

Abra **http://127.0.0.1:58080/login.php** e entre com a conta criada no passo 4. O cliente recebe apenas a URL da API. Para parar os servidores, pressione `Ctrl+C` nos respectivos terminais. Os dados permanecem no MySQL.

## 7. Conferir o funcionamento

1. Cadastre um dentista, um paciente e um procedimento fictícios.
2. Agende uma consulta entre 08:00 e 18:00. Tente repetir o horário para verificar o conflito.
3. Cadastre orçamento e pagamento e abra o recibo.
4. Cadastre material, registre entrada/saída e confira o saldo e o alerta de estoque mínimo.
5. Consulte materiais duas vezes e observe `CACHE MISS` e `CACHE HIT` no terminal da API. Edite um material e confira `CACHE INVALIDADO`.
6. Entre como dentista para conferir as permissões e a agenda do profissional.
7. Confira os formulários, navegação, mensagens e saída do sistema, também em tela pequena.

**Glaucio:** conferir estas instruções em sua máquina e completar a identificação da equipe. **Adla:** executar os fluxos acima e registrar qualquer problema com tela, passos, resultado esperado e resultado recebido.

## 8. Organização e decisões principais

| Pasta | Responsabilidade |
|---|---|
| `OdontoCare.Domain` | Entidades |
| `OdontoCare.Application` | Casos de uso, regras, DTOs, interfaces e ResultWrapper |
| `OdontoCare.Infrastructure` | Banco, repositórios, transações, senhas e cache |
| `OdontoCare.API` | Endpoints, autenticação, autorização e composição das dependências |
| `OdontoCare-main` | Telas PHP, sessão e comunicação HTTP com a API |
| `database` | Scripts de criação e migração |
| `tools` | Preparação e testes executáveis |

O cliente envia o JWT guardado na sessão usando `Authorization: Bearer`. A API verifica assinatura, emissor, público, validade e perfil. O token vale 60 minutos por padrão, com tolerância de 15 segundos; após 401, o cliente remove o token. Senhas usam PBKDF2-SHA256 com salt individual.

As respostas usam `success`, `data`, `message` e `errors`. Exemplo: `{"success":true,"data":[],"message":"Operação realizada com sucesso.","errors":[]}`. Os erros usam códigos 400, 401, 403, 404, 405, 409 e 500 conforme o caso, sem revelar detalhes internos do banco.

Principais rotas, sob `/api`: `auth/login`, `auth/registro`, `usuarios`, `pacientes`, `consultas`, `procedimentos`, `orcamentos`, `pagamentos`, `painel`, `materiais` e `equipamentos`. O login usa POST com `{"identificador":"SEU_LOGIN","senha":"SUA_SENHA"}`. As rotas protegidas exigem Bearer; cadastro de usuário exige administrador. Consulte o OpenAPI para os campos e métodos de cada operação.

O cache da lista de materiais é local à API, com expiração absoluta de dois minutos e deslizante de 30 segundos. Alterações removem a chave do cache. A agenda usa bloqueios transacionais; o estoque mantém saldo e histórico na mesma transação.

## 9. Testes disponíveis

Os scripts de teste foram mantidos por fazerem parte do projeto. Os relatórios e resultados antigos não estão neste pacote. Para conferir as fronteiras das camadas:

```powershell
./tools/verificar_arquitetura.ps1
```

Para testes integrados, prepare outro banco/instância **descartável**, API e cliente apontando para ele. Configure em um terminal as variáveis `ODONTOCARE_DB_HOST`, `ODONTOCARE_DB_NAME`, `ODONTOCARE_DB_USER` e `ODONTOCARE_DB_PASS` como no passo 4, usando esse banco de teste. Configure também:

```powershell
$env:ODONTOCARE_VALIDACAO_ISOLADA = '1'
$env:ODONTOCARE_API_URL = 'http://127.0.0.1:PORTA_API_TESTE'
$env:ODONTOCARE_WEB_URL = 'http://127.0.0.1:PORTA_CLIENTE_TESTE'
./tools/validar_entrega.ps1
```

Substitua as portas antes de executar. As suítes criam registros fictícios e algumas excluem os próprios registros. Não use o banco da apresentação. Os resultados JSON são gerados na raiz e não precisam acompanhar a passagem do projeto.

Para incluir todos os cenários adicionais, configure `ODONTOCARE_CACHE_LOG` com o caminho absoluto do log da API (categoria do cache em Information), `ODONTOCARE_API_EXPIRACAO_URL` com outra API usando `Jwt__ExpirationMinutes=1`, e `ODONTOCARE_API_FALHA_URL` com uma API apontando para um banco indisponível. O teste de cache precisa de acesso exclusivo aos materiais durante a medição. A automação do GitHub verifica compilação e arquitetura; não substitui os testes integrados.

## 10. Alternativa com Docker

Requer Docker Desktop/Engine com contêineres Linux e Compose em execução. Esta alternativa está preparada, mas a execução dos contêineres ainda precisa de homologação.

```powershell
./tools/preparar_docker.ps1
docker compose config --quiet
docker compose up -d --build
docker compose --profile setup run --rm --build setup
```

O preparador cria `.env` e solicita a senha inicial. Login: `admin`. Acesse http://127.0.0.1:58084/login.php; OpenAPI em http://127.0.0.1:5244/openapi/v1.json. O banco é inicializado somente na primeira criação do volume.

Para consultar e parar sem apagar os dados:

```powershell
docker compose ps
docker compose logs --tail 60 api web
docker compose stop
```

Não compartilhe `.env`. Alterar suas senhas não muda automaticamente as credenciais de um banco já inicializado.

## Problemas comuns e limites

- **Falha de conexão:** confira MySQL, porta, usuário, senha e os dois scripts SQL.
- **PHP não encontra driver/função:** confira `php -m` e o `php.ini` carregado por `php --ini`.
- **Login retorna à tela inicial:** confira a pasta de sessões e mantenha a API em execução.
- **401:** faça login novamente; o token pode ter expirado ou a chave ter mudado.
- **403:** confira o perfil e utilize o formulário atual, que inclui proteção CSRF.
- **Porta ocupada:** encerre a instância anterior ou ajuste a porta e a URL correspondente.

O pacote é para execução acadêmica local. A homologação visual, Docker e o ensaio coletivo ainda precisam ser concluídos. O cache ainda merece teste de leitura simultânea com gravação; não há validação de carga ou implantação externa nesta entrega. Esta organização de arquivos não altera as regras do sistema.
