# OdontoCare — Entrega técnica de Daniel

> **Estado atualizado em 02/11/2026:** a API foi compilada e a integração PHP/API foi executada com MySQL 8 e MariaDB em instâncias isoladas. As verificações, correções e limites estão em [VALIDACAO_LOCAL_2026-11-02.md](VALIDACAO_LOCAL_2026-11-02.md). Para executar e apresentar, seguir [EXECUCAO_E_DEMONSTRACAO.md](EXECUCAO_E_DEMONSTRACAO.md).

## 1. Base revisada

A base recebida tinha quatro projetos (`OdontoCare.Domain`, `OdontoCare.Application`, `OdontoCare.Infrastructure`, `OdontoCare.API`) em .NET 10, usando o provedor `MySql.EntityFrameworkCore` para o banco legado. A aplicação cliente estava em `OdontoCare-main/` (PHP), com `ApiClient.php` já preparado para aceitar ResultWrapper, mas o login era realizado por SQL e as telas ainda consultavam DAOs locais. Os novos arquivos respeitam a organização existente, mantêm as tabelas/colunas do banco e utilizam DTOs para evitar exposição do campo `senha`.

## 2. Funcionalidades desenvolvidas

| Entrega | Arquivos principais | Camada |
|---|---|---|
| ResultWrapper e erros de negócio | `Common/ResultWrapper.cs`, `BusinessException.cs` | Application |
| Falhas globais e validação | `Middleware/ExceptionMiddleware.cs`, `Program.cs` | API |
| JWT com claims sub/name/email/role/jti | `Security/JwtTokenService.cs`, `Extensions/DependencyInjection.cs` | API |
| Login, cadastro e administração de usuários | `AuthController.cs`, `UsuariosAdminController.cs`, `AuthService.cs`, `UsuarioAdminService.cs` | API/Application |
| Senhas PBKDF2 com salt individual, verificação em tempo constante | `Pbkdf2PasswordService.cs` | Infrastructure |
| Matriz de papéis | `Security/Roles.cs`, atributos `[Authorize]` | Application/API |
| CRUD pacientes, consultas; horários configuráveis; cancelamento lógico | `PacientesController.cs`, `ConsultasController.cs`, `ConsultaService.cs` | API/Application |
| CRUD de materiais e equipamentos, estoque, movimentação e histórico | `MaterialService.cs`, `MaterialRepository.cs`, `EquipamentoService.cs`, controllers | Application/Infrastructure/API |
| Cache da listagem de materiais (2 minutos absolutos/30 segundos deslizantes), invalidação em escrita e logs HIT/MISS | `Caching/MaterialMemoryCache.cs` | Infrastructure |
| Esquema banco do histórico de movimentação | `database/02_migracao_estoque.sql` | Banco |
| Cliente PHP via HTTP e token em sessão no servidor; materiais | `ApiClient.php`, DAOs de pacientes/consultas/usuários, `materiais.php`, login/agenda | Cliente |
| Migração de senhas legadas | `tools/migrar_senhas.php` e `tools/criar_admin.php` | Ferramenta CLI |

Pagamentos, orçamentos, procedimentos, recibo, painel e seletores de formulários agora consomem a API. O cliente não usa PDO. Casos de uso de autenticação, usuários, pacientes, consultas e equipamentos foram movidos para Application, com repositórios e bloqueios em Infrastructure. O [relatório de entrega](ENTREGA_DANIEL.md) descreve a regressão desta versão.

## 3. Instruções para executar

### Pré-requisitos

- SDK **.NET 10**, MySQL ou MariaDB, PHP 8+ com `curl`, PDO MySQL, e um servidor PHP.
- Banco `odontocare` criado pelo script `database/odontocare.sql` (esquema) ou por uma base local equivalente.
- Aplicar `database/02_migracao_estoque.sql` **após** o esquema base. Cria `movimentacao_estoque` com FKs para `material` e `usuario`.

### Segredos

Não adicionar senha do banco ou chave JWT no `appsettings.json`. Usar Secret Manager ou variáveis de ambiente:

