# Validação local — 02/11/2026

## Resultado

Validação básica real aprovada: compilação .NET, esquema MariaDB, autenticação, persistência de pacientes e integração PHP. Não representa homologação completa de todos os módulos.

Base inspecionada: branch main, commit c5bd0a1. Clone inicialmente limpo. A primeira rodada não alterou código funcional; as correções das rodadas seguintes estão descritas abaixo. Nenhum commit ou push foi executado.

## Ambiente

- Windows x64; inicialmente somente runtime .NET 8.0.15, sem SDK.
- SDK oficial .NET 10.0.403 instalado em pasta local `.validation/dotnet`, fora do repositório.
- PHP CLI 8.2.12 do XAMPP; extensões curl, PDO, pdo_mysql e mysqli disponíveis.
- MariaDB 10.4.32 do XAMPP, com diretório de dados novo em `.validation/mariadb`, fora do repositório; escuta apenas em 127.0.0.1:33316.
- API em http://127.0.0.1:5240; cliente PHP em http://127.0.0.1:58080 durante os testes.
- MySQL 8.0 também encontrado instalado, mas não utilizado nesta rodada. Não foi comprovada equivalência com MySQL 8.0.
- Nenhum processo de banco foi encontrado na inspeção inicial; consulta de serviços do Windows não estava disponível sob as permissões iniciais.
- Materiais sincronizados em sources não foram alterados.

## Verificações executadas

- `dotnet build OdontoCare.slnx`: restauração e compilação dos quatro projetos concluídas, zero erros e zero avisos.
- `php -l`: 47 arquivos, zero falhas.
- Importação de `database/odontocare.sql` e `database/02_migracao_estoque.sql` na instância isolada, sem erros.
- 20 verificações dinâmicas aprovadas, usando o ApiClient.php real e dados sintéticos.

| Cenário | Resultado |
|---|---|
| Pacientes sem autenticação | 401 |
| Login com campos ausentes | 400 |
| Login com senha incorreta | 401 |
| Senha PBKDF2 gerada em PHP autenticada pela API | 200 |
| Listagem de pacientes autenticada | 200 |
| Cadastro de paciente inválido | 400 |
| Cadastro de paciente sintético | 201 |
| CPF duplicado | 409 |
| Leitura do paciente cadastrado | 200 |
| Atualização do paciente | 200 |
| Consulta PDO confirma atualização persistida pela API | Aprovado |
| Paciente inexistente | 404 |
| Administrador cadastra dentista | 201 |
| Login do dentista | 200 |
| Dentista tenta cadastrar usuário | 403 |
| JWT inválido | 401 |
| Cliente PHP remove JWT inválido da sessão | Aprovado |
| Login pelo formulário PHP | 302 para index.php |
| Painel inicial com sessão autenticada | 200, conteúdo esperado |
| Página de pacientes com registro atualizado pela API | 200, conteúdo esperado |

As verificações web foram feitas por HTTP com cookies, sem inspeção visual de navegador. Credenciais e tokens temporários não constam neste relatório nem nas evidências JSON.

## Ocorrências resolvidas no ambiente de teste

- Restrições de rede impediram inicialmente clone, conexão TCP local e execução integrada; ações necessárias foram executadas com autorização do ambiente.
- A primeira configuração do servidor PHP usou caminho relativo para sessões, causando redirecionamento ao login. Foi corrigida para caminho absoluto e a rodada completa passou. Não foi defeito do código da aplicação.
- O PHP legado não possui variável própria para porta do banco. Nesta validação, ODONTOCARE_DB_HOST recebeu host com segmento de porta do DSN; nenhuma mudança no arquivo de conexão foi feita.

## Limites e próximos cenários

Não foram testados: CRUD completo de todos os módulos, exclusão de registros, histórico e concorrência de estoque, concorrência de agendamentos, expiração temporal de JWT, resposta 500 por falha induzida, HTTPS, interface visual, carga, nem implantação. O banco contém apenas dados sintéticos criados nesta rodada. A instância e os servidores temporários são encerrados ao concluir a validação.

Evidências: `validacao-local-2026-11-02.json`. O executor local fica em `.validation/validate.php`, na pasta pai do clone, e cria novos dados sintéticos a cada execução; exige os serviços de teste preparados. Não deve ser executado contra banco real.

