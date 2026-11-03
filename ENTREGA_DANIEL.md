# Entrega técnica de Daniel — 02/11/2026

## Resultado

Versão preparada para revisão e passagem ao próximo integrante. **282 verificações automatizadas aprovadas nesta rodada**, em MySQL 8.0.42. Compilação Debug e Release: zero erros e zero avisos. Sintaxe de 58 arquivos PHP aprovada. Fronteiras de arquitetura e diff conferidos.

Esse resultado não substitui a homologação visual, a execução do Docker nem a avaliação do professor.

## Mudanças desta rodada

1. Procedimentos, orçamentos e pagamentos receberam endpoints de CRUD com JWT, perfis, validação de valores/datas/opções e vínculos, ResultWrapper e tratamento de inexistência/conflito.
2. DAOs financeiros, recibo, painel e seletores de formulários passaram a consumir a API. Não há SQL/PDO ativo no cliente PHP. O teste web foi executado com host/usuário/senha de banco inválidos no processo do PHP; a API usou sua própria conexão válida.
3. Autenticação, administração de usuários, pacientes, consultas e equipamentos passaram para Application. `IEntityRepository<T>` não expõe EF nem IQueryable. Infrastructure implementa consultas, persistência e bloqueios; a API compõe as dependências.
4. A transação ReadCommitted da agenda continua bloqueando paciente e profissional em ordem fixa. Seis cenários concorrentes e a suíte estendida verificam as reservas após a mudança.
5. O painel do dentista limita a agenda ao próprio profissional e não exibe totais financeiros globais.
6. README, checklist e passagem foram atualizados; Dockerfiles, Compose, gerador local de configuração e verificador de arquitetura foram incluídos.

Não houve alteração do esquema para os módulos financeiros: as tabelas já existiam. Não houve commit ou push.

## Evidências executadas

| Arquivo | Verificações | Falhas |
|---|---:|---:|
| `validacao-entrega-aceite.json` | 24 | 0 |
| `validacao-entrega-api.json` | 72 | 0 |
| `validacao-entrega-cache.json` | 7 | 0 |
| `validacao-entrega-concorrencia.json` | 6 | 0 |
| `validacao-entrega-estendida.json` | 39 | 0 |
| `validacao-entrega-expiracao.json` | 3 | 0 |
| `validacao-entrega-financeiro.json` | 33 | 0 |
| `validacao-entrega-seguranca.json` | 98 | 0 |
| **Total** | **282** | **0** |

- Financeiro web: 33 verificações. As antigas opções de revogação de usuário PDO do servidor PHP não se aplicam à arquitetura nova; testes históricos com outros totais permanecem no relatório cronológico.
- API de entrega: 72 verificações de CRUD, centavos, campos ausentes, valores rejeitados sem alteração, perfis, referências inexistentes, recibo, painel e estoque.
- Segurança web: 98 verificações, incluindo CSRF, saída segura, GET sem efeitos, HTML/JSON e operações POST autorizadas.
- Aceite: 24 verificações, incluindo os três perfis, hash/salt e HTTP 500 induzido em instância separada com banco indisponível.
- Cache: sete verificações, incluindo expiração absoluta observada após aproximadamente 123 segundos com acessos intermediários.
- JWT: três verificações com token real de um minuto, esperando validade e tolerância; rejeição 401 e remoção do token no cliente.

A primeira tentativa web desta rodada falhou porque o servidor de teste recebeu um caminho relativo incorreto para sessões PHP. Corrigido para caminho absoluto, as suítes foram executadas novamente e os resultados acima pertencem às execuções completas aprovadas. O erro de configuração não foi ocultado nem considerado teste aprovado.

## Como repetir

Preparar banco/API/PHP descartáveis conforme o roteiro. Em um terminal só de testes, definir:

```powershell
$env:ODONTOCARE_VALIDACAO_ISOLADA = '1'
$env:ODONTOCARE_API_URL = 'http://127.0.0.1:PORTA_API_TESTE'
$env:ODONTOCARE_WEB_URL = 'http://127.0.0.1:PORTA_PHP_TESTE'
$env:ODONTOCARE_DB_HOST = '127.0.0.1;port=PORTA_BANCO_TESTE'
$env:ODONTOCARE_DB_NAME = 'odontocare'
$env:ODONTOCARE_DB_USER = Read-Host 'Usuario do banco de teste'
$dbSecret = Read-Host 'Senha do banco de teste' -AsSecureString
$env:ODONTOCARE_DB_PASS = [Net.NetworkCredential]::new('', $dbSecret).Password
./tools/validar_entrega.ps1
```

Para reproduzir todas as 282 verificações, configurar também antes do executor:

- `ODONTOCARE_API_FALHA_URL`: outra API, apontando para uma porta de banco indisponível; necessário aos dois testes de 500.
- `ODONTOCARE_CACHE_LOG`: caminho absoluto do log da API de teste, com categoria `OdontoCare.Infrastructure.Caching.MaterialMemoryCache` em Information. Não consultar/movimentar materiais por outro cliente durante a suíte temporizada.
- `ODONTOCARE_API_EXPIRACAO_URL`: API separada com `Jwt__ExpirationMinutes=1`. Não alterar a validade da API da apresentação.

O executor encerra com erro se uma suíte falhar. Os resultados não incluem JWT, senhas ou hashes. As contas/fixtures ficam apenas no banco descartável, não no pacote.

## Limites e responsabilidade seguinte

- Docker: sintaxe YAML, serviços, isolamento de configuração e sintaxe do preparador PowerShell conferidos. Imagens/contêineres **não executados**, pois não há Docker instalado. Validar conforme DOCKER.md.
- MariaDB: validado anteriormente; esta rodada final usou MySQL 8.0.42. Não atribuir a MariaDB os resultados novos sem repetir.
- Interface: HTML e fluxos HTTP testados; a revisão visual humana e o ensaio de apresentação ficam com Adla/equipe.
- Documentação: Glaucio deve conferir integrantes completos, matrícula e instruções em sua máquina.
- Git: mudanças locais prontas para revisão; commit, push e CI remoto dependem de autorização e execução posteriores.
- Desempenho: sem teste de carga. As listas financeiras/painel usam a base pequena do trabalho; crescimento exige paginação e consultas agregadas no banco.
- Layout: a variante Agenda em destaque ficou opcional. Esta rodada priorizou funcionamento e entrega acadêmica.

[Passagem para a equipe](PASSAGEM_PARA_EQUIPE.md) · [Checklist atualizado](CHECKLIST_ACADEMICO_DANIEL.md)


## Conferência do pacote

O ZIP foi extraído em uma pasta separada. A solução extraída restaurou as dependências usando o cache local e compilou em Release com zero erros e zero avisos. Isso verifica que os fontes necessários estão no pacote; não representa instalação do SDK/PHP/MySQL em uma segunda máquina. O manifesto SHA256 permite conferir os arquivos recebidos. Nenhuma pasta bin/obj, sessão, banco, .env ou credencial local foi incluída.
