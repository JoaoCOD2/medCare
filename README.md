# MedCare — plataforma de consultas médicas

Protótipo funcional de agendamento de consultas (PHP 8 + MySQL), com identidade visual própria.

> **Status:** Fase 1 concluída (frontend + núcleo + dados de demonstração). O MySQL entra na Fase 2/3 **sem alterar páginas nem serviços**.

## Requisitos
- PHP 8.1+ com as extensões `mbstring`, `json` e `session` (e `pdo_mysql` a partir da Fase 3)
- MySQL 8+ (somente a partir da Fase 2)

## Como rodar
```bash
php -S localhost:8000 -t public
```
Abra http://localhost:8000. A pasta **`public/` é a raiz web** — `src/`, `config/` e `.env` ficam fora do alcance do navegador.

Em Apache/Nginx, aponte o DocumentRoot para `medcare/public`. Se instalar em subpasta, defina `APP_BASE_URL` no `.env`.

## Arquitetura (camadas)
```
public/pages, public/api   → apresentação (HTML/JSON, sem regra de negócio, sem SQL)
src/Services               → regras de negócio (AgendamentoService, BuscaService)
src/Repositories/Contracts → interfaces (o "contrato" com o banco)
src/Repositories/Demo      → implementação com dados fictícios  (Fase 1)
src/Repositories/Pdo       → implementação MySQL/PDO            (Fase 3)
src/Core                   → Container, Session, Csrf, Auth, Env
includes/                  → header, navbar, footer e partials de view
```

### Como trocar os dados fictícios pelo MySQL
1. Rode `database/database.sql` (Fase 2).
2. Crie em `src/Repositories/Pdo/` as classes `MedicoRepository`, `EspecialidadeRepository`, `HorarioRepository` e `ConsultaRepository`, implementando as interfaces de `Contracts/`.
3. Em `src/Core/Container.php`, troque os casos `pdoIndisponivel(...)` pelas novas classes.
4. No `.env`: `DATA_DRIVER=pdo` e as credenciais `DB_*`.
5. Apague `src/Demo/` e `src/Repositories/Demo/` (e a leitura de `$_SESSION['demo_*']`).

Páginas, API e serviços **não mudam**.

## O que já funciona (modo demonstração)
- Home completa, especialidades, busca com filtros (nome, especialidade, cidade, bairro, atendimento, data)
- Perfil do médico com agenda por dia (horários ocupados desabilitados)
- Assistente de agendamento em 5 etapas, com atalhos (`?medico_id=` e `?horario=`)
- Número da consulta (`MC-AAAAMMDD-NNN`), tela de sucesso, minhas consultas e cancelamento
- Proteções já ativas: CSRF em todo POST, escape de saída (`e()`), validação no servidor, sessão `HttpOnly`/`SameSite`
- Regras: não agenda horário ocupado, não agenda modalidade que o médico não atende, não cancela consulta concluída

## Limitações da Fase 1 (de propósito)
- Login e cadastro têm interface e validação no navegador; a gravação real chega na **Fase 4** (`password_hash`/`password_verify`, CPF/e-mail únicos).
- Consultas ficam na **sessão PHP** só para demonstrar o fluxo; nada é persistido.
- Paciente fixo de demonstração (`Auth::pacienteAtual()`).
- Sem JavaScript em `localStorage` — o estado do assistente vive só em memória.

## Roadmap
| Fase | Entrega |
|---|---|
| 1 | Frontend, núcleo, dados de demonstração ✅ |
| 2 | `database.sql` + `seed.sql` |
| 3 | Conexão PDO + repositórios MySQL |
| 4 | Cadastro e login, guards de perfil |
| 5–6 | Busca e agendamento transacionais (`FOR UPDATE` + índice único) |
| 7–9 | Dashboards do paciente, do médico e painel admin (CRUDs) |
| 10 | Segurança, responsividade e refinamento |

## Antes de publicar no GitHub
- O `.env` já está no `.gitignore`; versione apenas o `.env.example`.
- Nunca suba credenciais de banco.

