# OdontoCare API

Sistema de gestão para consultório odontológico. Na primeira versão (Feita em Engenharia de Software I) o aplicativo PHP conversava direto com o banco de dados. Nesta versão (Engenharia de Software II) toda a regra de negócio e o acesso aos dados foram para uma **API REST em ASP.NET Core (.NET 10)**, e o aplicativo PHP virou um **cliente** que consome essa API por HTTP.

> Trabalho prático da disciplina de Engenharia de Software II, IF Sudeste MG, Campus Muriaé, 2026.
> Repositório oficial: https://github.com/Dhaniels1/OdontoCare-API

## Sumário

1. [Integrantes](#1-integrantes)
2. [Descrição do sistema](#2-descrição-do-sistema)
3. [Tecnologias utilizadas](#3-tecnologias-utilizadas)
4. [Arquitetura](#4-arquitetura)
5. [Banco de dados](#5-banco-de-dados)
6. [Como executar a API](#6-como-executar-a-api)
7. [Como executar o aplicativo (cliente PHP)](#7-como-executar-o-aplicativo-cliente-php)
8. [Padronização das respostas (ResultWrapper)](#8-padronização-das-respostas-resultwrapper)
9. [Autenticação e autorização (JWT)](#9-autenticação-e-autorização-jwt)
10. [Estratégia de cache](#10-estratégia-de-cache)
11. [Principais endpoints](#11-principais-endpoints)
12. [Exemplos de requisição e resposta](#12-exemplos-de-requisição-e-resposta)
13. [Testes e validações](#13-testes-e-validações)
14. [Alternativa com Docker](#14-alternativa-com-docker)
15. [Reprodução do projeto do zero](#15-reprodução-do-projeto-do-zero)
16. [Problemas comuns e limites conhecidos](#16-problemas-comuns-e-limites-conhecidos)
17. [Divisão de responsabilidades](#17-divisão-de-responsabilidades)

---

## 1. Integrantes

| Nome completo | 
|---|---|

| Adla Schinaider Ferreira |

| Bruna Aparecida Oliveira da Silva |

| Daniel da Silva Ribeiro de Castro |

| Glauco Roberto Berbert Souza Costa |

## 2. Descrição do sistema

O OdontoCare ajuda a organizar a rotina administrativa e clínica de um consultório odontológico:

- **Pacientes** com dados cadastrais e prontuário (histórico odontológico, alergias, exames e tratamentos anteriores);
- **Consultas** com controle de conflito de horário (o mesmo paciente ou o mesmo profissional não pode ter duas consultas no mesmo dia e horário) e respeito ao horário de funcionamento da clínica;
- **Procedimentos**, **orçamentos** e **pagamentos** (com recibo);
- **Materiais** e **equipamentos**, com entrada e saída de estoque e alerta de estoque mínimo;
- **Painel** com os números gerais da clínica;
- **Controle de acesso por perfil**: Administrador, Secretária e Dentista.

O fluxo geral é este:

```
 Navegador ──▶ Aplicativo cliente (PHP) ──HTTP + JWT──▶ API (ASP.NET Core) ──▶ MySQL
```

O PHP não acessa mais o banco. Ele só monta a tela e chama a API; quem decide o que pode ou não acontecer é a API.

## 3. Tecnologias utilizadas

**API**

| Item | Tecnologia |
|---|---|
| Linguagem e plataforma | C# com ASP.NET Core Web API, **.NET 10** |
| Persistência | Entity Framework Core com **MySQL 8.0** (`MySql.EntityFrameworkCore`) |
| Autenticação | **JWT Bearer** (`Microsoft.AspNetCore.Authentication.JwtBearer`) |
| Hash de senha | **PBKDF2-SHA256** com salt individual por usuário |
| Cache | `IMemoryCache` (cache em memória da própria API) |
| Contrato da API | OpenAPI (`Microsoft.AspNetCore.OpenApi`), JSON em `/openapi/v1.json` |
| Integração contínua | GitHub Actions (compila e confere as fronteiras entre as camadas) |

**Aplicativo cliente**

- PHP 8.2 (cURL para falar com a API), HTML5, CSS3 e Bootstrap 5.

**Outros**

- Git e GitHub; Docker e Docker Compose (alternativa de execução, veja a seção 14).

## 4. Arquitetura

A API segue **Clean Architecture**. A ideia central é a regra de dependência: as camadas de dentro não conhecem as de fora. O domínio não sabe que existe banco, HTTP ou JWT.

```
              ┌───────────────────────────────────────────┐
              │  OdontoCare.API                           │  Controllers, JWT,
              │  (porta de entrada HTTP)                  │  middleware de erros
              └───────────────┬───────────────────────────┘
                              │ usa
              ┌───────────────▼───────────────────────────┐
              │  OdontoCare.Application                   │  Serviços, DTOs, regras,
              │  (casos de uso)                           │  interfaces, ResultWrapper
              └───────────────┬───────────────────────────┘
                              │ usa
              ┌───────────────▼───────────────────────────┐
              │  OdontoCare.Domain                        │  Entidades
              └───────────────────────────────────────────┘
                              ▲
                              │ implementa as interfaces da Application
              ┌───────────────┴───────────────────────────┐
              │  OdontoCare.Infrastructure                │  EF Core, repositórios,
              │  (detalhes técnicos)                      │  cache, hash de senha
              └───────────────────────────────────────────┘
```

| Camada / pasta | Responsabilidade |
|---|---|
| `OdontoCare.Domain` | Entidades do negócio (Paciente, Consulta, Procedimento, Orçamento, Pagamento, Usuário, Material, Equipamento, MovimentacaoEstoque). Não depende de nenhuma outra camada. |
| `OdontoCare.Application` | Casos de uso (`Services`), DTOs com as validações, interfaces (`IEntityRepository<T>`, `IMaterialCache`, `ITokenService`, `IPasswordService`...), perfis de acesso (`Roles`) e o `ResultWrapper`. Não conhece EF Core nem ASP.NET. |
| `OdontoCare.Infrastructure` | Implementações das interfaces: `OdontoCareDbContext` (EF Core), `EntityRepository<T>`, controle de transação da agenda, `MaterialMemoryCache` e `Pbkdf2PasswordService`. |
| `OdontoCare.API` | Controllers, configuração da injeção de dependências (`Extensions/DependencyInjection.cs`), JWT (`Security/JwtTokenService.cs`) e o tratamento global de erros (`Middleware/ExceptionMiddleware.cs`). |
| `OdontoCare-main` | Aplicativo cliente em PHP (telas, sessão e `api/ApiClient.php`, que faz as chamadas HTTP). |
| `database` | Scripts SQL de criação e migração. |
| `tools` | Utilitários de preparação e scripts de validação. |

### Decisões de projeto

- **Repositório genérico atrás de interface.** Os serviços da Application dependem de `IEntityRepository<T>`, nunca do `DbContext`. Isso deixa a regra de negócio isolada do banco e fácil de trocar ou simular em testes.
- **Injeção de dependências** configurada num único lugar (`AddOdontoCare`), para os controllers receberem só interfaces e serviços.
- **DTOs com validação** (`[Required]`, `[MaxLength]`, `[Range]`...) na entrada, para a API recusar dado ruim antes de chegar na regra.
- **Tratamento de erros centralizado.** Regras violadas lançam `BusinessException` (com o código HTTP adequado) e um middleware transforma tudo em `ResultWrapper`. O cliente nunca recebe detalhe interno de banco.
- **Configuração sensível fora do código.** String de conexão e chave do JWT vêm de variáveis de ambiente; a API se recusa a iniciar sem elas.
- **Consulta não é apagada, é cancelada.** `DELETE /api/consultas/{id}` marca a consulta como `Cancelada` e mantém o histórico.
- **Agenda sem corrida.** Criar ou alterar consulta roda dentro de uma transação com bloqueio (`SELECT ... FOR UPDATE`) em paciente e profissional, para duas requisições simultâneas não furarem a regra de conflito.
- **Estoque consistente.** A movimentação de material e o novo saldo são gravados na mesma transação.
- **A arquitetura é verificada automaticamente.** O script `tools/verificar_arquitetura.ps1` roda no GitHub Actions e falha o build se uma camada depender do que não deve.

## 5. Banco de dados

- Banco: **MySQL 8.0**, base chamada `odontocare`.
- Criação: executar, **nesta ordem**, os scripts da pasta `database`:
  1. `database/odontocare.sql`
  2. `database/02_migracao_estoque.sql`
- Os scripts não trazem dados de demonstração nem senhas. O primeiro usuário (Administrador) é criado por um utilitário (passo 6.2).
- Tabelas principais: `usuario`, `paciente`, `consulta`, `procedimento`, `orcamento`, `pagamento`, `material`, `equipamento` e `movimentacao_estoque`.
- A conexão é informada pela variável de ambiente `ConnectionStrings__OdontoCare` (nada de senha dentro do repositório).

## 6. Como executar a API

### 6.1 Pré-requisitos

- **.NET SDK 10** (o SDK, não só o Runtime).
- **PHP 8.2** com as extensões `curl`, `mbstring`, `pdo_mysql` (e `dom`, se for rodar os testes de HTML).
- **MySQL 8.0** rodando localmente, com um usuário que acesse o banco.
- **Git**, para clonar.

Para conferir o que está instalado:

```powershell
dotnet --list-sdks
php --version
php -m
```

Se algum comando não for reconhecido, coloque a pasta do programa no PATH e abra outro terminal. No XAMPP o PHP costuma ficar em `C:\xampp\php`.

### 6.2 Passo a passo

Todos os comandos são executados na **raiz do projeto**. No Windows, use o PowerShell.

**1) Baixar o projeto**

```powershell
git clone https://github.com/Dhaniels1/OdontoCare-API.git
cd OdontoCare-API
```

**2) Preparar o banco.** No MySQL Workbench, execute `database/odontocare.sql` e depois `database/02_migracao_estoque.sql`. Use uma instalação de desenvolvimento, sem um banco `odontocare` que precise ser preservado.

**3) Criar o primeiro administrador.** Em um terminal separado:

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

Depois da mensagem de sucesso, feche esse terminal. O utilitário recusa uma segunda inicialização se já existir administrador. **Não existe senha padrão.**

**4) Iniciar a API.** Em outro terminal, na raiz do projeto. Quando pedir a conexão, use o formato:
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

Deixe o terminal aberto: é nele que aparecem os logs, inclusive os do cache. A chave do JWT é gerada na hora; se você reiniciar a API com outra chave, os tokens antigos deixam de valer e é preciso fazer login de novo.

O contrato OpenAPI fica em http://127.0.0.1:5240/openapi/v1.json (apenas em ambiente `Development`). Não há Swagger UI embutida.

### 6.3 Configurações disponíveis

| Variável / chave | Para que serve | Observação |
|---|---|---|
| `ConnectionStrings__OdontoCare` | Conexão com o MySQL | Obrigatória |
| `Jwt__Key` | Chave secreta de assinatura do token | Obrigatória, no mínimo 32 bytes |
| `Jwt__Issuer` / `Jwt__Audience` | Emissor e público do token | Obrigatórias |
| `Jwt__ExpirationMinutes` | Validade do token | Padrão: 60 |
| `Clinica__HorarioInicio` / `Clinica__HorarioFim` | Horário de funcionamento para agendar | Padrão: 08:00 e 18:00 |

## 7. Como executar o aplicativo (cliente PHP)

Em mais um terminal, na raiz do projeto:

```powershell
$env:ODONTOCARE_API_URL = 'http://127.0.0.1:5240'
$sessionDir = Join-Path $env:TEMP 'OdontoCare-sessions'
New-Item -ItemType Directory -Path $sessionDir -Force | Out-Null
php -d "session.save_path=$sessionDir" -S 127.0.0.1:58080 -t OdontoCare-main
```

Abra **http://127.0.0.1:58080/login.php** e entre com a conta criada no passo 6.2. O cliente só precisa saber a URL da API (`ODONTOCARE_API_URL`). Para parar, `Ctrl+C` em cada terminal; os dados continuam no MySQL.

**Como o cliente conversa com a API.** As telas continuam chamando as classes `*Dao.php`, mas elas agora não tocam no banco: usam `api/DadosApi.php` e `api/ApiClient.php` para fazer a requisição HTTP. No login, o PHP guarda o JWT na sessão e passa a mandar `Authorization: Bearer <token>` em todas as chamadas. Se a API responder 401, o cliente descarta o token e volta para o login. Formulários têm proteção CSRF (`config/seguranca.php`).

**Roteiro rápido para conferir se está tudo funcionando:**

1. Cadastre um dentista, um paciente e um procedimento fictícios.
2. Agende uma consulta entre 08:00 e 18:00 e tente repetir o mesmo horário: deve aparecer o conflito.
3. Cadastre orçamento e pagamento e abra o recibo.
4. Cadastre um material, registre entrada e saída e confira o saldo e o alerta de estoque mínimo.
5. Consulte os materiais duas vezes e veja `CACHE MISS` e `CACHE HIT` no terminal da API; edite um material e veja `CACHE INVALIDADO`.
6. Entre como dentista e confira as permissões e a agenda do profissional.

## 8. Padronização das respostas (ResultWrapper)

Toda resposta da API, de sucesso ou de erro, tem o mesmo formato (classe `ResultWrapper<T>`, na camada Application):

| Campo | Tipo | Significado |
|---|---|---|
| `success` | bool | `true` se a operação deu certo |
| `message` | string | Mensagem legível para o usuário |
| `data` | objeto, lista ou `null` | O conteúdo da resposta |
| `errors` | lista de strings | Detalhes, usado nos erros de validação |

Métodos de fábrica: `Ok`, `Failure`, `ValidationError`, `NotFound`, `Unauthorized` e `ServerError`.

O formato vale também para o que o ASP.NET geraria por conta própria: erro de validação do modelo (400), falta de token (401), falta de permissão (403), rota inexistente (404) e exceções não tratadas (middleware global). Nenhuma resposta vaza detalhe interno do banco.

Códigos HTTP usados: **200**, **201**, **400** (dados inválidos), **401** (sem autenticação ou token inválido), **403** (perfil sem permissão), **404** (não encontrado), **409** (conflito, como horário já ocupado ou CPF duplicado) e **500** (erro interno).

## 9. Autenticação e autorização (JWT)

### Fluxo

1. O cliente envia `POST /api/auth/login` com `identificador` (login ou e-mail) e `senha`.
2. A API busca o usuário e confere a senha com o hash PBKDF2 guardado no banco. Se não bater, responde 401.
3. Se bater, gera um **JWT assinado (HS256)** e devolve junto com a data de expiração e os dados do usuário.
4. Nas chamadas seguintes o cliente manda o cabeçalho `Authorization: Bearer <token>`.
5. A API valida **assinatura, emissor, público e validade** do token. Se estiver tudo certo e o **perfil** tiver permissão para aquela rota, a requisição segue; senão responde 401 ou 403.

### Conteúdo do token (claims)

| Claim | Conteúdo |
|---|---|
| `sub` | Id do usuário |
| `name` | Nome do usuário |
| `role` | Perfil (Administrador, Secretária ou Dentista) |
| `email` | E-mail, quando houver |
| `jti` | Identificador único do token |
| `exp` | Expiração (60 minutos por padrão) |

Tolerância de relógio de 15 segundos. Chave, emissor e público vêm de variáveis de ambiente, nunca do código-fonte.

### Senhas

Guardadas com **PBKDF2-SHA256 e salt individual**. A senha em texto puro nunca é gravada nem devolvida. A conta de administrador exige no mínimo 12 caracteres.

### Perfis e permissões

| Perfil | O que pode fazer |
|---|---|
| **Administrador** | Tudo, incluindo cadastrar, alterar e remover usuários e excluir materiais e equipamentos |
| **Secretária** | Cadastrar, alterar e excluir pacientes, consultas, procedimentos, orçamentos, pagamentos, materiais (e movimentar estoque) e equipamentos (cadastrar e alterar) |
| **Dentista** | Somente leitura; na agenda enxerga **apenas as próprias consultas** |

## 10. Estratégia de cache

**O que é cacheado:** a lista de materiais (`GET /api/materiais`), que é uma consulta lida com muita frequência e muda pouco. A busca por texto, o filtro de estoque baixo e a paginação são aplicados em memória sobre a lista já carregada.

**Onde:** `IMemoryCache`, dentro da própria API (classe `MaterialMemoryCache`, na Infrastructure, atrás da interface `IMaterialCache`, na Application). O serviço de materiais não sabe como o cache funciona.

**Expiração:**

- absoluta de **2 minutos** (depois disso o dado é descartado de qualquer jeito);
- deslizante de **30 segundos** (se ninguém usa, sai antes).

**Invalidação:** qualquer escrita em material remove a chave do cache, assim ninguém lê saldo velho. Isso acontece ao **criar**, **editar**, **excluir** e ao **registrar entrada ou saída de estoque**.

**Como saber, pelo log, de onde veio o dado:**

| Mensagem no terminal da API | Significado |
|---|---|
| `CACHE MISS: materiais (origem: banco de dados).` | Não havia cache; leu do MySQL e guardou |
| `CACHE HIT: materiais (origem: memória).` | Respondeu direto do cache |
| `CACHE INVALIDADO: materiais.` | Uma escrita limpou o cache |

**Demonstração:** chame `GET /api/materiais` duas vezes seguidas (MISS e depois HIT), crie ou edite um material (INVALIDADO) e chame de novo (MISS outra vez).

**Limite conhecido:** o cache é local a uma única instância da API. Com várias instâncias seria preciso um cache distribuído (por exemplo, Redis).

## 11. Principais endpoints

Todas as rotas ficam sob `/api`. **Leitura** = qualquer usuário autenticado; **Gestão** = Administrador e Secretária; **Admin** = só Administrador.

| Método | Rota | Descrição | Acesso |
|---|---|---|---|
| POST | `/api/auth/login` | Autentica e devolve o JWT | Público |
| POST | `/api/auth/registro` | Cadastra usuário | Admin |
| GET | `/api/usuarios` | Lista usuários | Gestão |
| GET | `/api/usuarios/{id}` | Busca usuário | Admin |
| PUT | `/api/usuarios/{id}` | Atualiza usuário | Admin |
| DELETE | `/api/usuarios/{id}` | Remove usuário | Admin |
| GET | `/api/pacientes` | Lista pacientes | Leitura |
| GET | `/api/pacientes/{id}` | Busca paciente | Leitura |
| POST | `/api/pacientes` | Cadastra paciente | Gestão |
| PUT | `/api/pacientes/{id}` | Atualiza paciente | Gestão |
| DELETE | `/api/pacientes/{id}` | Remove paciente | Gestão |
| GET | `/api/consultas` | Lista consultas (dentista vê só as suas) | Leitura |
| GET | `/api/consultas/{id}` | Busca consulta | Leitura |
| POST | `/api/consultas` | Agenda consulta (valida conflito e horário) | Gestão |
| PUT | `/api/consultas/{id}` | Atualiza ou reagenda | Gestão |
| PATCH | `/api/consultas/{id}/cancelar` | Cancela, mantendo o histórico | Gestão |
| DELETE | `/api/consultas/{id}` | Também cancela (não apaga) | Gestão |
| GET | `/api/procedimentos` e `/{id}` | Lista e busca procedimentos | Leitura |
| POST / PUT / DELETE | `/api/procedimentos` e `/{id}` | Cria, atualiza e remove | Gestão |
| GET | `/api/orcamentos` e `/{id}` | Lista e busca orçamentos | Leitura |
| POST / PUT / DELETE | `/api/orcamentos` e `/{id}` | Cria, atualiza e remove | Gestão |
| GET | `/api/pagamentos` e `/{id}` | Lista (filtro `?consultaId=`) e busca | Leitura |
| POST / PUT / DELETE | `/api/pagamentos` e `/{id}` | Cria, atualiza e remove | Gestão |
| GET | `/api/pagamentos/{id}/recibo` | Dados do recibo | Leitura |
| GET | `/api/materiais` | Lista paginada, **com cache** (`pagina`, `tamanho`, `busca`, `estoqueBaixo`) | Leitura |
| GET | `/api/materiais/{id}` e `/{id}/movimentacoes` | Busca e histórico de estoque | Leitura |
| POST / PUT | `/api/materiais` e `/{id}` | Cria e atualiza | Gestão |
| POST | `/api/materiais/{id}/entradas` e `/saidas` | Movimenta o estoque | Gestão |
| DELETE | `/api/materiais/{id}` | Remove | Admin |
| GET | `/api/equipamentos` e `/{id}` | Lista e busca | Leitura |
| POST / PUT | `/api/equipamentos` e `/{id}` | Cria e atualiza | Gestão |
| DELETE | `/api/equipamentos/{id}` | Remove | Admin |
| GET | `/api/painel` | Números gerais da clínica | Leitura |

Os campos exatos de cada operação estão no contrato OpenAPI (`/openapi/v1.json`).

## 12. Exemplos de requisição e resposta

Os exemplos usam `curl` e a API em `http://127.0.0.1:5240`. Os valores são ilustrativos; o formato é o que a API devolve.

### 12.1 Login (sucesso)

```bash
curl -X POST http://127.0.0.1:5240/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"identificador":"admin","senha":"SUA_SENHA"}'
```

Resposta `200 OK`:

```json
{
  "success": true,
  "message": "Autenticado com sucesso.",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expiraEmUtc": "2026-11-20T15:00:00Z",
    "usuario": {
      "idUsuario": 1,
      "nome": "Administrador",
      "email": null,
      "login": "admin",
      "tipoUsuario": "Administrador"
    }
  },
  "errors": []
}
```

### 12.2 Login com senha errada

Resposta `401 Unauthorized`:

```json
{
  "success": false,
  "message": "Usuário ou senha inválidos.",
  "data": null,
  "errors": []
}
```

### 12.3 Endpoint protegido sem token

```bash
curl http://127.0.0.1:5240/api/pacientes
```

Resposta `401 Unauthorized`:

```json
{
  "success": false,
  "message": "Autenticação necessária.",
  "data": null,
  "errors": []
}
```

### 12.4 Endpoint protegido com token

```bash
curl http://127.0.0.1:5240/api/procedimentos \
  -H "Authorization: Bearer SEU_TOKEN"
```

Resposta `200 OK`:

```json
{
  "success": true,
  "message": "Operação realizada com sucesso.",
  "data": [
    {
      "idProcedimento": 1,
      "nomeProcedimento": "Limpeza",
      "descricao": "Profilaxia completa",
      "valorProcedimento": 150.00
    }
  ],
  "errors": []
}
```

### 12.5 Perfil sem permissão (dentista tentando cadastrar paciente)

Resposta `403 Forbidden`:

```json
{
  "success": false,
  "message": "Você não tem permissão para esta operação.",
  "data": null,
  "errors": []
}
```

### 12.6 Dados inválidos (validação)

```bash
curl -X POST http://127.0.0.1:5240/api/pacientes \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"nome":"","cpf":""}'
```

Resposta `400 Bad Request`:

```json
{
  "success": false,
  "message": "Dados inválidos.",
  "data": null,
  "errors": [
    "O nome é obrigatório.",
    "O CPF é obrigatório."
  ]
}
```

### 12.7 Agendamento com conflito de horário

```bash
curl -X POST http://127.0.0.1:5240/api/consultas \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"dataConsulta":"2026-11-20","horarioConsulta":"09:00:00","idUsuario":2,"idPaciente":1,"idProcedimento":1}'
```

Na primeira vez, `201 Created`. Repetindo o mesmo horário para o mesmo profissional ou paciente, `409 Conflict`:

```json
{
  "success": false,
  "message": "Paciente ou profissional já possui consulta neste horário.",
  "data": null,
  "errors": []
}
```

Fora do expediente (por exemplo, às 22:00) a resposta é `409` com a mensagem `Agendamento fora do horário de funcionamento configurado.`

## 13. Testes e validações

O projeto traz scripts executáveis na pasta `tools`:

| Script | O que confere |
|---|---|
| `verificar_arquitetura.ps1` | Se as camadas respeitam a regra de dependência (roda também no GitHub Actions) |
| `validar_entrega.ps1` | Executa a bateria de validações integradas |
| `validar_entrega_api.php`, `validar_financeiro.php`, `validar_integracao_estendida.php` | Endpoints, financeiro e integração PHP + API |
| `validar_seguranca_web.php` | Segurança do cliente (sessão, CSRF) |
| `validar_cache.php` | Comportamento do cache (MISS, HIT e invalidação) |
| `validar_concorrencia_agenda.php` | Duas reservas simultâneas no mesmo horário |
| `validar_expiracao_jwt.php` | Token expirado deve ser recusado |

Para conferir só a arquitetura:

```powershell
./tools/verificar_arquitetura.ps1
```

Os testes integrados criam registros fictícios, e alguns excluem os próprios registros. Por isso devem rodar num **banco descartável**, nunca no da apresentação. Configure `ODONTOCARE_DB_HOST`, `ODONTOCARE_DB_NAME`, `ODONTOCARE_DB_USER` e `ODONTOCARE_DB_PASS` como no passo 6.2, e depois:

```powershell
$env:ODONTOCARE_VALIDACAO_ISOLADA = '1'
$env:ODONTOCARE_API_URL = 'http://127.0.0.1:PORTA_API_TESTE'
$env:ODONTOCARE_WEB_URL = 'http://127.0.0.1:PORTA_CLIENTE_TESTE'
./tools/validar_entrega.ps1
```

Para incluir todos os cenários extras, configure também `ODONTOCARE_CACHE_LOG` (caminho absoluto do log da API, com a categoria do cache em Information), `ODONTOCARE_API_EXPIRACAO_URL` (outra API com `Jwt__ExpirationMinutes=1`) e `ODONTOCARE_API_FALHA_URL` (API apontando para um banco indisponível). O teste de cache precisa de acesso exclusivo aos materiais durante a medição.

O GitHub Actions (`.github/workflows/dotnet.yml`) compila a solução e roda a verificação de arquitetura a cada push, mas **não** substitui os testes integrados.

## 14. Alternativa com Docker

Requer Docker Desktop (ou Engine) com contêineres Linux e Compose. Esta alternativa está preparada, **mas a execução dos contêineres ainda precisa de homologação**.

```powershell
./tools/preparar_docker.ps1
docker compose config --quiet
docker compose up -d --build
docker compose --profile setup run --rm --build setup
```

O preparador cria um `.env` e pede a senha inicial. Login: `admin`. Cliente em http://127.0.0.1:58084/login.php e OpenAPI em http://127.0.0.1:5244/openapi/v1.json. O banco só é inicializado na primeira criação do volume.

Para consultar e parar sem apagar os dados:

```powershell
docker compose ps
docker compose logs --tail 60 api web
docker compose stop
```

Não compartilhe o `.env`. Trocar as senhas depois não altera as credenciais de um banco já inicializado.

## 15. Reprodução do projeto do zero

Resumo para quem baixa o repositório e quer ver rodando:

1. Instalar .NET SDK 10, PHP 8.2 (com `curl`, `mbstring`, `pdo_mysql`) e MySQL 8.0.
2. `git clone https://github.com/Dhaniels1/OdontoCare-API.git` e entrar na pasta.
3. No MySQL, executar `database/odontocare.sql` e `database/02_migracao_estoque.sql`.
4. Criar o administrador com `php tools/criar_admin.php` (passo 6.2). Não há usuário nem senha padrão.
5. Iniciar a API na porta **5240** com as variáveis `ConnectionStrings__OdontoCare`, `Jwt__Key`, `Jwt__Issuer` e `Jwt__Audience` (passo 6.2).
6. Iniciar o cliente PHP na porta **58080** apontando para a API (seção 7).
7. Abrir http://127.0.0.1:58080/login.php e entrar com a conta criada no passo 4.

Qualquer configuração específica está descrita nas seções 5, 6 e 7. Nenhum segredo fica no repositório.

## 16. Problemas comuns e limites conhecidos

**Problemas comuns**

- **Falha de conexão:** confira se o MySQL está de pé, a porta, o usuário, a senha e se os dois scripts SQL foram executados.
- **PHP não encontra driver ou função:** confira `php -m` e o `php.ini` carregado (`php --ini`).
- **Login volta para a tela inicial:** confira a pasta de sessões e mantenha a API em execução.
- **401:** faça login de novo; o token pode ter expirado ou a chave do JWT mudou ao reiniciar a API.
- **403:** confira o perfil do usuário e use o formulário atual, que traz a proteção CSRF.
- **Porta ocupada:** encerre a instância anterior ou ajuste a porta e a URL correspondente.

**Limites conhecidos**

- O pacote é para execução acadêmica local; não há implantação externa nem teste de carga.
- A execução via Docker está preparada, mas ainda não foi homologada.
- Não há Swagger UI; só o contrato OpenAPI em JSON.
- O cache é local a uma instância da API e merece um teste de leitura simultânea com gravação.

## 17. Divisão de responsabilidades

| Integrante | Responsabilidade |
|---|---|
| Bruna | Estrutura inicial da API e CRUDs de Pacientes e Consultas |
| Daniel | ResultWrapper, JWT, autorização, cache, materiais, integração com o PHP e revisão final |
| Glauco | Documentação (este README) e conferência das instruções de execução |
| Adla | Testes, checklist de requisitos e roteiro de apresentação |

**Histórico do repositório.** A base da API (estrutura em camadas e os CRUDs de Pacientes e Consultas) foi iniciada pela Bruna em https://github.com/Bruna-ap/OdontoCare-API. Esse código foi baixado e integrado ao repositório principal pelo Daniel, que por isso concentra os commits iniciais. Os commits seguintes de documentação e revisão são feitos pelos demais integrantes neste mesmo repositório.