## Continuação: estoque, equipamentos e concorrência

Esta seção atualiza os limites da primeira rodada: foram executadas mais 39 verificações, todas aprovadas após a correção descrita abaixo. A compilação da alteração terminou com zero erros e zero avisos.

- Estoque: criação, alerta de saldo baixo, validação de quantidade, entrada, saída, rejeição de saldo insuficiente, filtro/paginação, invalidação do cache, histórico com usuário e bloqueio de exclusão de material com movimentações.
- Concorrência de estoque: 12 saídas simultâneas preservaram saldo não negativo e correspondência entre quantidade e histórico, sem HTTP 500. Na rodada final houve 1 sucesso e 11 conflitos, com saldo final 6 a partir de 7 unidades. Isso valida integridade; não valida aproveitamento máximo do estoque sob concorrência. O cliente pode precisar repetir operações que retornem conflito.
- Equipamentos: criação, atualização, listagem, validação de campos e registro inexistente; dentista impedido de excluir equipamento ou movimentar estoque.
- Agenda: criação, conflito sequencial, expediente, relacionamentos inexistentes, restrição de acesso do dentista, cancelamento preservando histórico, reutilização do horário liberado e atualização com verificação de conflito.

### Falha reproduzida e corrigida

Antes da correção, 12 POST simultâneos para o mesmo paciente/profissional/data/horário geraram 10 respostas 201 e 10 reservas persistidas. A validação consultava disponibilidade antes de gravar, sem proteção conjunta contra concorrência.

`ConsultaService` agora usa uma transação ReadCommitted e bloqueia as linhas de paciente e profissional, em ordem fixa, antes de conferir disponibilidade e salvar, tanto na criação quanto na atualização. Não houve alteração de esquema ou exclusão dos registros usados como evidência.

Após a correção, o cenário concorrente passou nas três rodadas executadas: uma resposta 201, onze respostas 409 e uma única reserva persistida em cada rodada. Os testes sequenciais de atualização e conflito também passaram. Ainda não foram exercitados todos os cruzamentos de criação/atualização/cancelamento concorrentes.

Evidências preservadas:

- `validacao-estendida-antes-2026-11-02.json`: comportamento do commit c5bd0a1 antes da correção, 33 verificações aprovadas e 1 falha.
- `validacao-estendida-depois-2026-11-02.json`: execução final do teste versionável, 39 verificações aprovadas.
- `tools/validar_integracao_estendida.php`: executor PHP/cURL com requisições simultâneas, sem senhas ou tokens fixos. Cria dados sintéticos e exige `ODONTOCARE_VALIDACAO_ISOLADA=1` e as variáveis de conexão; não realiza limpeza destrutiva.

Para repetir o executor, iniciar API e MariaDB isolados, configurar expediente da API para 08:00–18:00 e definir `ODONTOCARE_API_URL`, `ODONTOCARE_DB_HOST`, `ODONTOCARE_DB_NAME`, `ODONTOCARE_DB_USER` e `ODONTOCARE_DB_PASS`. A conexão PHP aceita a porta no host, por exemplo `127.0.0.1;port=33316`. `ODONTOCARE_VALIDACAO_RESULTADO` pode indicar um caminho de saída JSON. Executar `php tools/validar_integracao_estendida.php`.

Continuam pendentes: MySQL 8.0, expiração temporal de JWT, falha 500 induzida, HTTPS, carga, interface visual e validação integral dos módulos legados financeiros/procedimentos. Não houve commit nem push. Serviços temporários encerrados ao finalizar esta rodada.

## Continuação: MySQL 8 e módulos financeiros PHP

O MySQL Community Server 8.0.42 instalado na máquina foi iniciado em uma nova pasta `.validation/mysql8`, fora do repositório, com porta 33318 e escuta em 127.0.0.1. Os bancos da instalação original e a instância MariaDB das etapas anteriores não foram utilizados nem alterados nesta rodada.

O esquema e a migração de estoque foram importados sem erro. A API corrigida foi executada contra o MySQL 8 e **39/39 verificações de integração e concorrência passaram**. O teste de agenda novamente persistiu somente uma reserva entre 12 pedidos simultâneos.

Os módulos financeiros foram exercitados via HTTP com cookies de sessão e autenticação real do PHP/API. **40/40 verificações financeiras passaram na versão corrigida**; **50 arquivos PHP** passaram em `php -l`. O código .NET não recebeu novas alterações nesta etapa.