Funcionalidades
Página inicial com especialidades e médicos em destaque
Busca de médicos por nome, especialidade, cidade, bairro, tipo de atendimento e data
Perfil do médico com agenda por dia (horários ocupados ficam desabilitados)
Assistente de agendamento em etapas, com atalhos ?medico_id= e ?horario=
Cadastro e login de pacientes (senha com password_hash / password_verify)
Número único de consulta no formato MC-AAAAMMDD-XXXXXXXX
Tela de sucesso, listagem de Minhas consultas e cancelamento
Modo de demonstração (sem banco) e modo MySQL, trocáveis por configuração
Regras de negócio
Não é possível agendar um horário já ocupado
Não é possível agendar uma modalidade que o médico não atende (presencial, teleconsulta ou ambos)
Consulta concluída não pode ser cancelada
Ao cancelar, o horário volta a ficar livre
A reserva usa transação com SELECT ... FOR UPDATE para evitar agendamento duplo
Segurança
Token CSRF em todo POST (cabeçalho X-CSRF-Token ou campo _csrf)
Escape de saída com e() (proteção contra XSS)
Consultas SQL com prepared statements
Sessão com cookie HttpOnly, SameSite=Lax, modo estrito e expiração por 30 min de inatividade
src/, config/ e .env ficam fora da raiz web
Requisitos
PHP 8.1 ou superior com as extensões mbstring, json, session e pdo_mysql
MySQL 8 (ou MariaDB equivalente), apenas para o modo pdo
Instalação
1. Obter o projeto
bash
git clone <url-do-repositorio> medCare
cd medCare
2. Configurar o ambiente

Crie um arquivo .env na raiz do projeto (ele já está no .gitignore):

ini
APP_NAME=MedCare
APP_ENV=local
APP_BASE_URL=

# pdo = MySQL | demo = dados fictícios na sessão (sem banco)
DATA_DRIVER=pdo

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=medcare
DB_USER=root
DB_PASS=

Se o projeto rodar em uma subpasta (ex.: XAMPP), defina APP_BASE_URL, por exemplo /medCare/public.

3. Iniciar o servidor MySQL

O banco só aceita conexões se o servidor MySQL estiver rodando. Escolha uma das opções:

XAMPP: abra o painel de controle e clique em Start na linha do MySQL.
MySQL instalado no Windows: abra o terminal como administrador e rode net start MySQL80 (o nome do serviço pode variar).
Linux: sudo systemctl start mysql
macOS (Homebrew): brew services start mysql
Docker:
bash
  docker run --name medcare-mysql -e MYSQL_ALLOW_EMPTY_PASSWORD=yes \
    -p 3306:3306 -d mysql:8
4. Criar o banco (modo MySQL)
Opção A: extensão MySQL do VS Code (recomendada)

Usamos a extensão MySQL, de Jun Han (ID formulahendry.vscode-mysql), que permite conectar ao banco e executar SQL sem sair do editor.

Instalar a extensão

No VS Code, abra a aba de extensões (Ctrl+Shift+X).
Pesquise por MySQL e escolha a de autoria Jun Han (descrição: MySQL management tool).
Clique em Install.

Ou pelo terminal:

bash
code --install-extension formulahendry.vscode-mysql

Conectar ao servidor

Abra o painel MySQL na barra lateral (Explorer) e clique em + (Add Connection).
Preencha, na ordem em que o VS Code perguntar:
Host: 127.0.0.1
Usuário: root (ou o seu usuário)
Senha: a senha do MySQL (deixe em branco se não houver, como no XAMPP padrão)
Porta: 3306
Certificado SSL: deixe em branco (Enter)
A conexão aparece no painel. Clique nela para listar os bancos.

Executar o script do projeto

