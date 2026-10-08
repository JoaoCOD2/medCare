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
