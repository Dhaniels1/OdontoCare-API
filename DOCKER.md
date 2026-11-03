# Execução acadêmica com Docker

Configuração preparada para estudo e reprodução local. **Docker não estava instalado na máquina de validação; build e execução dos contêineres ainda precisam ser verificados pelo próximo integrante.** A execução nativa (.NET/PHP/MySQL) foi testada separadamente. Não confundir as duas evidências.

Pré-requisitos: Docker Engine/Desktop em execução, suporte a contêineres Linux e Docker Compose. Na raiz do projeto, em PowerShell:

```powershell
./tools/preparar_docker.ps1
docker compose config --quiet
docker compose up -d --build
docker compose --profile setup run --rm --build setup
```

O primeiro comando cria `.env` sem sobrescrever arquivo existente e solicita a senha inicial (mínimo 12 caracteres). Não compartilhe esse arquivo. O usuário inicial é `admin`. A ferramenta de criação recusa uma segunda conta inicial quando já há administrador.

Abra http://127.0.0.1:58084/login.php. OpenAPI: http://127.0.0.1:5244/openapi/v1.json. O PHP web recebe apenas a URL da API, sem credenciais ou extensão MySQL. Somente o utilitário CLI de primeiro acesso recebe conexão com banco.

O banco utiliza volume persistente e importa os dois scripts apenas na primeira inicialização de um volume vazio. As portas ficam restritas a localhost, e o banco não publica porta no computador. Os endpoints HTTP são para demonstração local; publicação externa exige configurar HTTPS e revisar a implantação.

Para acompanhar ou parar sem apagar dados:

```powershell
docker compose ps
docker compose logs --tail 60 api web
docker compose stop
```

Não execute limpeza de volumes para resolver um erro sem antes avaliar os dados. Mudanças em `.env` não trocam automaticamente as senhas de um banco já inicializado.

## Aceite do integrante que validar Docker

1. Confirmar a sintaxe com `config --quiet` (sem imprimir a configuração resolvida com segredos).
2. Construir imagens e confirmar banco saudável e API acessível.
3. Criar administrador pelo serviço `setup` e entrar pelo PHP.
4. Cadastrar paciente, procedimento, consulta e pagamento; abrir recibo.
5. Demonstrar material, entrada/saída e logs HIT/MISS/INVALIDADO.
6. Parar e iniciar; conferir que os dados persistem. Registrar versão do Docker e resultado.

Referências de implementação: [ordem de inicialização e healthcheck](https://docs.docker.com/compose/how-tos/startup-order/), [variáveis obrigatórias no Compose](https://docs.docker.com/reference/compose-file/interpolation/) e [imagens ASP.NET Core](https://learn.microsoft.com/en-us/aspnet/core/host-and-deploy/docker/building-net-docker-images?view=aspnetcore-10.0).