Abra database/database.sql no VS Code.
Clique com o botão direito no editor e escolha Run MySQL Query (atalho Ctrl+Alt+E). Se houver mais de uma conexão, selecione a que você criou.
O resultado aparece em um painel ao lado. O script cria o banco medcare, as 5 tabelas e os dados de exemplo (3 especialidades, 12 médicos e horários de segunda a sexta para os próximos 30 dias, a partir do dia em que o script for executado).
Atualize o painel MySQL e confira se o banco medcare tem as tabelas pacientes, especialidades, medicos, horarios e consultas.

Erro ER_NOT_SUPPORTED_AUTH_MODE no MySQL 8? A extensão não suporta o método de autenticação padrão do MySQL 8. Em um terminal do MySQL, rode:

sql
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'sua_senha';
FLUSH PRIVILEGES;

Use a mesma senha no .env e na conexão da extensão.

Opção B: linha de comando
bash
mysql -u root -p < database/database.sql
Opção C: phpMyAdmin

Crie o banco medcare (utf8mb4_unicode_ci), abra a aba Importar e envie database/database.sql.

O script cria as 5 tabelas e popula 3 especialidades e 12 médicos de exemplo. Ele é seguro para rodar mais de uma vez.

Importante: o cadastro (public/api/cadastro.php) grava as colunas nascimento e telefone, que não existem em database.sql. Antes de usar o cadastro, execute:

sql
ALTER TABLE pacientes
  ADD COLUMN nascimento DATE NULL AFTER email,
  ADD COLUMN telefone VARCHAR(20) NULL AFTER nascimento;
5. Rodar
bash
php -S localhost:8000 -t public

Acesse http://localhost:8000. A pasta public/ é a raiz web.

Em Apache/Nginx, aponte o DocumentRoot para medCare/public.

6. Testar a configuração
bash
php teste_container.php   # mostra qual driver e quais repositórios estão ativos
php teste_db.php          # testa a conexão com o MySQL
Estrutura do projeto
medCare/
├── config/                 Configurações (app.php, database.php) lidas do .env
├── database/database.sql   Estrutura + dados de exemplo
├── includes/               Header, navbar, footer e partials de view
├── public/                 Raiz web
│   ├── api/                Endpoints JSON (login, cadastro, agendamento, ...)
│   ├── pages/              Páginas PHP renderizadas no servidor
│   ├── assets/css, js      Estilo e scripts
│   ├── components/         Fragmentos HTML reutilizáveis
│   └── *.html              Versões estáticas das telas
├── src/
│   ├── Core/               Container, Session, Csrf, Auth, Env
│   ├── Helpers/            Funções globais (e(), url(), formatação de datas)
│   ├── Services/           Regras de negócio (AgendamentoService, BuscaService)
│   ├── Repositories/
│   │   ├── Contracts/      Interfaces
│   │   ├── Demo/           Implementação com dados fictícios
│   │   └── Pdo/            Implementação MySQL
│   ├── Demo/DemoData.php   Dados fictícios do modo demonstração
│   └── bootstrap.php       Autoload PSR-4, helpers, fuso e sessão
└── storage/                Arquivos gerados em tempo de execução
Arquitetura em camadas
public/pages, public/api   → apresentação (HTML/JSON, sem regra de negócio)
src/Services               → regras de negócio (AgendamentoService, BuscaService)
src/Repositories/Contracts → interfaces (o "contrato" com o banco)
src/Repositories/Demo      → implementação com dados fictícios
src/Repositories/Pdo       → implementação MySQL/PDO
src/Core                   → Container, Session, Csrf, Auth, Env
includes/                  → header, navbar, footer e partials de view

O Container escolhe a implementação dos repositórios conforme DATA_DRIVER (demo ou pdo). Por isso páginas e serviços não mudam ao trocar um pelo outro. Para trocar, basta ajustar o .env.

Modo demonstração (sem banco)

Com DATA_DRIVER=demo o projeto usa os dados fictícios de src/Demo/DemoData.php, sem precisar de MySQL. Serve para ver as telas e o fluxo de agendamento: as consultas ficam apenas na sessão PHP ($_SESSION['demo_*']) e nada é persistido. Cadastro e login usam o MySQL, então para testá-los é necessário o banco configurado.