### Falhas financeiras reproduzidas e corrigidas

- Valores negativos eram aceitos nos três cadastros: procedimento, orçamento e pagamento. Os controladores agora validam valores não negativos no limite de DECIMAL(10,2), com no máximo duas casas decimais e sem notação exponencial.
- Pagamentos com fração de centavo eram arredondados ao gravar. Agora retornam HTTP 400 sem criar registro.
- Data impossível de orçamento e consulta inexistente no pagamento terminavam com HTTP 200 e detalhes da exceção SQL. Agora valores, datas, identificadores, opções e vínculos são conferidos antes de gravar; entradas inválidas recebem HTTP 400 sem inserir registros.
- Os três DAOs financeiros agora devolvem mensagem genérica e HTTP 500 para exceções PDO, sem exibir SQLSTATE ou detalhes do banco ao usuário.
- O recibo ignorava a validade temporal da sessão. Ele agora carrega a verificação compartilhada de permissões; sessão expirada é redirecionada ao login.

A validação foi centralizada em `OdontoCare-main/config/validacaoFinanceira.php`. Ela é aplicada no servidor pelos controladores PHP; não depende das restrições HTML dos formulários. A data do pagamento continua obrigatória, conforme o formulário existente. Valores zero não foram proibidos, pois não havia regra explícita exigindo valor estritamente positivo.

### Cobertura comprovada

- Criação e atualização de procedimentos, orçamentos e pagamentos; centavos persistidos corretamente e valor máximo 99999999.99 aceito sem arredondamento.
- Consulta criada pela API usando procedimento cadastrado no PHP; pagamento vinculado à consulta e orçamento vinculado ao paciente.
- Listagens de procedimentos, orçamentos e pagamentos, recibo com valor brasileiro e redirecionamento para recibo inexistente.
- Valores negativos, excesso de casas decimais, estouro do limite monetário, notação exponencial, texto, campo em formato de array, status inválido, data ausente/impossível e relacionamento inexistente rejeitados.
- Atualização financeira inválida preserva o valor já gravado; dentista não consegue criar pagamento; página de pagamentos exige login.
- Falha de leitura induzida nos três DAOs, retirando temporariamente somente SELECT do usuário `validation_php` criado para este teste. As três respostas foram HTTP 500 genéricas; a permissão foi restaurada em bloco finally e a listagem voltou a HTTP 200. Nenhuma tabela ou registro foi removido.
- Expiração de sessão simulada alterando apenas `expiraEmUtc` da sessão de teste: recibo, painel e pagamentos redirecionaram ao login. Isso verifica a expiração da sessão PHP, não a passagem real do prazo de validade de um JWT na API.

### Evidências e repetição

- `validacao-mysql8-2026-11-02.json`: 39 testes aprovados em MySQL 8.0.42.
- `validacao-financeiro-antes-2026-11-02.json`: primeira rodada, 19 testes aprovados e 6 falhas financeiras reproduzidas.
- `validacao-financeiro-sessao-antes-2026-11-02.json`: após validação de entradas, 33 testes aprovados e 1 falha de sessão no recibo.
- `validacao-financeiro-depois-2026-11-02.json`: rodada final, 40 testes aprovados.
- `tools/validar_financeiro.php`: executor reutilizável, sem credenciais fixas ou tokens no arquivo/evidências; cria dados sintéticos, sem limpeza destrutiva.

Para repetir os testes financeiros, configurar as variáveis de ambiente do executor estendido e também `ODONTOCARE_WEB_URL` para o servidor PHP. `ODONTOCARE_VALIDACAO_RESULTADO` indica o caminho JSON opcional. Executar `php tools/validar_financeiro.php`. Os testes opcionais de sessão usam `ODONTOCARE_VALIDACAO_SESSOES`, apontando para a pasta absoluta de sessões do PHP de teste. A falha controlada de banco exige `ODONTOCARE_VALIDACAO_USUARIO_WEB` com um usuário exclusivo `validation_*` no host 127.0.0.1 e conexão de teste com permissão para revogar/restaurar SELECT em `odontocare`; omitir essa variável fora desse cenário preparado. O PHP web deve ter as variáveis de conexão configuradas e o mesmo endereço de API.

### Limites atuais