```bash
cd OdontoCare.API
# Exemplo ilustrativo, substitua pelos valores locais: NÃO commit esses dados.
dotnet user-secrets set 'ConnectionStrings:OdontoCare' 'server=127.0.0.1;port=3306;database=odontocare;user=SEU_USUARIO;password=SUA_SENHA'
dotnet user-secrets set 'Jwt:Key' 'GERE_UMA_CHAVE_ALEATORIA_COM_MAIS_DE_32_BYTES_E_GUARDE_EM_SECRETS'
dotnet run --urls http://localhost:5240
```

A configuração `Jwt:Issuer` = `OdontoCare.API`, `Jwt:Audience` = `OdontoCare.Cliente`, `Jwt:ExpirationMinutes` = 60. Para uso em outro ambiente, configure via ambiente `ConnectionStrings__OdontoCare` e `Jwt__Key`. O startup rejeita configurações ausentes ou chave com menos de 32 bytes. Utilize **HTTPS em produção**.

O horário de expediente inicial está configurado em `Clinica:HorarioInicio` = `08:00` e `Clinica:HorarioFim` = `18:00`, uma **hipótese de desenvolvimento**: confirmar o horário real da clínica e ajustar antes da apresentação.

### Se o banco ainda não possui usuários

Após executar os scripts SQL, configure também `ODONTOCARE_ADMIN_LOGIN`, `ODONTOCARE_ADMIN_PASSWORD` (12+ caracteres) e opcionalmente `ODONTOCARE_ADMIN_NAME`. Execute `php tools/criar_admin.php` no terminal. Esse comando permite criar **o primeiro** administrador apenas quando não existe nenhum administrador; não há endpoint HTTP anônimo de bootstrap. Não reutilize as senhas de exemplo do dump antigo.

### Migrar senhas do legado

O dump populado original tinha senhas em texto puro. Ele foi removido do pacote atualizado, que conserva o dump *somente com esquema* em `database/odontocare.sql`. Se já existe banco importado com usuários legados:

1. Fazer backup privado do banco.
2. Configurar `ODONTOCARE_DB_HOST`, `ODONTOCARE_DB_NAME`, `ODONTOCARE_DB_USER`, `ODONTOCARE_DB_PASS` no ambiente.
3. Executar `php tools/migrar_senhas.php` a partir da raiz do repositório.
4. Trocar imediatamente as senhas de demonstração por senhas fortes usando `PUT /api/usuarios/{id}` na sessão do administrador autenticado.

O utilitário é idempotente para hashes no formato PBKDF2 configurado. Login **não aceita** senha em texto puro na coluna `usuario.senha`. Não divulgar o dump antigo contendo dados pessoais ou credenciais nos anexos da entrega.

### Aplicativo PHP

Configure as variáveis de ambiente para o servidor PHP:

```text
ODONTOCARE_API_URL=http://localhost:5240
```

`ODONTOCARE_DB_*` é usado apenas pelos utilitários CLI de configuração e teste, nunca pelo servidor PHP. O cliente PHP autentica em `/api/auth/login`, armazena JWT na sessão PHP, envia `Authorization: Bearer ...` nas chamadas HTTP e elimina o token ao sair. Em ambientes distribuídos, proteger armazenamento das sessões (`Secure`, `HttpOnly`, `SameSite`) e evitar usar HTTP sem TLS. Token não é guardado em navegador/localStorage; logo, não aparece diretamente no JavaScript.

## 4. Exemplos de API e testes

Use `OdontoCare.API/OdontoCare.API.http` ou importe `GET /openapi/v1.json` no Postman (OpenAPI ativo em `Development`). O projeto não inclui Swagger UI embutido.

### Login

```http
POST /api/auth/login
Content-Type: application/json

{"identificador":"usuario_existente","senha":"senha_configurada"}
```

Sucesso 200:

```json
{"success":true,"message":"Autenticado com sucesso.","data":{"token":"<jwt>","expiraEmUtc":"<data UTC>","usuario":{"idUsuario":1,"nome":"Exemplo","email":"exemplo@teste.com","login":"exemplo","tipoUsuario":"Administrador"}},"errors":[]}
```

Erro 401:

```json
{"success":false,"message":"Usuário ou senha inválidos.","data":null,"errors":[]}
```

### Cadastrar material

```http
POST /api/materiais
Authorization: Bearer <jwt>
Content-Type: application/json

{"nomeMaterial":"Luva nitrílica","descricao":"Caixa com luvas","estoqueMinimo":10,"unidade":"Caixa"}
```

