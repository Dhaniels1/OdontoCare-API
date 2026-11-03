# Checklist acadêmico — fechamento de Daniel

Atualização: 02/11/2026, após integração final. Fontes de escopo: especificação do professor (seções 3–10 e tabela de avaliação) e planejamento de divisão de responsabilidades (quarta página do PDF: Daniel). Os PDFs originais fornecidos pelo usuário foram preservados em Downloads.

## Responsabilidades de Daniel

| Item | Estado nesta entrega | Evidência / limite |
|---|---|---|
| 1. Arquitetura | Revisada e reorganizada | Casos de uso em Application; repositórios/transações em Infrastructure. Verificador de dependências e compilação aprovados. |
| 2. ResultWrapper e erros | Implementado e testado | Sucessos e erros 400/401/403/404/405/409/500; falha de banco induzida sem detalhes internos no cliente. |
| 3. Cadastro e senhas | Implementado e testado | PBKDF2 e salt individual, sem senha nas respostas. |
| 4. Login JWT | Implementado e testado | Assinatura, perfis, acesso protegido e expiração real. |
| 5. Perfis | Implementado e testado | Administrador, Secretária e Dentista; verificações positivas e negativas. |
| 6. Estoque/alerta | Implementado e testado | CRUD, histórico, concorrência, bloqueio de saldo negativo e limite do estoque mínimo. |
| 7. Cache | Implementado e testado | Fonte/MISS, memória/HIT, invalidação e dois prazos reais. |
| 8. Integração PHP/API | Concluída para os fluxos existentes | Financeiro, procedimentos, recibo, painel e seletores migrados. PHP web testado com banco inacessível. |
| 9. Revisão final e entrega | Pronta para homologação do grupo | Testes locais aprovados; commits/pushes aguardam autorização. CI remoto não foi executado nesta rodada. |

Detalhes e resultados: [ENTREGA_DANIEL.md](ENTREGA_DANIEL.md).

## Critérios do professor

| Critério | Peso | Evidência local |
|---|---:|---|
| Arquitetura limpa | 20% | Quatro camadas, inversão de dependências e verificador estático. |
| API e funcionalidades | 20% | Cadastros, agenda, estoque, equipamentos e financeiro via endpoints. |
| JWT | 15% | Cadastro/login, hashes, expiração e perfis verificados. |
| Cache | 15% | Uso real com logs e expiração observada. |
| ResultWrapper | 10% | Contrato consistente nos status verificados. |
| Integração | 10% | Cliente sem SQL/PDO ou credenciais de banco. |
| Documentação/qualidade | 10% | README e roteiros preenchidos; revisão final de nomes e reprodução pelo grupo pendentes. |

Os pesos não representam uma nota atribuída ao projeto. Aprovação acadêmica depende da avaliação e apresentação ao professor.

## O que segue com a equipe

- Glaucio: conferir README, nomes completos/matrículas e reprodução; conteúdo técnico já preparado.
- Adla: homologar visualmente e conduzir o roteiro de teste/demonstração; evidências automatizadas já disponíveis.
- Bruna/Daniel: revisar o resultado conjunto de pacientes, consultas e integração, corrigindo eventuais apontamentos.
- Todos: explicar decisões, executar o projeto e validar o pacote entregue.
- Docker: consta no estudo autônomo do professor; arquivos preparados e sintaxe conferida, mas contêineres não executados porque Docker não estava instalado. Seguir [DOCKER.md](DOCKER.md).
- Git: revisar o diff, obter autorização de Daniel para commits/pushes e verificar o workflow publicado.

O novo layout é opcional e não bloqueia a passagem. A entrega técnica está pronta para revisão do próximo integrante, sem declarar encerrada a homologação coletiva.
