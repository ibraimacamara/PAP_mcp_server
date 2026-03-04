# IBRA — Sistema de Gestão Escolar com Assistente IA

Este projeto é um sistema de gestão escolar com um assistente de inteligência artificial que responde a perguntas em linguagem natural, como:

- *"Qual é o encarregado de educação do aluno João Silva?"*
- *"Insere o aluno Ibra na turma 10A"*
- *"Lista todos os professores"*

---

## Como funciona (visão geral)

```
Tu escreves no chat
        ↓
   n8n (interface de chat)
        ↓
   Agente IA (Google Gemini)
        ↓
   Servidor MCP (Python) — porta 8000
        ↓
   Base de dados MySQL (gestor_escola)
```

---

## O que precisas de instalar

Antes de começar, instala estas ferramentas no teu computador:

| Ferramenta | Para que serve | Link |
|---|---|---|
| **Python 3.10+** | Correr o servidor MCP | https://www.python.org/downloads/ |
| **Docker Desktop** | Correr o n8n | https://www.docker.com/products/docker-desktop/ |
| **XAMPP** (ou MySQL) | Base de dados | https://www.apachefriends.org/ |

> **Nota:** No XAMPP, basta iniciar o **Apache** e o **MySQL**.

---

## Passo 1 — Configurar a base de dados

1. Abre o **XAMPP** e clica em **Start** no Apache e no MySQL
2. Abre o browser e vai a `http://localhost/phpmyadmin`
3. Cria uma base de dados chamada `gestor_escola`
4. Importa o teu ficheiro SQL (se tiveres um) ou cria as tabelas manualmente

> A base de dados deve ter estas tabelas: `aluno`, `encarregado`, `aluno_encarregado`, `professor`, `curso`, `turma`, `aluno_turma`, `aluno_curso`, `users`

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

Abre o **terminal** (Prompt de Comando no Windows, Terminal no Mac), vai à pasta do projeto e corre:

```bash
cd PAP_mcp_server/backend
pip install -r requirements.txt
```

Se tiveres problemas com `pip`, tenta `pip3` em vez de `pip`.

---

## Passo 4 — Iniciar o servidor MCP

No mesmo terminal, corre:

```bash
python server1.py
```

Deves ver esta mensagem:

```
Starting MCP server (SSE) on http://0.0.0.0:8000/sse
```

> **Deixa este terminal aberto.** O servidor tem de estar sempre a correr enquanto usas o sistema.

---

## Passo 5 — Iniciar o n8n com Docker

Abre um **segundo terminal**, vai à pasta do projeto e corre:

```bash
cd PAP_mcp_server
docker compose up -d
```

O Docker vai descarregar a imagem do n8n (pode demorar alguns minutos na primeira vez).

Quando terminar, abre o browser e vai a:

```
http://localhost:5678
```

Cria uma conta (é local, não precisas de cartão de crédito).

---

## Passo 6 — Obter a chave API do Google Gemini (grátis)

1. Vai a [https://aistudio.google.com](https://aistudio.google.com)
2. Faz login com a tua conta Google
3. Clica em **"Get API key"** → **"Create API key"**
4. Copia a chave (começa por `AIza...`)

> Esta chave é gratuita e permite testar à vontade.

---

## Passo 7 — Importar o workflow no n8n

1. No n8n (`http://localhost:5678`), clica em **"New workflow"**
2. Clica no menu **⋯** (três pontos) no canto superior direito
3. Escolhe **"Import from file"**
4. Seleciona o ficheiro `ibra_agent_workflow.json` que está na pasta do projeto
5. O workflow vai aparecer com 5 nós ligados entre si

---

## Passo 8 — Adicionar as credenciais do Google Gemini

1. Clica no nó **"Google Gemini Flash"** (o nó cor-de-laranja/amarelo)
2. Em **"Credential to connect with"**, clica em **"Create new credential"**
3. Cola a tua chave API do passo anterior
4. Clica em **Save**

---

## Passo 9 — Ativar o workflow

1. No canto superior direito do n8n, muda o toggle de **Inactive** para **Active**
2. O workflow está agora a correr

---

## Passo 10 — Abrir o chat

1. Clica no nó **"Chat Trigger"** (o primeiro nó, à esquerda)
2. Copia o link que aparece (algo como `http://localhost:5678/webhook/.../chat`)
3. Abre esse link no browser

Já podes escrever perguntas em português! Experimenta:

- `"Lista todos os alunos"`
- `"Qual o encarregado do aluno com número 1?"`
- `"Insere o aluno Ibra Camara na turma 1"`

---

## Incorporar o chat no painel de administração (opcional)

Se quiseres mostrar o chat dentro do painel PHP (`dashmin`), abre qualquer página PHP e adiciona este código onde quiseres o chat:

```html
<iframe
  src="http://localhost:5678/webhook/ibra-chat-webhook/chat"
  width="100%"
  height="600"
  style="border: none; border-radius: 8px;">
</iframe>
```

---

## Resumo — ordem de arranque

Sempre que quiseres usar o sistema, faz isto **por esta ordem**:

```
1. Inicia o XAMPP (Apache + MySQL)
2. Corre: python server1.py          (terminal 1)
3. Corre: docker compose up -d       (terminal 2, só se o Docker não estiver já a correr)
4. Abre: http://localhost:5678       (n8n — workflow deve estar ativo)
5. Abre o link do chat e usa o sistema
```

---

## Estrutura do projeto

```
PAP_mcp_server/
├── backend/
│   ├── server1.py          ← Servidor MCP (ferramentas de base de dados)
│   ├── server.py           ← Versão alternativa genérica
│   ├── conexao.py          ← Ligação ao MySQL
│   ├── .env                ← Credenciais da base de dados
│   └── requirements.txt    ← Dependências Python
├── dashmin/                ← Painel de administração PHP
├── ibra_agent_workflow.json ← Workflow do n8n (importar aqui)
├── docker-compose.yml      ← Configuração do Docker para o n8n
└── README.md               ← Este ficheiro
```

---

## Ferramentas disponíveis no agente

O agente tem acesso a estas ações na base de dados:

| Ferramenta | O que faz |
|---|---|
| `list_aluno` | Lista todos os alunos |
| `insert_aluno` | Adiciona um novo aluno |
| `update_aluno` | Atualiza dados de um aluno |
| `delete_aluno` | Remove um aluno |
| `get_aluno_encarregado` | Consulta o encarregado de um aluno |
| `list_encarregado` | Lista todos os encarregados |
| `insert_encarregado` | Adiciona um novo encarregado |
| `list_turma` | Lista todas as turmas |
| `insert_aluno_turma` | Inscreve um aluno numa turma |
| `get_aluno_turma` | Consulta a turma de um aluno |
| `list_professor` | Lista todos os professores |
| `insert_professor` | Adiciona um novo professor |
| `list_curso` | Lista todos os cursos |

---

## Problemas comuns

**"Sem conexão ao banco"**
→ Verifica se o MySQL está ligado no XAMPP e se o `.env` tem os dados corretos.

**O chat não responde**
→ Confirma que o `server1.py` está a correr e que o workflow está **Active** no n8n.

**Erro ao ligar ao MCP no Docker**
→ O endereço `host.docker.internal:8000` só funciona se o servidor MCP estiver a correr no teu computador (fora do Docker). Confirma que o `server1.py` está a correr num terminal normal.

**"Invalid API key" no Gemini**
→ A chave expira ou pode ter sido revogada. Vai ao Google AI Studio e cria uma nova.
