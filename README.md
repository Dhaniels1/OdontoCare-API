# OdontoCare

Sistema acadêmico de gestão odontológica: pacientes, consultas, usuários, procedimentos, orçamentos, pagamentos, recibos, materiais e equipamentos. A aplicação PHP consome uma API REST em C#; o acesso aos dados fica no servidor da API.

## Equipe

- Bruna: estrutura inicial, pacientes e consultas; integração compartilhada.
- Daniel: arquitetura, segurança, ResultWrapper, estoque, cache e integração.
- Glaucio: documentação e README final.
- Adla: testes, checklist e demonstração.

Os nomes completos e identificadores acadêmicos devem ser conferidos pelo grupo antes da submissão.

## Comece aqui

- [Passagem para o próximo integrante](PASSAGEM_PARA_EQUIPE.md).
- [Execução nativa e demonstração](EXECUCAO_E_DEMONSTRACAO.md).
- [Execução com Docker e limite de validação](DOCKER.md).
- [Checklist acadêmico atualizado](CHECKLIST_ACADEMICO_DANIEL.md).
- [Relatório da entrega](ENTREGA_DANIEL.md) e [histórico de validação](VALIDACAO_LOCAL_2026-11-02.md).

## Tecnologias e arquitetura

.NET 10 / ASP.NET Core, C#, Entity Framework Core com MySql.EntityFrameworkCore, MySQL 8 (validação final), PHP 8.2, cURL, Bootstrap e Chart.js. MariaDB foi usado nas verificações anteriores; a rodada final desta entrega foi executada com MySQL 8.0.42.

```text
Cliente PHP -- HTTP + Bearer JWT --> API --> Application --> Domain
                                      |          ^
                                      +--> Infrastructure
                                           banco / cache / hash
```

- `OdontoCare.Domain`: entidades independentes de banco e framework web.
- `OdontoCare.Application`: casos de uso, DTOs, validações, contratos de repositórios, ResultWrapper e regras de agenda/financeiro/usuários.
- `OdontoCare.Infrastructure`: EF, mapeamentos, repositórios, projeções de leitura, transações/bloqueios, hash e cache.
- `OdontoCare.API`: endpoints, autenticação, autorização, conversão HTTP, erros globais e composição das dependências.
- `OdontoCare-main`: apresentação PHP, sessão, CSRF e cliente HTTP. Não recebe credenciais do banco.
- `tools`: utilitários CLI de configuração e testes. Somente esses utilitários precisam de PDO MySQL.

As regras da agenda usam contratos de persistência. O bloqueio transacional de paciente/profissional permanece em Infrastructure para garantir atomicidade. Consultas de projeção (`ConsultaLookupService`) ficam em Infrastructure porque apenas recuperam dados; regras de negócio ficam em Application. Estoque preserva saldo e histórico em uma mesma transação.

## Execução nativa

Pré-requisitos: .NET SDK 10, PHP 8.2 com cURL/mbstring e MySQL 8. Para preparar administrador ou executar testes CLI, habilitar também PDO MySQL; testes HTML usam DOM.

1. Criar uma instância de banco acadêmico vazia. Importar `database/odontocare.sql` e depois `database/02_migracao_estoque.sql` pelo cliente MySQL. Os scripts usam o banco `odontocare` e não inserem pessoas reais.
2. Configurar a API por variáveis de ambiente. Não colocar segredos em `appsettings.json`:

```powershell
$connectionSecret = Read-Host 'Connection string MySQL' -AsSecureString
$env:ConnectionStrings__OdontoCare = [Net.NetworkCredential]::new('', $connectionSecret).Password
$env:Jwt__Key = [Convert]::ToBase64String([Security.Cryptography.RandomNumberGenerator]::GetBytes(48))
$env:Jwt__Issuer = 'OdontoCare.API'
$env:Jwt__Audience = 'OdontoCare.Cliente'
$env:ASPNETCORE_ENVIRONMENT = 'Development'
dotnet build OdontoCare.slnx
dotnet run --no-build --no-launch-profile --project OdontoCare.API --urls http://127.0.0.1:5240
```

Formato da conexão: `Server=HOST;Port=PORTA;Database=odontocare;Uid=USUARIO;Pwd=SENHA;`. Use os valores locais no prompt. As configurações `Clinica__HorarioInicio` e `Clinica__HorarioFim` têm padrão 08:00 e 18:00.

3. Criar o primeiro administrador pelo utilitário CLI conforme a seção de primeiro acesso do [roteiro](EXECUCAO_E_DEMONSTRACAO.md). Ele usa PBKDF2 e recusa nova inicialização se já houver administrador. Não existe senha padrão no código.
4. Em outro terminal, iniciar o cliente:

```powershell
$env:ODONTOCARE_API_URL = 'http://127.0.0.1:5240'
php -S 127.0.0.1:58080 -t OdontoCare-main
```

Abrir http://127.0.0.1:58080/login.php. No ambiente Development, o contrato OpenAPI está em http://127.0.0.1:5240/openapi/v1.json (sem Swagger UI embutida). O HTTP é destinado à demonstração local.

## JWT e perfis

`POST /api/auth/login` recebe identificador e senha. Retorna token, expiração UTC e dados públicos do usuário. A senha é armazenada como PBKDF2-SHA256 com 210.000 iterações e salt individual; não retorna nas consultas.

