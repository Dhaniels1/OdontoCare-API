# Passagem de Daniel para a equipe

Data: 02/11/2026. Esta pasta é a versão para revisão do próximo integrante. Não contém banco, contas de demonstração, senhas, tokens ou pastas de compilação. As credenciais da demonstração na máquina de Daniel não devem ser enviadas junto.

## Para quem passar

**Glaucio** pode começar pela documentação: conferir nomes completos, identificação acadêmica, instruções e adequação da apresentação ao professor. **Adla** pode iniciar em seguida (ou em paralelo pela equipe) a homologação visual e o ensaio. Bruna revisa os fluxos de pacientes e consultas junto com Daniel.

Nenhuma mensagem foi enviada automaticamente a integrantes. Esta versão foi autorizada para publicação no GitHub na branch `main`; antes de iniciar novas alterações, atualize o clone local com `git pull`.

## O que está pronto para revisão

- Cliente PHP integrado à API em todos os fluxos presentes, incluindo financeiro, recibo e painel.
- Casos de uso em Application; EF, consultas de leitura e bloqueios em Infrastructure; verificador das fronteiras de dependência.
- Login JWT, PBKDF2 com salt, permissões, respostas padronizadas, CSRF e escape de HTML.
- Agenda com bloqueios para evitar reservas concorrentes conflitantes.
- Estoque com histórico, saldo protegido, alerta e cache real com invalidação/expiração.
- README ampliado, documentação de execução, scripts Docker e testes repetíveis.

Consultar [ENTREGA_DANIEL.md](ENTREGA_DANIEL.md) para os números finais e limitações. Os relatórios anteriores são históricos; falhas antigas não descrevem automaticamente o estado desta entrega.

## Sequência do próximo integrante

1. Ler README e escolher execução nativa (validada) ou Docker (configuração preparada; engine ainda não validado).
2. Preparar banco vazio com os dois scripts e criar sua própria conta pelo utilitário `tools/criar_admin.php`. Nunca reutilizar senha de outra pessoa.
3. Executar o roteiro de [EXECUCAO_E_DEMONSTRACAO.md](EXECUCAO_E_DEMONSTRACAO.md): paciente → procedimento → consulta → orçamento/pagamento → recibo → estoque/cache.
4. Conferir as telas em computador e celular: textos, formulários, tabelas, alertas, recibo, navegação e saída.
5. Registrar data, versões, resultado e qualquer problema reproduzível. Para um problema, anotar tela, ação, resultado esperado e recebido, sem capturar credenciais.
6. Ensaiar a explicação de cada camada, token, perfis, ResultWrapper, transação e HIT/MISS/invalidação. Todos precisam conhecer o funcionamento geral.
7. Devolver a Daniel os ajustes encontrados. Depois da revisão, autorizar e conferir publicação no repositório oficial.

## Pendências de homologação, sem ocultar limites

- Executar Docker em máquina com Docker e preencher o aceite de DOCKER.md.
- Conferência visual humana e demonstração completa pelo grupo.
- Nomes completos/matrículas, prazo e formato final de envio conferidos pelo grupo.
- Aprovação de commits/pushes e confirmação de CI na versão publicada.

O layout escolhido “Agenda em destaque”, com sálvia/bege/bordô, continua opcional. Não foi aplicado nesta rodada para manter o foco no fechamento funcional e evitar uma nova mudança visual antes da homologação.

## Atenção ao pacote e à demonstração local

A pasta `OdontoCare-Entrega-Daniel` e o ZIP correspondente são o material de passagem. A cópia anterior `Downloads/OdontoCare-API` foi preservada para não interromper a demonstração que estava aberta; não usar essa cópia anterior como fonte da entrega final. O clone de trabalho do Codex permanece com as alterações sem commit.