Esta rodada cobre os fluxos acima em MySQL 8.0.42 e por HTTP local. Permanecem sem homologação: exclusões, proteção contra CSRF, escape completo de HTML em páginas legadas, expiração real de JWT na API, HTTP 500 induzido na API, HTTPS, inspeção visual, carga e todos os cruzamentos de alterações concorrentes de agenda. Os registros inválidos criados durante a reprodução original foram mantidos somente no banco descartável, como evidência; não houve remoção nem reparo de dados reais. Os serviços de teste foram encerrados ao concluir. Nenhum commit ou push realizado.

## Continuação: segurança web e preparação da demonstração

Esta etapa cobre as exclusões, CSRF, saída de HTML e expiração real de JWT que estavam pendentes acima. Foram aprovadas **147 verificações na rodada final**: 98 de segurança web, 40 de regressão financeira, 3 de expiração do JWT e 6 cenários adicionais de concorrência. Os números representam verificações dos executores, não quantidade de usuários ou testes de carga.

### Alterações implementadas

- `config/seguranca.php` centraliza o escape HTML com UTF-8 e aspas, a serialização JSON segura para scripts, os tokens CSRF e a validação de método/identificador de exclusão.
- Formulários de login, cadastro, edição, exclusão, movimentação de materiais e logout usam token aleatório de 32 bytes, associado à sessão. A comparação usa `hash_equals`; token ausente, falso, em formato de array ou de outra sessão recebe HTTP 403.
- O token CSRF é renovado após o login. O token anterior não autoriza requisições da sessão autenticada.
- Os seis controladores de gestão e o logout aceitam alterações somente por POST. Os antigos GET de exclusão retornam HTTP 405 para o administrador autenticado, sem modificar registros.
- As telas usam formulários POST nos botões de exclusão. Consultas mostram a ação como cancelamento, preservando o histórico.
- Campos e mensagens das páginas PHP usam escape de HTML. Os gráficos usam JSON com flags HEX, em vez de escape HTML dentro do JavaScript, para preservar os dados sem permitir fechamento de script.
- Os três DAOs financeiros carregam explicitamente sua conexão. Isso corrigiu uma dependência ausente detectada ao abrir diretamente o formulário de edição de procedimento.
- Os testes financeiros foram adaptados para obter o token do formulário, manter cookies e acompanhar a renovação do token após o login.

### Resultados comprovados

| Grupo | Resultado e evidência |
|---|---|
| Segurança web | 98/98, `validacao-seguranca-web-2026-11-02.json` |
| Regressão financeira com CSRF | 40/40, `validacao-financeiro-seguranca-2026-11-02.json` |
| Expiração real de JWT | 3/3, `validacao-jwt-expiracao-2026-11-02.json` |
| Cruzamentos de agenda | 6/6, `validacao-concorrencia-agenda-2026-11-02.json` |
| Sintaxe PHP | 54 arquivos, nenhuma falha |
| Compilação .NET final | Zero erros e zero avisos |
| Revisão de whitespace | `git diff --check` sem falhas |

O teste de segurança criou seus próprios registros sintéticos. Para cada controlador verificou que GET, CSRF inválido, visitante e dentista não alteravam o registro. Depois confirmou operações POST autorizadas nos seis módulos, excluindo somente os alvos criados pelo executor; o cancelamento da consulta manteve a linha no histórico. Os demais dados do banco de teste foram preservados. Nenhum banco real foi usado.

A verificação de HTML usou nomes e observações contendo aspas, tags, ampersand e uma sequência de fechamento de script. As 16 páginas exercitadas responderam HTTP 200 sem inserir esse conteúdo como marcação ativa no HTML retornado. Os formulários POST foram inspecionados com DOM para conferir o token. O JSON de rótulos do gráfico foi decodificado para verificar que preserva o texto. Isso é verificação de resposta/estrutura HTML, não execução visual em navegador.

Também foram conferidos os endpoints de leitura de usuários existentes: administrador lista e consulta usuários sem o campo senha; dentista recebe 403 na listagem; usuário inexistente recebe 404. O formulário de edição foi inspecionado para confirmar que carrega o nome do usuário e deixa a senha vazia. Uma duplicação de rotas introduzida durante a revisão foi removida antes da compilação e execução finais; não permaneceu alteração nesses endpoints.

