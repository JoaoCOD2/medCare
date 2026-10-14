MedCare — Plataforma de consultas médicas
Plataforma de agendamento de consultas desenvolvida em PHP 8 e MySQL, com interface própria, busca de médicos e gerenciamento de consultas.
1. Requisitos
- PHP 8.1 ou superior.
- Extensões PHP: mbstring, json, session e pdo_mysql.
- MySQL 8 ou MariaDB compatível para usar o modo com banco de dados.
- Composer, se o projeto utilizar dependências instaladas por ele.
- VS Code (opcional, recomendado para desenvolvimento).
2. Funcionalidades
- Página inicial com especialidades e médicos em destaque.
- Busca de médicos por nome, especialidade, cidade, bairro, modalidade e data.
- Perfil do médico com agenda diária e horários indisponíveis desabilitados.
- Fluxo de agendamento em etapas.
- Cadastro, login e saída de pacientes.
- Número identificador de consulta.
- Listagem e cancelamento de consultas.
- Modo de demonstração e modo MySQL, selecionados pela configuração.
- Regras para impedir reservas duplicadas e modalidades não atendidas pelo médico.
- Bloqueio do cancelamento de consultas concluídas.
Atenção: as funcionalidades disponíveis dependem da configuração atual do projeto. Consulte a seção “Limitações e pontos de atenção” antes de considerar o sistema pronto para produção.

3. Como iniciar o projeto
Abra o PowerShell na pasta raiz do projeto (medCare) e execute:
php -S localhost:8000 -t public
Depois, acesse http://localhost:8000.
A pasta public/ é a raiz pública do site. Mantenha arquivos como src/, config/ e .env fora dela.
Para encerrar o servidor, volte ao terminal e pressione Ctrl+C. Fechar a aba do navegador não apaga o projeto nem desfaz as alterações nos arquivos.
4. Configurar o ambiente (.env)
Crie um arquivo chamado .env na raiz do projeto, ao lado das pastas public/ e src/. Use os valores abaixo como modelo e ajuste-os à sua instalação:
APP_NAME=MedCare
APP_ENV=local
APP_BASE_URL=

# Use "demo" para dados de demonstração ou "pdo" para MySQL.
DATA_DRIVER=pdo

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=medcare
DB_USER=root
DB_PASS=
- No XAMPP padrão, o usuário costuma ser root e a senha pode estar vazia; confirme a configuração do seu ambiente.
- Não publique o arquivo .env nem compartilhe senhas reais.
- Se o projeto estiver em uma subpasta, configure APP_BASE_URL conforme a instalação.
5. Iniciar e preparar o MySQL
O servidor MySQL precisa estar em execução antes de o PHP tentar conectar-se ao banco.
Se estiver usando XAMPP:
1. Abra o painel de controle do XAMPP.
2. Clique em Start na linha do MySQL.
3. Confirme que o serviço ficou ativo.
Criar as tabelas e os dados de exemplo
Escolha apenas uma das opções abaixo para executar database/database.sql.
Opção A — VS Code
1. Instale uma extensão de gerenciamento MySQL compatível com sua versão do VS Code.
2. Crie uma conexão com o servidor usando o host 127.0.0.1, a porta 3306 e seu usuário e senha do MySQL.
3. Abra database/database.sql.
4. Execute o script usando o comando oferecido pela extensão.
5. Atualize a conexão e confira se o banco medcare contém as tabelas esperadas.
Os nomes e atalhos dos comandos podem variar conforme a extensão instalada.
Opção B — Terminal MySQL
Se o cliente mysql estiver instalado e disponível no PATH, execute no PowerShell:
mysql -u root -p < database/database.sql
Digite a senha quando solicitado. Se o usuário não tiver senha, siga o procedimento adequado à configuração local.
Opção C — phpMyAdmin
1. Abra o phpMyAdmin.
2. Selecione ou crie o banco medcare.
3. Abra a opção Importar.
4. Selecione o arquivo database/database.sql e inicie a importação.
O script do projeto deve ser a referência para confirmar os nomes reais das tabelas e colunas.
6. Colunas adicionais para cadastro
A versão do cadastro descrita no projeto utiliza as colunas nascimento e telefone na tabela pacientes. Se essas colunas ainda não existirem no seu banco, execute o comando abaixo uma única vez, depois de confirmar a estrutura atual:
ALTER TABLE pacientes
  ADD COLUMN nascimento DATE NULL,
  ADD COLUMN telefone VARCHAR(20) NULL;
