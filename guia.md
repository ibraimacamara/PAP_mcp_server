# IBRA — Sistema de Gestão Escolar com Assistente IA

Este projeto é um sistema de gestão escolar com um assistente de inteligência artificial que responde a perguntas em linguagem natural, como:

- *"Qual é o encarregado de educação do aluno João Silva?"*
- *"Insere o aluno Ibra na turma INF-A"*
- *"Lista todos os professores"*

---

## Como funciona (visão geral)

```
Tu escreves no chat (painel PHP ou n8n)
        ↓
   n8n — Agente IA (Google Gemini)
        ↓
   Servidor MCP (Python) — porta 8000
        ↓
   Base de dados MySQL (gestor_escola)
```

---

## O que precisas de instalar

| Ferramenta | Para que serve | Link |
|---|---|---|
| **Python 3.10+** | Correr o servidor MCP | https://www.python.org/downloads/ |
| **Docker Desktop** | Correr o n8n | https://www.docker.com/products/docker-desktop/ |
| **XAMPP** | Apache (PHP) + MySQL | https://www.apachefriends.org/ |

> No XAMPP, inicia o **Apache** e o **MySQL**.

---

## Passo 1 — Configurar a base de dados

1. Abre o **XAMPP** e clica em **Start** no Apache e no MySQL
2. Corre o script SQL de criação da base de dados:

```bash
/Applications/XAMPP/xamppfiles/bin/mysql -u root -e "SOURCE /caminho/para/PAP_mcp_server/database/schema_with_data.sql;"
```

> Ou abre o `phpMyAdmin` (`http://localhost/phpmyadmin`), clica em **Import** e seleciona o ficheiro `database/schema_with_data.sql`.

O script cria automaticamente a base de dados `gestor_escola` com dados de teste:
- 5 alunos (incluindo **Ibra Camara**)
- 4 turmas (INF-A, INF-B, GES-A, ENF-A)
- 3 cursos
- 3 professores
- 4 encarregados

---

## Passo 2 — Configurar o ficheiro `.env`

Abre o ficheiro `backend/.env` e confirma que os dados estão corretos:

```
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=gestor_escola
MCP_PORT=8000
```

> Se o teu MySQL tiver senha, mete-a no campo `DB_PASSWORD`.

---

## Passo 3 — Instalar as dependências Python

```bash
cd PAP_mcp_server/backend
python -m venv .venv
source .venv/bin/activate      # Mac/Linux
# .venv\Scripts\activate       # Windows

pip install -r requirements.txt
```

---

## Passo 4 — Iniciar o servidor MCP

```bash
cd PAP_mcp_server/backend
source .venv/bin/activate
python server1.py
```

Deves ver:

```
Starting MCP server (sse) on http://0.0.0.0:8000/sse
Uvicorn running on http://0.0.0.0:8000
```

> **Deixa este terminal aberto.** O servidor tem de estar sempre a correr.

> **Atenção:** Não corras outros servidores na porta 8000 ao mesmo tempo (verifica com `lsof -i :8000`).

---

## Passo 5 — Configurar o Apache (XAMPP) para o painel PHP

Adiciona `ibra.local` ao ficheiro de hosts do sistema:

```bash
# Mac/Linux
echo "127.0.0.1 ibra.local" | sudo tee -a /etc/hosts

# Windows (como Administrador)
echo 127.0.0.1 ibra.local >> C:\Windows\System32\drivers\etc\hosts
```

Edita o ficheiro `/Applications/XAMPP/xamppfiles/etc/extra/httpd-vhosts.conf` e adiciona:

```apache
<VirtualHost *:80>
    ServerName ibra.local
    DocumentRoot "/caminho/para/PAP_mcp_server/dashmin"
    <Directory "/caminho/para/PAP_mcp_server/dashmin">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Reinicia o Apache no XAMPP. O painel estará disponível em `http://ibra.local/admin/`.

> Certifica-te também que o ficheiro `httpd-vhosts.conf` está incluído no `httpd.conf` (linha `Include etc/extra/httpd-vhosts.conf` deve estar sem `#`).

---

## Passo 6 — Iniciar o n8n com Docker

```bash
cd PAP_mcp_server
docker compose up -d
```

Abre `http://localhost:5678` e cria uma conta local.

---

## Passo 7 — Obter a chave API do Google Gemini (grátis)