Para expiração, uma segunda instância local da API foi configurada temporariamente com `Jwt:ExpirationMinutes=1`. O mesmo JWT autêntico foi aceito com HTTP 200 antes de vencer e rejeitado com HTTP 401 após o vencimento mais a tolerância de 15 segundos. O cliente PHP removeu o token expirado. O relógio do sistema não foi alterado e os tokens não constam das evidências.

Os cenários concorrentes adicionais confirmaram:

1. Mesmo profissional e pacientes diferentes: uma criação 201 e um conflito 409.
2. Mesmo paciente e profissionais diferentes: uma criação 201 e um conflito 409.
3. Duas edições para o mesmo horário: uma atualização 200 e um conflito 409.
4. Criação disputando horário com edição: uma operação aprovada e um conflito, com uma reserva ativa persistida.
5. Cancelamento disputando horário com nova reserva: cancelamento preservado, sem duplicidade ativa. Na rodada observada, a nova reserva recebeu 409.
6. Profissionais e pacientes distintos no mesmo horário: ambas as criações receberam 201.

Esses cenários não esgotam todas as interleavings possíveis nem equivalem a um teste de carga.

### Entrega e repetição

Os executores `tools/validar_seguranca_web.php`, `tools/validar_expiracao_jwt.php` e `tools/validar_concorrencia_agenda.php` foram incluídos para repetir as verificações em ambiente isolado. Eles exigem a marcação de validação e as variáveis de banco/API; os testes web também exigem `ODONTOCARE_WEB_URL`. O executor de JWT deve apontar para uma API separada configurada com validade de um minuto.

O documento `EXECUCAO_E_DEMONSTRACAO.md` reúne pré-requisitos, comandos de execução, configuração por ambiente e roteiro da apresentação. README e estado da implementação foram atualizados para apontar aos resultados atuais.

Permanecem pendentes: inspeção visual de navegador, teste de carga, HTTP 500 induzido na API, configuração HTTPS/publicação e revisão de segurança completa para produção. O projeto continua acadêmico e tem módulos PHP/PDO legados. Os serviços temporários foram encerrados; nenhum commit ou push foi executado. As mudanças estão disponíveis para revisão local.


## Revisão contra planejamento e especificação do professor — 02/11/2026

Foram executadas mais 31 verificações em ambiente isolado MySQL 8.0.42, todas aprovadas: 24 em `validacao-aceite-daniel-2026-11-02.json` e sete em `validacao-cache-2026-11-02.json`.

A primeira suíte verifica os três perfis, cadastro e hashes com salt, permissões e envelopes HTTP 200/201/400/401/403/404/405/409/500. O erro 500 foi induzido em uma API separada apontando para banco indisponível e não expôs conexão ou stack. Isso resolve a pendência histórica específica de teste de HTTP 500 listada acima.

A segunda comprova fonte original/MISS, memória/HIT, invalidação por edição e movimentação, expiração deslizante após inatividade e absoluta após aproximadamente 123 segundos mesmo com acessos intermediários. Os executores são `tools/validar_aceite_daniel.php` e `tools/validar_cache.php`; exigem ambiente isolado e configuração por variáveis, sem credenciais nos resultados.

O [checklist acadêmico](CHECKLIST_ACADEMICO_DANIEL.md) registra os limites: integração PDO parcial, revisão arquitetural, README definitivo, Docker no estudo autônomo e fechamento coletivo. Não houve commit ou push nesta revisão.


## Fechamento para passagem ao próximo integrante — 02/11/2026

As pendências anteriores de integração PHP/PDO e distribuição dos casos de uso entre camadas foram tratadas. Todos os fluxos existentes do cliente agora utilizam a API, incluindo financeiro, recibo e painel. O cliente foi testado com conexão de banco inválida e continuou funcionando pela API.

A rodada final totalizou **282 verificações aprovadas**; a solução compilou em Debug/Release sem avisos nem erros e os 58 arquivos PHP passaram na análise de sintaxe. Resultados completos, limites e comandos em [ENTREGA_DANIEL.md](ENTREGA_DANIEL.md). Docker foi preparado e verificado estaticamente, mas não executado. A passagem para Glaucio/Adla está em [PASSAGEM_PARA_EQUIPE.md](PASSAGEM_PARA_EQUIPE.md). Commit e push permanecem sujeitos à autorização.