Banco de dados
especialidades 1 ── N medicos 1 ── N horarios
                       │               │
pacientes 1 ───── N consultas ─────────┘
Tabela	Descrição
pacientes	Nome, CPF e e-mail únicos, hash da senha
especialidades	Nome e ícone (Font Awesome)
medicos	Nome, CRM único, especialidade, cidade, bairro, tipo de atendimento
horarios	Agenda do médico (data, hora, ocupado), único por médico + data + hora
consultas	Número da consulta, paciente, médico, horário, modalidade e status (agendado, concluido, cancelado)
API
Endpoint	Método	Função
api/login.php	POST	Login do paciente
api/cadastro.php	POST	Cadastro de paciente
api/sair.php	POST	Logout
api/medicos.php	GET	Lista/busca médicos
api/filtros.php	GET	Dados dos filtros (especialidades, cidades)
api/horarios.php	GET	Horários disponíveis de um médico
api/agendamento.php	POST (JSON + CSRF)	Confirma uma consulta
api/consultas.php	GET	Consultas do paciente (ou uma por ?numero=)
api/cancelar_consulta.php	POST (JSON + CSRF)	Cancela uma consulta
api/contexto.php	GET	Contexto da sessão/usuário

Exemplo de agendamento:

json
POST /api/agendamento.php
X-CSRF-Token: <token>

{ "medico_id": 1, "horario_id": "12", "tipo": "presencial" }
Pontos de atenção conhecidos

Itens encontrados na leitura do código que valem ser corrigidos:

Banco e .env no Container: Container.php lê config('db_host'), config('db_name') etc., mas config/app.php não define essas chaves. Com DATA_DRIVER=pdo, os repositórios podem ignorar o .env e usar os valores padrão (root, sem senha). A solução é ler config/database.php no Container.
Dois logins com sessões diferentes: pages/login.php grava $_SESSION['usuario'] (lido por Auth), enquanto api/login.php grava usuario_id, usuario_nome e usuario_tipo. O login pela API não autentica nas páginas e endpoints que usam Auth::pacienteAtual().
Colunas ausentes: nascimento e telefone (veja a seção de instalação).
Mensagens de erro do banco: api/cadastro.php devolve $e->getMessage() ao cliente. Em produção, registre em log e mostre uma mensagem genérica.
Status divergentes: o banco usa agendado/concluido/cancelado, mas helpers e demo usam confirmada/aguardando/concluida/cancelada.
Validação do cadastro: o servidor não valida formato de e-mail, CPF nem força da senha, e api/cadastro.php e api/login.php não exigem token CSRF.
Arquivos para limpar: public/assets/js/forms.js.bak, teste_container.php e teste_db.php (não devem ir para produção) e páginas duplicadas em .html e .php.
Autenticação nos endpoints: api/consultas.php e api/cancelar_consulta.php assumem paciente logado; sem login, acessam ['id'] de null.
Roadmap
Fase	Entrega	Status
1	Frontend, núcleo e dados de demonstração	✅
2	database.sql com estrutura e seed	✅
3	Conexão PDO + repositórios MySQL	✅
4	Cadastro e login, guards de perfil	Parcial (cadastro e login feitos; faltam guards de perfil)
5–6	Busca e agendamento transacionais (FOR UPDATE + índice único)	✅
7–9	Dashboards do paciente, do médico e painel admin (CRUDs)	Pendente
10	Segurança, responsividade e refinamento	Pendente
Antes de publicar no GitHub
O .env já está no .gitignore. Versione apenas um .env.example (o modelo da seção de instalação, sem credenciais reais).
Nunca suba credenciais de banco.
Remova arquivos de desenvolvimento: public/assets/js/forms.js.bak, teste_container.php e teste_db.php.
Licença

Defina a licença do projeto (por exemplo MIT) antes de publicar.