Se o MySQL informar que uma coluna já existe, não execute novamente sem antes verificar a estrutura da tabela.
7. Testar a aplicação
Com o MySQL ativo e o arquivo .env configurado, abra o terminal na raiz do projeto.
Para iniciar o servidor web:
php -S localhost:8000 -t public
Para testar a conexão e o container, caso esses scripts ainda existam no projeto:
php teste_db.php
php teste_container.php
Acesse http://localhost:8000 e teste as páginas e os fluxos principais. Se aparecer um erro, verifique a mensagem no terminal do PHP e o log do MySQL antes de alterar vários arquivos ao mesmo tempo.
8. Estrutura do projeto
medCare/
├── config/                  # Configurações da aplicação e do banco
├── database/
│   └── database.sql         # Estrutura e dados iniciais
├── includes/                # Partes reutilizáveis das páginas
├── public/                  # Raiz pública do site
│   ├── api/                 # Endpoints da API
│   ├── pages/               # Páginas PHP
│   ├── assets/
│   │   ├── css/
│   │   └── js/
│   └── components/          # Componentes reutilizáveis
├── src/
│   ├── Core/                # Sessão, autenticação, CSRF e configuração
│   ├── Helpers/             # Funções auxiliares
│   ├── Services/            # Regras de negócio
│   └── Repositories/
│       ├── Contracts/       # Interfaces dos repositórios
│       ├── Demo/            # Dados de demonstração
│       └── Pdo/              # Acesso ao MySQL via PDO
├── storage/                 # Arquivos gerados em execução
├── .env                     # Configurações locais; não publicar
└── .env.example             # Modelo de configuração sem segredos
9. Arquitetura
A aplicação separa a apresentação, as regras de negócio e o acesso aos dados:
- public/pages e public/api: páginas e endpoints, sem concentrar regras de negócio ou SQL.
- src/Services: regras de negócio, como busca e agendamento.
- src/Repositories/Contracts: contratos dos repositórios.
- src/Repositories/Demo: implementação com dados de demonstração.
- src/Repositories/Pdo: implementação com MySQL/PDO.
- src/Core: componentes centrais, como container, sessão, CSRF, autenticação e ambiente.
- includes/: cabeçalho, navegação, rodapé e trechos reutilizáveis de interface.
O Container deve selecionar a implementação dos repositórios com base em DATA_DRIVER. Confirme no código que a configuração está sendo lida corretamente antes de alternar entre demo e pdo.
10. Banco de dados e relacionamentos
O banco do projeto contempla estas entidades:
Tabela	Finalidade
pacientes	Dados dos pacientes, CPF, e-mail e hash da senha
especialidades	Nome e ícone da especialidade
medicos	Dados profissionais, CRM, especialidade e informações de atendimento
horarios	Agenda, data, hora e disponibilidade do médico
consultas	Paciente, médico, horário, modalidade e status da consulta


Relacionamentos principais:
- Uma especialidade pode estar associada a vários médicos.
- Um médico pode possuir vários horários.
- Um paciente pode ter várias consultas.
- Cada consulta referencia um médico e um horário.
Os nomes e as restrições exatos devem ser conferidos em database/database.sql.
11. Endpoints da API
Endpoint	Método	Função
api/login.php	POST	Autenticação do paciente
api/cadastro.php	POST	Cadastro do paciente
api/sair.php	POST	Encerramento da sessão
api/medicos.php	GET	Lista e busca médicos
api/filtros.php	GET	Dados para os filtros
api/horarios.php	GET	Horários de um médico
api/agendamento.php	POST	Confirmação de consulta
api/consultas.php	GET	Consultas do paciente
api/cancelar_consulta.php	POST	Cancelamento de consulta
api/contexto.php	GET	Contexto da sessão e do usuário


Confirme os caminhos, métodos e campos diretamente nos arquivos atuais da API, pois eles podem mudar durante o desenvolvimento.
12. Segurança
O projeto prevê mecanismos importantes de segurança:
- Uso de consultas preparadas com PDO.
- Escape de saída com e() para reduzir riscos de XSS.
- Proteção CSRF nas operações que alteram dados.
- Cookies de sessão com configurações de segurança.
- Arquivos de configuração fora da raiz pública.
- Validação de dados no servidor.
- Senhas armazenadas com password_hash() e verificadas com password_verify().
Esses mecanismos precisam ser verificados no código real. A presença deles na documentação não garante que todos os endpoints estejam protegidos. Antes de publicar, confira especialmente login, cadastro, autorização, sessões e mensagens de erro.
13. Limitações e pontos de atenção
Na revisão da documentação e dos registros do projeto, foram identificados pontos que merecem conferência:
1. Configuração do banco: confirme se Container.php lê corretamente as configurações de config/database.php e do .env. Não presuma que os valores padrão são os corretos.
2. Sessão de login: confira se o login por página e o login pela API usam o mesmo formato de sessão e são reconhecidos pelos mesmos mecanismos de autenticação.
3. Cadastro: confirme que nascimento e telefone existem no banco se o endpoint os utiliza.
4. Erros do banco: não envie detalhes internos de exceções SQL ao navegador em produção; registre-os em log e mostre uma mensagem segura.
5. Status de consultas: padronize os valores de status usados pelo banco, pelos serviços, pelos helpers e pela demonstração.
6. Validação e CSRF: verifique a validação de CPF, e-mail e senha e a proteção CSRF em todos os endpoints que alteram dados.
7. Autorização: endpoints de consulta e cancelamento devem confirmar que existe um paciente autenticado e que a consulta pertence a ele.
8. Arquivos de desenvolvimento: revise arquivos de backup e scripts de teste antes de publicar.
Esses itens são verificações pendentes, não uma afirmação de que todos continuam presentes na versão atual do código.
14. Plano de desenvolvimento
Etapa	Entrega	Situação descrita
1	Interface, núcleo e dados de demonstração	Concluída
2	Estrutura SQL e dados iniciais	Concluída
3	Conexão PDO e repositórios MySQL	Concluída, a validar no ambiente
4	Cadastro, login e controle de acesso	Parcial
5–6	Busca e agendamento transacionais	Concluída, a validar no ambiente
7–9	Painéis do paciente, médico e administrador	Pendente
10	Segurança, responsividade e refinamento	Pendente


15. Preparação para publicar no GitHub
Antes de publicar:
- Mantenha .env no .gitignore.
- Publique somente .env.example, sem senhas ou credenciais reais.
- Remova arquivos temporários, backups e scripts de teste que não devam fazer parte da versão final.
- Revise mensagens de erro, autenticação, autorização e proteção CSRF.
- Defina uma licença para o projeto, se for necessário.
MedCare — documentação do projeto organizada para facilitar a instalação, a manutenção e a evolução da aplicação.