1. Vai a [https://aistudio.google.com](https://aistudio.google.com)
2. Faz login com a tua conta Google
3. Clica em **"Get API key"** → **"Create API key"**
4. Copia a chave (começa por `AIza...`)

> Modelo recomendado: `models/gemini-2.5-pro-exp-03-25` (gratuito, experimental)

---

## Passo 8 — Importar o workflow no n8n

1. No n8n, clica em **"New workflow"**
2. Menu **⋯** → **"Import from file"**
3. Seleciona `ibra_agent_workflow.json`
4. Clica no nó **"Google Gemini"** → cria credencial com a tua chave API
5. Confirma que o nó **"MCP Server Escolar"** tem:
   - Connection Type: **SSE**
   - URL: `http://host.docker.internal:8000/sse`
6. Ativa o workflow (toggle **Inactive → Active**)

---

## Passo 9 — Usar o chat

### Opção A — Chat integrado no painel PHP

Abre `http://ibra.local/admin/chatbox.php` para a interface de chat completa.

O painel também tem um **widget de chat** (botão "IA" na barra superior) disponível em qualquer página da administração.

### Opção B — Chat do n8n

Clica no nó **"Chat Trigger"** no n8n → copia o link do chat webhook.

Exemplos de perguntas:
- `"Lista todas as turmas"`
- `"Qual o encarregado do aluno Ibra Camara?"`
- `"Insere o aluno Ibra na turma INF-B"`
- `"Liga o encarregado António ao aluno Fatoumata como Tio"`

---

## Resumo — ordem de arranque

```
1. Inicia o XAMPP (Apache + MySQL)
2. cd backend && python server1.py          ← terminal 1
3. docker compose up -d                     ← (só se o Docker não estiver a correr)
4. Abre http://localhost:5678               ← confirma que o workflow está Active
5. Abre http://ibra.local/admin/            ← painel de administração
```

---

## Estrutura do projeto

```
PAP_mcp_server/
├── backend/
│   ├── server1.py              ← Servidor MCP principal
│   ├── conexao.py              ← Ligação ao MySQL
│   ├── .env                    ← Credenciais (não commitar)
│   └── requirements.txt        ← Dependências Python
├── dashmin/                    ← Painel de administração PHP
│   └── admin/
│       ├── chatbox.php         ← Chat completo (página inteira)
│       ├── menu.php            ← Sidebar + widget de chat popup
│       └── ...
├── database/
│   └── schema_with_data.sql    ← Schema + dados de teste
├── ibra_agent_workflow.json    ← Workflow do n8n
├── docker-compose.yml          ← n8n via Docker
└── guia.md                     ← Este ficheiro
```

---

## Ferramentas disponíveis no agente

| Ferramenta | O que faz |
|---|---|
| `list_aluno` | Lista todos os alunos |
| `insert_aluno` | Adiciona um novo aluno |
| `update_aluno` | Atualiza dados de um aluno |
| `delete_aluno` | Remove um aluno |
| `get_aluno_encarregado` | Consulta o encarregado de um aluno |
| `list_encarregado` | Lista todos os encarregados |
| `insert_encarregado` | Adiciona um novo encarregado |
| `update_encarregado` | Atualiza dados de um encarregado |
| `delete_encarregado` | Remove um encarregado |
| `link_aluno_encarregado` | Liga um encarregado existente a um aluno |
| `list_turma` | Lista todas as turmas |
| `insert_aluno_turma` | Inscreve um aluno numa turma |
| `get_aluno_turma` | Consulta a turma de um aluno |
| `list_professor` | Lista todos os professores |
| `insert_professor` | Adiciona um novo professor |
| `list_curso` | Lista todos os cursos |

---

## Problemas comuns

**"Sem conexão ao banco"**
→ Verifica se o MySQL está ligado no XAMPP e se o `backend/.env` tem os dados corretos.

**"Unknown database 'gestor_escola'"**
→ Corre o script SQL: `database/schema_with_data.sql` no phpMyAdmin ou via terminal.

**O chat não responde / erro de ligação**
→ Confirma que o `server1.py` está a correr e que o workflow está **Active** no n8n.

**Porta 8000 ocupada**
→ Corre `lsof -i :8000` para ver que processo está a usar a porta e termina-o com `kill <PID>`.

**"Non-200 status code (404)" no MCP do n8n**
→ O servidor MCP usa transporte SSE. Confirma que o nó MCP no n8n aponta para `http://host.docker.internal:8000/sse` com tipo **SSE**.

**Quota do Gemini esgotada (429)**
→ Muda o modelo para `models/gemini-2.5-pro-exp-03-25` ou cria uma nova chave API num projeto Google diferente.