Sucesso HTTP 201. **O saldo inicial é zero**; registrar entrada obrigatoriamente por uma movimentação auditável:

```http
POST /api/materiais/1/entradas
Authorization: Bearer <jwt>
Content-Type: application/json

{"quantidade":20}
```

Saída maior que o saldo retorna HTTP 409, no formato ResultWrapper.

### Demonstração do cache

1. Autenticar com token do perfil autorizado.
2. `GET /api/materiais?pagina=1&tamanho=20` → console da API mostra `CACHE MISS`.
3. Repetir o GET → console mostra `CACHE HIT`.
4. Criar ou atualizar material, registrar entrada/saída ou excluir → console mostra `CACHE INVALIDADO`.
5. Repetir GET → `CACHE MISS` e dados atualizados.

O cache é adequado apenas para **uma única instância de API**. Em múltiplas instâncias, mover a implementação de `IMaterialCache` para Redis/cache distribuído e coordenar invalidação.

### Outros endpoints

| Método | Rota | Perfil |
|---|---|---|
| POST | `/api/auth/login` | Público |
| POST | `/api/auth/registro` | Administrador |
| GET/POST/PUT/DELETE | `/api/pacientes[/{id}]` | Leitura todos; escrita Admin/Secretária |
| GET/POST/PUT | `/api/consultas[/{id}]` | Leitura todos (dentista vê sua agenda); escrita Admin/Secretária |
| PATCH | `/api/consultas/{id}/cancelar` | Admin/Secretária |
| DELETE | `/api/consultas/{id}` | Admin/Secretária (cancelamento lógico) |
| GET/POST/PUT/DELETE | `/api/materiais[/{id}]` | GET todos; escrita Admin/Secretária; DELETE Admin |
| POST | `/api/materiais/{id}/entradas` | Admin/Secretária |
| POST | `/api/materiais/{id}/saidas` | Admin/Secretária |
| GET | `/api/materiais/{id}/movimentacoes` | Todos |
| GET/POST/PUT/DELETE | `/api/equipamentos[/{id}]` | GET todos; escrita Admin/Secretária; DELETE Admin |
| GET | `/api/usuarios` | Admin/Secretária |
| GET/PUT/DELETE | `/api/usuarios/{id}` | GET Admin, PUT/DELETE Admin |
| GET | `/api/procedimentos` | Todos |

Todos os **resultados de sucesso com corpo** utilizam `ResultWrapper`. Para preservar o padrão, operações de atualização e exclusão retornam **200 com envelope JSON**, em vez de `204 No Content` (que não admite corpo). Validação responde 400; falta de login, 401; acesso negado, 403; registro ausente, 404; regra conflitante, 409; erro interno, 500.

## 5. Matriz de permissões

- Administrador: gerencia usuários, pacientes, consultas, equipamentos e materiais, inclui exclusões.
- Secretária: lê usuários para escolha do profissional, cadastra/edita pacientes e consultas, movimenta materiais; não administra contas.
- Dentista: lê pacientes, materiais, procedimentos e equipamentos, e **apenas suas próprias consultas**. Não altera cadastros administrativos.

Adaptação de nomenclatura: perfis do banco antigo `Administrador`, `Dentista`, `Secretária`; a API normaliza também `Secretaria`, `Recepcionista` e `Profissional` quando recebidos em cadastro. O frontend nunca é a fonte de autorização.

## 6. Pendências e limitações antes da entrega

A lista histórica foi substituída pelo [checklist acadêmico](CHECKLIST_ACADEMICO_DANIEL.md), cruzado com a especificação do professor e a divisão de responsabilidades em 02/11/2026.

Compilação, banco isolado, perfis, concorrência, CSRF, expiração JWT e cache já têm evidências no [relatório local](VALIDACAO_LOCAL_2026-11-02.md). A integração PHP/API e a separação das camadas foram concluídas nesta rodada. Permanecem a validação do Docker, conferência dos dados da equipe no README, reprodução/apresentação pela equipe e fechamento do Git após autorização. O checklist distingue essas pendências de melhorias opcionais.

**Refresh token** não foi implementado porque a especificação acadêmica exige autenticação JWT, mas não solicita renovação. Quando expirar, o cliente deve autenticar novamente. Em produção, cabe implementar revogação/refresh token com rotação, HTTPS obrigatório e monitoramento de autenticação.