O token tem emissor, público, assinatura, validade e perfil verificados. Validade padrão: 60 minutos; tolerância de relógio: 15 segundos. O PHP guarda JWT na sessão do servidor e envia `Authorization: Bearer <token>`. Ao expirar, solicita novo login; não há refresh token.

Administrador gerencia contas; Administrador/Secretária realizam operações de gestão; Dentista consulta e acessa apenas sua agenda. Materiais/equipamentos só podem ser excluídos pelo Administrador. O painel do dentista limita a agenda e omite os totais financeiros globais. As consultas de pagamentos/orçamentos/recibos mantêm leitura para os três perfis, conforme comportamento acadêmico existente; escrita exige gestão.

## Endpoints principais

Todos os caminhos abaixo começam com `/api`; exceto login, exigem JWT.

| Caminho | Métodos / comportamento |
|---|---|
| `/auth/login` | POST: autenticar |
| `/auth/registro` | POST: cadastrar usuário (Administrador) |
| `/usuarios`, `/usuarios/{id}` | GET; PUT/DELETE por Administrador |
| `/pacientes`, `/pacientes/{id}` | GET/POST/PUT/DELETE |
| `/consultas`, `/consultas/{id}` | GET/POST/PUT/DELETE; DELETE cancela sem apagar |
| `/consultas/{id}/cancelar` | PATCH: cancelamento lógico |
| `/procedimentos`, `/procedimentos/{id}` | GET/POST/PUT/DELETE |
| `/orcamentos`, `/orcamentos/{id}` | GET/POST/PUT/DELETE |
| `/pagamentos`, `/pagamentos/{id}` | GET/POST/PUT/DELETE; lista aceita `consultaId` |
| `/pagamentos/{id}/recibo` | GET: dados do recibo |
| `/painel` | GET: indicadores, alertas e próximas consultas |
| `/materiais`, `/materiais/{id}` | GET/POST/PUT/DELETE; listagem paginada |
| `/materiais/{id}/entradas`, `/materiais/{id}/saidas` | POST: movimentar saldo |
| `/materiais/{id}/movimentacoes` | GET: histórico |
| `/equipamentos`, `/equipamentos/{id}` | GET/POST/PUT/DELETE |

## Requisições e respostas

Login (valores ilustrativos; substitua pela conta criada localmente):

```http
POST /api/auth/login
Content-Type: application/json

{"identificador":"SEU_LOGIN","senha":"SUA_SENHA_LOCAL"}
```

Consulta protegida:

```http
GET /api/procedimentos
Authorization: Bearer <token retornado no login>
```

Resposta ilustrativa:

```json
{"success":true,"data":[{"idProcedimento":1,"nomeProcedimento":"Avaliação","descricao":"Avaliação inicial","valorProcedimento":80.00}],"message":"Operação realizada com sucesso.","errors":[]}
```

Inclusão protegida:

```http
POST /api/procedimentos
Authorization: Bearer <token>
Content-Type: application/json

{"nomeProcedimento":"Avaliação","descricao":"Avaliação inicial","valorProcedimento":80.00}
```

A inclusão responde 201 e `Location`; leitura/edição/exclusão respondem 200 com ResultWrapper. Um conflito de agenda responde 409:

```json
{"success":false,"data":null,"message":"Paciente ou profissional já possui consulta neste horário.","errors":[]}
```

Outros códigos: 400 para validação, 401 sem autenticação válida, 403 sem permissão, 404 inexistente, 405 método não permitido e 500 erro interno genérico. O tratamento global não divulga detalhes de banco ao cliente. Financeiro valida faixa DECIMAL(10,2), centavos, datas, opções e vínculos também na API.

## Cache

A listagem real de materiais usa `IMemoryCache`: expiração absoluta de dois minutos e deslizante de 30 segundos. O cache armazena a lista de materiais por versão; filtros e paginação são aplicados depois da leitura. Gravações/movimentações invalidam a versão. Logs mostram `CACHE MISS`, `CACHE HIT` e `CACHE INVALIDADO`.

Demonstração: consultar materiais duas vezes, editar/movimentar um material e consultar novamente. Os dados devem vir da fonte, depois da memória e novamente da fonte após invalidação. `tools/validar_cache.php` verifica também os dois prazos reais. O cache é local a uma instância; distribuição por múltiplas réplicas não faz parte desta entrega.

## Testes e reprodução

As suítes exigem `ODONTOCARE_VALIDACAO_ISOLADA=1`, URL da API e conexão com banco descartável. Testes web exigem a URL do cliente. Elas criam dados fictícios, e algumas excluem seus próprios registros. Não apontar para a base da apresentação ou dados reais. Configuração e resultados: [relatório de entrega](ENTREGA_DANIEL.md).

```powershell
./tools/verificar_arquitetura.ps1
```

O workflow do GitHub compila e verifica as fronteiras de camadas após uma publicação autorizada. Não equivale às suítes de integração locais.

## Limites da entrega

Docker foi preparado, mas ainda precisa de execução em máquina com Docker. A homologação visual e o ensaio da equipe continuam necessários. As listas financeiras/painel são adequadas à base acadêmica pequena; paginação e agregações otimizadas podem ser acrescentadas se o volume crescer. O novo layout “Agenda em destaque” ficou como melhoria opcional, sem bloquear a entrega funcional.
