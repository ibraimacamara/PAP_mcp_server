import os
from fastmcp import FastMCP
from typing import Any, Dict, List, Tuple
import json
import bcrypt
from conexao import get_connection, init_db

mcp = FastMCP("gestor de escola MCP Tool")

 

# ---------------------------------------------------------------------------
# ALUNO
# ---------------------------------------------------------------------------

@mcp.tool
def insert_aluno(data: str) -> dict:
    """
    Regista um aluno completo:
    - Cria user (username = email)
    - senha = BI criptografado
    - Cria aluno
    - Liga curso, turma e encarregado(s)
    """

    conn = get_connection()
    if conn is None:
        return {"success": False, "message": "Sem conexão ao banco"}

    cur = conn.cursor(dictionary=True)

    try:
        data_dict = json.loads(data) if isinstance(data, str) else data

        # -------------------------
        # DADOS
        # -------------------------
        nome = data_dict.get("nome")
        data_nasc = data_dict.get("data_nascimento")
        contato = data_dict.get("contato")
        bi = data_dict.get("bi")
        email = data_dict.get("email")
        morada = data_dict.get("morada")
        genero = data_dict.get("genero")
        distrito = data_dict.get("distrito")
        freguesia = data_dict.get("freguesia")

        curso_id = data_dict.get("curso_id")
        turma_id = data_dict.get("turma_id")

        enc_principal = data_dict.get("encarregado_principal_id")
        laco_principal = data_dict.get("laco_principal")

        enc_sec = data_dict.get("encarregado_secundario_id")
        laco_sec = data_dict.get("laco_secundario")

        # -------------------------
        # VALIDAÇÃO
        # -------------------------
        if not all([nome, data_nasc, contato, bi, email, curso_id, turma_id]):
            return {"success": False, "message": "Campos obrigatórios em falta"}

        if not enc_principal or not laco_principal:
            return {"success": False, "message": "Encarregado principal obrigatório"}

        # -------------------------
        # VERIFICAR USER
        # -------------------------
        cur.execute("SELECT id FROM users WHERE username = %s", (email,))
        if cur.fetchone():
            return {"success": False, "message": "Email já registado"}

        # HASH DA SENHA (BI)

        senha_hash = bcrypt.hashpw(
            bi.encode("utf-8"),
            bcrypt.gensalt()
        ).decode("utf-8")

        # -------------------------
        # INSERT USERS

        cur.execute("""
            INSERT INTO users (username, senha, categoria)
            VALUES (%s, %s, %s)
        """, (email, senha_hash, "aluno"))

        user_id = cur.lastrowid

        # -------------------------
        # INSERT ALUNO
  
        cur.execute("""
            INSERT INTO aluno (
                user_id, nome, data_nascimento, contato,
                bi, email, morada, genero, distrito, freguesia
            )
            VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)
        """, (
            user_id, nome, data_nasc, contato,
            bi, email, morada, genero, distrito, freguesia
        ))

        aluno_id = cur.lastrowid

        # -------------------------
        # CURSO

        cur.execute("""
            INSERT INTO aluno_curso (numero_aluno, curso_id)
            VALUES (%s, %s)
        """, (aluno_id, curso_id))

        # -------------------------
        # TURMA

        cur.execute("""
            INSERT INTO aluno_turma (numero_aluno, turma_id)
            VALUES (%s, %s)
        """, (aluno_id, turma_id))

        # -------------------------
        # ENCARREGADO PRINCIPAL
    
        cur.execute("""
            INSERT INTO aluno_encarregado
            (numero_aluno, encarregado_id, laco_familiar)
            VALUES (%s, %s, %s)
        """, (aluno_id, enc_principal, laco_principal))

        # -------------------------
        # ENCARREGADO SECUNDÁRIO
        # -------------------------
        if enc_sec and laco_sec:
            cur.execute("""
                INSERT INTO aluno_encarregado
                (numero_aluno, encarregado_id, laco_familiar)
                VALUES (%s, %s, %s)
            """, (aluno_id, enc_sec, laco_sec))

        conn.commit()

        return {
            "success": True,
            "message": "Aluno registado com sucesso",
            "user_id": user_id,
            "aluno_id": aluno_id
        }

    except Exception as e:
        conn.rollback()
        return {
            "success": False,
            "message": "Erro ao registar aluno",
            "error": str(e)
        }

    finally:
        cur.close()
        conn.close()


@mcp.tool
def list_aluno() -> dict:
    """
    Lista todos os alunos registados no sistema.
    Retorna: {success, rows, columns, row_count, error}
    """
    conn = get_connection()
    if conn is None:
        return {"success": False, "message": "Sem conexão ao banco", "error": "get_connection retornou None"}

    cur = conn.cursor(dictionary=True)
    try:
        cur.execute("SELECT * FROM `aluno`")
        rows = cur.fetchall()
        columns = list(rows[0].keys()) if rows else []
        return {"success": True, "rows": rows, "columns": columns, "row_count": len(rows), "error": None}
    except Exception as e:
        return {"success": False, "message": "Erro ao listar alunos", "error": str(e)}
    finally:
        cur.close()
        conn.close()


@mcp.tool
def update_aluno(numero_aluno: int, updates: str) -> dict:
    """
    Atualiza dados de um aluno pelo seu número de identificação.
    updates: dict ou JSON string com os campos a alterar.
    Ex: {"email":"novo@email.com", "contato":"912345678"}
    """
    conn = get_connection()
    if not conn:
        return {"success": False, "message": "Sem conexão ao banco"}

    cur = conn.cursor()
    try:
        updates_dict = json.loads(updates) if isinstance(updates, str) else updates
        if not isinstance(updates_dict, dict):
            return {"success": False, "message": "Updates deve ser dict/JSON"}

        cur.execute("DESCRIBE `aluno`")
        colunas = [c[0] for c in cur.fetchall()]
        validos = {k: v for k, v in updates_dict.items() if k in colunas}

        if not validos:
            return {"success": False, "message": "Nenhuma coluna válida para update",
                    "error": f"Colunas disponíveis: {', '.join(colunas)}"}

        set_clause = ", ".join(f"`{k}`=%s" for k in validos.keys())
        sql = f"UPDATE `aluno` SET {set_clause} WHERE numero_aluno=%s"
        cur.execute(sql, list(validos.values()) + [numero_aluno])
        conn.commit()

        return {"success": True, "message": "Aluno atualizado com sucesso",
                "rows_affected": cur.rowcount, "error": None}
    except Exception as e:
        conn.rollback()
        return {"success": False, "message": "Erro ao atualizar aluno", "error": str(e)}
    finally:
        cur.close()
        conn.close()


@mcp.tool
def delete_aluno(numero_aluno: int) -> dict:
    """
    Remove um aluno pelo seu número de identificação.
    """
    conn = get_connection()
    if not conn:
        return {"success": False, "message": "Sem conexão ao banco"}

    cur = conn.cursor()
    try:
        cur.execute("DELETE FROM `aluno` WHERE numero_aluno = %s", (numero_aluno,))
        conn.commit()
        return {"success": True, "message": "Aluno removido com sucesso",
                "rows_affected": cur.rowcount, "error": None}
    except Exception as e:
        conn.rollback()
        return {"success": False, "message": "Erro ao remover aluno", "error": str(e)}
    finally:
        cur.close()
        conn.close()


# ---------------------------------------------------------------------------
# ENCARREGADO
# ---------------------------------------------------------------------------

@mcp.tool
def insert_encarregado(data: str) -> dict:
    """
    ESPELHO EXATO DO PHP:
    - cria users primeiro
    - depois cria encarregado
    - username = email
    - senha = hash(BI)
    """

    conn = get_connection()
    if conn is None:
        return {"success": False, "message": "Sem conexão ao banco"}

    cur = conn.cursor(dictionary=True)

    try:
        data_dict = json.loads(data) if isinstance(data, str) else data

        nome = data_dict.get("nome")
        email = data_dict.get("email")
        bi = data_dict.get("bi")
        morada = data_dict.get("morada")
        contato = data_dict.get("contato")
        genero = data_dict.get("genero")
        distrito = data_dict.get("distrito")
        freguesia = data_dict.get("freguesia")

        if not nome or not email or not bi:
            return {"success": False, "message": "Campos obrigatórios em falta"}


        # 1. VERIFICAR USER
        cur.execute("SELECT id FROM users WHERE username = %s", (email,))
        if cur.fetchone():
            return {"success": False, "message": "Já existe este user"}
        
        
        # 2. CRIAR USER (igual PHP)
        senha_hash = bcrypt.hashpw(
            bi.encode("utf-8"),
            bcrypt.gensalt()
        ).decode("utf-8")

        categoria = "encarregado"

        cur.execute("""
            INSERT INTO users (username, senha, categoria)
            VALUES (%s, %s, %s)
        """, (email, senha_hash, categoria))

        user_id = cur.lastrowid

    
        # 3. INSERIR ENCARREGADO
        cur.execute("""
            INSERT INTO encarregado
            (user_id, nome, email, bi, morada, contato, genero, distrito, freguesia)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
        """, (
            user_id,
            nome,
            email,
            bi,
            morada,
            contato,
            genero,
            distrito,
            freguesia
        ))

        conn.commit()

        return {
            "success": True,
            "message": "Encarregado criado com sucesso",
            "user_id": user_id,
            "encarregado_id": cur.lastrowid
        }

    except Exception as e:
        conn.rollback()
        return {
            "success": False,
            "message": "Erro ao criar encarregado",
            "error": str(e)
        }

    finally:
        cur.close()
        conn.close()

@mcp.tool
def list_encarregado() -> dict:
    """
    Lista todos os encarregados de educação registados.
    Retorna: {success, rows, columns, row_count, error}
    """
    conn = get_connection()
    if conn is None:
        return {"success": False, "message": "Sem conexão ao banco", "error": "get_connection retornou None"}

    cur = conn.cursor(dictionary=True)
    try:
        cur.execute("SELECT * FROM `encarregado`")
        rows = cur.fetchall()
        columns = list(rows[0].keys()) if rows else []
        return {"success": True, "rows": rows, "columns": columns, "row_count": len(rows), "error": None}
    except Exception as e:
        return {"success": False, "message": "Erro ao listar encarregados", "error": str(e)}
    finally:
        cur.close()
        conn.close()


@mcp.tool
def update_encarregado(encarregado_id: int, updates: str) -> dict:
    """
    Atualiza dados de um encarregado de educação pelo ID.
    updates: dict ou JSON string com os campos a alterar.
    """
    conn = get_connection()
    if not conn:
        return {"success": False, "message": "Sem conexão ao banco"}

    cur = conn.cursor()
    try:
        updates_dict = json.loads(updates) if isinstance(updates, str) else updates
        if not isinstance(updates_dict, dict):
            return {"success": False, "message": "Updates deve ser dict/JSON"}

        cur.execute("DESCRIBE `encarregado`")
        colunas = [c[0] for c in cur.fetchall()]
        validos = {k: v for k, v in updates_dict.items() if k in colunas}

        if not validos:
            return {"success": False, "message": "Nenhuma coluna válida para update",
                    "error": f"Colunas disponíveis: {', '.join(colunas)}"}

        set_clause = ", ".join(f"`{k}`=%s" for k in validos.keys())
        sql = f"UPDATE `encarregado` SET {set_clause} WHERE id=%s"
        cur.execute(sql, list(validos.values()) + [encarregado_id])
        conn.commit()

        return {"success": True, "message": "Encarregado atualizado com sucesso",
                "rows_affected": cur.rowcount, "error": None}
    except Exception as e:
        conn.rollback()
        return {"success": False, "message": "Erro ao atualizar encarregado", "error": str(e)}
    finally:
        cur.close()
        conn.close()


@mcp.tool
def delete_encarregado(encarregado_id: int) -> dict:
    """
    Remove um encarregado de educação pelo ID.
    """
    conn = get_connection()
    if not conn:
        return {"success": False, "message": "Sem conexão ao banco"}

    cur = conn.cursor()
    try:
        cur.execute("DELETE FROM `encarregado` WHERE id = %s", (encarregado_id,))
        conn.commit()
        return {"success": True, "message": "Encarregado removido com sucesso",
                "rows_affected": cur.rowcount, "error": None}
    except Exception as e:
        conn.rollback()
        return {"success": False, "message": "Erro ao remover encarregado", "error": str(e)}
    finally:
        cur.close()
        conn.close()


@mcp.tool
def get_encarregado_aluno(
    aluno_input: str = "",
    numero_aluno: int = 0,
) -> dict:
    """
    Consulta o encarregado de educação de um aluno.
    Pode pesquisar por nome completo/parcial ou por número do aluno.
    Retorna dados do aluno e dos seus encarregados com o laço familiar.
    para isso tens que ir pegar o numero_aluno ir na tabela aluno_encarregado buscar o encarregado_id
    e depois ir na tabela encarregado listar o dados de encarregado.
    se for aluno faz o acontrário.
    Ex: get_aluno_encarregado(aluno_input="Ibra Camara")
    """
    if not aluno_input and not numero_aluno:
        return {"success": False, "message": "Informe número do aluno ou nome.", "error": "missing_parameters"}

    conn = get_connection()
    if conn is None:
        return {"success": False, "message": "Sem conexão ao banco."}

    cur = conn.cursor(dictionary=True)
    try:
        if numero_aluno:
            cur.execute("SELECT * FROM aluno WHERE numero_aluno = %s", (numero_aluno,))
            aluno = cur.fetchone()
        else:
            termo = aluno_input.strip()
            cur.execute("""
                SELECT * FROM aluno
                WHERE nome = %s OR CONCAT(nome) = %s
                OR nome LIKE %s OR CONCAT(nome) LIKE %s
                LIMIT 1
            """, (termo, termo, termo, f"%{termo}%", f"%{termo}%", f"%{termo}%"))
            aluno = cur.fetchone()

        if not aluno:
            return {"success": False, "message": f"Aluno '{aluno_input or numero_aluno}' não encontrado.",
                    "error": "not_found"}

        aluno_id = aluno["numero_aluno"]
        cur.execute("SELECT id_encarregado, laco_familiar FROM aluno_encarregado WHERE numero_aluno = %s", (aluno_id,))
        relacoes = cur.fetchall()

        if not relacoes:
            return {"success": True, "message": "Aluno encontrado, mas não possui encarregado.",
                    "aluno": aluno, "encarregados": []}

        ids = [r["id_encarregado"] for r in relacoes]
        placeholders = ", ".join(["%s"] * len(ids))
        cur.execute(f"SELECT * FROM encarregado WHERE id IN ({placeholders})", tuple(ids))
        encarregados = cur.fetchall()

        for enc in encarregados:
            for r in relacoes:
                if enc["id"] == r["id_encarregado"]:
                    enc["laco_familiar"] = r["laco_familiar"]

        return {"success": True, "message": "Consulta realizada com sucesso.",
                "aluno": aluno, "encarregados": encarregados}

    except Exception as e:
        return {"success": False, "message": "Erro interno.", "error": str(e)}
    finally:
        cur.close()
        conn.close()


@mcp.tool
def delete_aluno_encarregado(encarregado_id: int) -> dict:
    """
    Remove a relação entre um aluno e um encarregado de educação pelo ID do encarregado.
    """
    conn = get_connection()
    if not conn:
        return {"success": False, "message": "Sem conexão ao banco"}

    cur = conn.cursor()
    try:
        cur.execute("DELETE FROM `aluno_encarregado` WHERE id_encarregado = %s", (encarregado_id,))
        conn.commit()
        return {"success": True, "message": "Relação aluno-encarregado removida com sucesso.",
                "rows_affected": cur.rowcount, "error": None}
    except Exception as e:
        conn.rollback()
        return {"success": False, "message": "Erro ao remover relação.", "error": str(e)}
    finally:
        cur.close()
        conn.close()


@mcp.tool
def link_aluno_encarregado(numero_aluno: int, encarregado_id: int, laco_familiar: str = "Pai/Mãe") -> dict:
    """
    Liga um encarregado de educação existente a um aluno.
    laco_familiar: ex. "Pai", "Mãe", "Tio", "Avó", etc.
    Ex: link_aluno_encarregado(numero_aluno=1, encarregado_id=2, laco_familiar="Mãe")
    """
    conn = get_connection()
    if not conn:
        return {"success": False, "message": "Sem conexão ao banco"}

    cur = conn.cursor(dictionary=True)
    try:
        cur.execute("SELECT numero_aluno, nome FROM aluno WHERE numero_aluno = %s", (numero_aluno,))
        aluno = cur.fetchone()
        if not aluno:
            return {"success": False, "message": f"Aluno {numero_aluno} não encontrado."}

        cur.execute("SELECT id, nome FROM encarregado WHERE id = %s", (encarregado_id,))
        enc = cur.fetchone()
        if not enc:
            return {"success": False, "message": f"Encarregado {encarregado_id} não encontrado."}

        cur.execute(
            "INSERT INTO aluno_encarregado (numero_aluno, id_encarregado, laco_familiar) VALUES (%s, %s, %s)",
            (numero_aluno, encarregado_id, laco_familiar)
        )
        conn.commit()
        return {
            "success": True,
            "message": f"Encarregado '{enc['nome']}' ligado ao aluno '{aluno['nome']}' como {laco_familiar}."
        }
    except Exception as e:
        conn.rollback()
        return {"success": False, "message": "Erro ao criar ligação.", "error": str(e)}
    finally:
        cur.close()
        conn.close()
# ---------------------------------------------------------------------------
# CURSO
# ---------------------------------------------------------------------------

@mcp.tool
def list_curso() -> dict:
    """
    Lista todos os cursos disponíveis no sistema.
    Retorna: {success, rows, row_count, error}
    """
    conn = get_connection()
    if conn is None:
        return {"success": False, "message": "Sem conexão ao banco", "error": "get_connection retornou None"}

    cur = conn.cursor(dictionary=True)
    try:
        cur.execute("SELECT * FROM `curso`")
        rows = cur.fetchall()
        return {"success": True, "rows": rows, "row_count": len(rows), "error": None}
    except Exception as e:
        return {"success": False, "message": "Erro ao listar cursos", "error": str(e)}
    finally:
        cur.close()
        conn.close()

@mcp.tool
def list_todos_alunos_do_curso(curso_id: int) -> dict:
    """
    Lista todos os alunos associados a um curso específico.

    Fluxo:
    - recebe curso_id
    - procura em aluno_curso
    - pega numero_aluno
    - busca dados na tabela aluno
    """

    conn = get_connection()
    if not conn:
        return {"success": False, "message": "Sem conexão ao banco"}

    cur = conn.cursor(dictionary=True)

    try:
        # -------------------------
        # 1. VALIDAR CURSO
        # -------------------------
        cur.execute("SELECT id, nome FROM curso WHERE id = %s", (curso_id,))
        curso = cur.fetchone()

        if not curso:
            return {"success": False, "message": f"Curso {curso_id} não encontrado"}

        # -------------------------
        # 2. BUSCAR ALUNOS (JOIN)
        # -------------------------
        cur.execute("""
            SELECT a.*
            FROM aluno_curso ac
            JOIN aluno a ON ac.numero_aluno = a.numero_aluno
            WHERE ac.curso_id = %s
        """, (curso_id,))

        alunos = cur.fetchall()

        return {
            "success": True,
            "curso": curso,
            "total_alunos": len(alunos),
            "alunos": alunos
        }

    except Exception as e:
        return {
            "success": False,
            "message": "Erro ao buscar alunos",
            "error": str(e)
        }

    finally:
        cur.close()
        conn.close()
        
# ---------------------------------------------------------------------------
# TURMA
# ---------------------------------------------------------------------------

@mcp.tool
def list_turma() -> dict:
    """
    Lista todas as turmas disponíveis no sistema com o curso associado.
    Útil para descobrir o ID de uma turma antes de inscrever um aluno.
    Retorna: {success, rows, row_count, error}
    """
    conn = get_connection()
    if conn is None:
        return {"success": False, "message": "Sem conexão ao banco", "error": "get_connection retornou None"}

    cur = conn.cursor(dictionary=True)
    try:
        cur.execute("""
            SELECT t.*, c.nome AS nome_curso
            FROM turma t
            LEFT JOIN curso c ON t.curso_id = c.id
        """)
        rows = cur.fetchall()
        return {"success": True, "rows": rows, "row_count": len(rows), "error": None}
    except Exception as e:
        return {"success": False, "message": "Erro ao listar turmas", "error": str(e)}
    finally:
        cur.close()
        conn.close()


@mcp.tool
def insert_aluno_turma(numero_aluno: int, turma_id: int) -> dict:
    """
    Inscreve um aluno numa turma.
    Use list_turma() para descobrir o ID da turma e list_aluno() para o número do aluno.
    Ex: insert_aluno_turma(numero_aluno=5, turma_id=2)
    """
    conn = get_connection()
    if not conn:
        return {"success": False, "message": "Sem conexão ao banco"}

    cur = conn.cursor(dictionary=True)
    try:
        cur.execute("SELECT numero_aluno, nome, FROM aluno WHERE numero_aluno = %s", (numero_aluno,))
        aluno = cur.fetchone()
        if not aluno:
            return {"success": False, "message": f"Aluno com número {numero_aluno} não encontrado.", "error": "not_found"}

        cur.execute("SELECT id, codigo FROM turma WHERE id = %s", (turma_id,))
        turma = cur.fetchone()
        if not turma:
            return {"success": False, "message": f"Turma com ID {turma_id} não encontrada.", "error": "not_found"}

        cur.execute("SELECT * FROM aluno_turma WHERE numero_aluno = %s AND turma_id = %s", (numero_aluno, turma_id))
        if cur.fetchone():
            return {"success": False, "message": f"Aluno {aluno['nome']} já está inscrito na turma {turma['codigo']}.",
                    "error": "already_exists"}

        cur.execute("INSERT INTO `aluno_turma` (numero_aluno, turma_id) VALUES (%s, %s)", (numero_aluno, turma_id))
        conn.commit()

        return {"success": True,
                "message": f"Aluno {aluno['nome']} inscrito na turma {turma['codigo']} com sucesso.",
                "rows_affected": cur.rowcount, "error": None}
    except Exception as e:
        conn.rollback()
        return {"success": False, "message": "Erro ao inscrever aluno na turma.", "error": str(e)}
    finally:
        cur.close()
        conn.close()


@mcp.tool
def get_aluno_turma(aluno_input: str = "", numero_aluno: int = 0) -> dict:
    """
    Consulta a turma em que um aluno está inscrito.
    Pesquisa por nome ou número do aluno.
    Ex: get_aluno_turma(aluno_input="Ibra Camara")
    """
    if not aluno_input and not numero_aluno:
        return {"success": False, "message": "Informe número do aluno ou nome.", "error": "missing_parameters"}

    conn = get_connection()
    if conn is None:
        return {"success": False, "message": "Sem conexão ao banco."}

    cur = conn.cursor(dictionary=True)
    try:
        if numero_aluno:
            cur.execute("SELECT * FROM aluno WHERE numero_aluno = %s", (numero_aluno,))
            aluno = cur.fetchone()
        else:
            termo = aluno_input.strip()
            cur.execute("""
                SELECT * FROM aluno
                WHERE nome = %s OR CONCAT(nome) = %s
                OR nome LIKE %s OR CONCAT(nome) LIKE %s
                LIMIT 1
            """, (termo, termo, termo, f"%{termo}%", f"%{termo}%", f"%{termo}%"))
            aluno = cur.fetchone()

        if not aluno:
            return {"success": False, "message": f"Aluno '{aluno_input or numero_aluno}' não encontrado.",
                    "error": "not_found"}

        cur.execute("""
            SELECT t.id, t.codigo, c.nome AS nome_curso
            FROM aluno_turma at2
            JOIN turma t ON at2.turma_id = t.id
            LEFT JOIN curso c ON t.curso_id = c.id
            WHERE at2.numero_aluno = %s
        """, (aluno["numero_aluno"],))
        turmas = cur.fetchall()

        if not turmas:
            return {"success": True, "message": "Aluno encontrado mas não está inscrito em nenhuma turma.",
                    "aluno": aluno, "turmas": []}

        return {"success": True, "message": "Consulta realizada com sucesso.",
                "aluno": aluno, "turmas": turmas}

    except Exception as e:
        return {"success": False, "message": "Erro interno.", "error": str(e)}
    finally:
        cur.close()
        conn.close()


# ---------------------------------------------------------------------------
# Funcionário
# ---------------------------------------------------------------------------

@mcp.tool
def list_funcionario() -> dict:
    """
    Lista todos os funcionarios registados no sistema.
    Retorna: {success, rows, row_count, error}
    """
    conn = get_connection()
    if conn is None:
        return {"success": False, "message": "Sem conexão ao banco", "error": "get_connection retornou None"}

    cur = conn.cursor(dictionary=True)
    try:
        cur.execute("SELECT * FROM `funcionario`")
        rows = cur.fetchall()
        return {"success": True, "rows": rows, "row_count": len(rows), "error": None}
    except Exception as e:
        return {"success": False, "message": "Erro ao listar funcionarios", "error": str(e)}
    finally:
        cur.close()
        conn.close()


@mcp.tool
def insert_funcionario(data: str) -> dict:
    """
    Insere um novo funcionario no sistema.
    data: dict ou JSON string com os campos do funcionario.
    Ex: {"nome":"João Costa", "email":"joao@escola.com"}
    """
    conn = get_connection()
    if conn is None:
        return {"success": False, "message": "Sem conexão ao banco", "error": "get_connection retornou None"}

    cur = conn.cursor()
    try:
        data_dict = json.loads(data) if isinstance(data, str) else data
        if not isinstance(data_dict, dict):
            return {"success": False, "message": "Dados devem ser um dict/JSON", "error": "Formato inválido"}

        cur.execute("DESCRIBE `funcionario`")
        col_names = [c[0] for c in cur.fetchall()]
        valid_data = {k: v for k, v in data_dict.items() if k in col_names}

        if not valid_data:
            return {"success": False, "message": "Nenhuma coluna válida para 'funcionario'",
                    "error": f"Colunas disponíveis: {', '.join(col_names)}"}

        columns = ", ".join([f"`{c}`" for c in valid_data.keys()])
        placeholders = ", ".join(["%s"] * len(valid_data))
        sql = f"INSERT INTO `funcionario` ({columns}) VALUES ({placeholders})"
        cur.execute(sql, tuple(valid_data.values()))
        conn.commit()

        return {"success": True, "message": "funcionario inserido com sucesso",
                "row_id": cur.lastrowid, "rows_affected": cur.rowcount, "error": None}
    except Exception as e:
        conn.rollback()
        return {"success": False, "message": "Erro ao inserir funcionario", "error": str(e)}
    finally:
        cur.close()
        conn.close()


@mcp.tool
def update_funcionario(funcionario_id: int, updates: str) -> dict:
    """
    Atualiza dados de um funcionario de educação pelo ID.
    updates: dict ou JSON string com os campos a alterar.
    """
    conn = get_connection()
    if not conn:
        return {"success": False, "message": "Sem conexão ao banco"}

    cur = conn.cursor()
    try:
        updates_dict = json.loads(updates) if isinstance(updates, str) else updates
        if not isinstance(updates_dict, dict):
            return {"success": False, "message": "Updates deve ser dict/JSON"}

        cur.execute("DESCRIBE `funcionario`")
        colunas = [c[0] for c in cur.fetchall()]
        validos = {k: v for k, v in updates_dict.items() if k in colunas}

        if not validos:
            return {"success": False, "message": "Nenhuma coluna válida para update",
                    "error": f"Colunas disponíveis: {', '.join(colunas)}"}

        set_clause = ", ".join(f"`{k}`=%s" for k in validos.keys())
        sql = f"UPDATE `funcionario` SET {set_clause} WHERE id=%s"
        cur.execute(sql, list(validos.values()) + [funcionario_id])
        conn.commit()

        return {"success": True, "message": "funcionario atualizado com sucesso",
                "rows_affected": cur.rowcount, "error": None}
    except Exception as e:
        conn.rollback()
        return {"success": False, "message": "Erro ao atualizar encarregado", "error": str(e)}
    finally:
        cur.close()
        conn.close()


@mcp.tool
def delete_funcionario(funcionario_id: int) -> dict:
    """
    Remove um funcionario e o respetivo user associado.
    """
    conn = get_connection()
    if not conn:
        return {"success": False, "message": "Sem conexão ao banco"}

    cur = conn.cursor(dictionary=True)

    try:
        # 1. Buscar o user_id do funcionario
        cur.execute("SELECT user_id FROM funcionario WHERE id = %s", (funcionario_id,))
        funcionario = cur.fetchone()

        if not funcionario:
            return {"success": False, "message": "funcionario não encontrado"}

        user_id = funcionario["user_id"]

        # 2. Apagar funcionario
        cur.execute("DELETE FROM funcionario WHERE id = %s", (funcionario_id,))

        # 3. Apagar user associado
        if user_id:
            cur.execute("DELETE FROM users WHERE id = %s", (user_id,))

        conn.commit()

        return {
            "success": True,
            "message": "funcionario e utilizador removidos com sucesso",
            "rows_affected": cur.rowcount,
            "error": None
        }

    except Exception as e:
        conn.rollback()
        return {"success": False, "message": "Erro ao remover funcionario", "error": str(e)}

    finally:
        cur.close()
        conn.close()


# ---------------------------------------------------------------------------
# PROFESSOR
# ---------------------------------------------------------------------------

@mcp.tool
def list_professor() -> dict:
    """
    Lista todos os professores registados no sistema.
    Retorna: {success, rows, row_count, error}
    """
    conn = get_connection()
    if conn is None:
        return {"success": False, "message": "Sem conexão ao banco", "error": "get_connection retornou None"}

    cur = conn.cursor(dictionary=True)
    try:
        cur.execute("SELECT * FROM `professor`")
        rows = cur.fetchall()
        return {"success": True, "rows": rows, "row_count": len(rows), "error": None}
    except Exception as e:
        return {"success": False, "message": "Erro ao listar professores", "error": str(e)}
    finally:
        cur.close()
        conn.close()


@mcp.tool
def insert_professor(data: str) -> dict:
    """
    Insere um novo professor no sistema.
    data: dict ou JSON string com os campos do professor.
    Ex: {"nome":"João Costa", "email":"joao@escola.com"}
    """
    conn = get_connection()
    if conn is None:
        return {"success": False, "message": "Sem conexão ao banco", "error": "get_connection retornou None"}

    cur = conn.cursor()
    try:
        data_dict = json.loads(data) if isinstance(data, str) else data
        if not isinstance(data_dict, dict):
            return {"success": False, "message": "Dados devem ser um dict/JSON", "error": "Formato inválido"}

        cur.execute("DESCRIBE `professor`")
        col_names = [c[0] for c in cur.fetchall()]
        valid_data = {k: v for k, v in data_dict.items() if k in col_names}

        if not valid_data:
            return {"success": False, "message": "Nenhuma coluna válida para 'professor'",
                    "error": f"Colunas disponíveis: {', '.join(col_names)}"}

        columns = ", ".join([f"`{c}`" for c in valid_data.keys()])
        placeholders = ", ".join(["%s"] * len(valid_data))
        sql = f"INSERT INTO `professor` ({columns}) VALUES ({placeholders})"
        cur.execute(sql, tuple(valid_data.values()))
        conn.commit()

        return {"success": True, "message": "Professor inserido com sucesso",
                "row_id": cur.lastrowid, "rows_affected": cur.rowcount, "error": None}
    except Exception as e:
        conn.rollback()
        return {"success": False, "message": "Erro ao inserir professor", "error": str(e)}
    finally:
        cur.close()
        conn.close()


@mcp.tool
def update_professor(professor_id: int, updates: str) -> dict:
    """
    Atualiza dados de um professor de educação pelo ID.
    updates: dict ou JSON string com os campos a alterar.
    """
    conn = get_connection()
    if not conn:
        return {"success": False, "message": "Sem conexão ao banco"}

    cur = conn.cursor()
    try:
        updates_dict = json.loads(updates) if isinstance(updates, str) else updates
        if not isinstance(updates_dict, dict):
            return {"success": False, "message": "Updates deve ser dict/JSON"}

        cur.execute("DESCRIBE `professor`")
        colunas = [c[0] for c in cur.fetchall()]
        validos = {k: v for k, v in updates_dict.items() if k in colunas}

        if not validos:
            return {"success": False, "message": "Nenhuma coluna válida para update",
                    "error": f"Colunas disponíveis: {', '.join(colunas)}"}

        set_clause = ", ".join(f"`{k}`=%s" for k in validos.keys())
        sql = f"UPDATE `professor` SET {set_clause} WHERE id=%s"
        cur.execute(sql, list(validos.values()) + [professor_id])
        conn.commit()

        return {"success": True, "message": "professor atualizado com sucesso",
                "rows_affected": cur.rowcount, "error": None}
    except Exception as e:
        conn.rollback()
        return {"success": False, "message": "Erro ao atualizar encarregado", "error": str(e)}
    finally:
        cur.close()
        conn.close()


@mcp.tool
def delete_professor(professor_id: int) -> dict:
    """
    Remove um professor e o respetivo user associado.
    """
    conn = get_connection()
    if not conn:
        return {"success": False, "message": "Sem conexão ao banco"}

    cur = conn.cursor(dictionary=True)

    try:
        # 1. Buscar o user_id do professor
        cur.execute("SELECT user_id FROM professor WHERE id = %s", (professor_id,))
        professor = cur.fetchone()

        if not professor:
            return {"success": False, "message": "Professor não encontrado"}

        user_id = professor["user_id"]

        # 2. Apagar professor
        cur.execute("DELETE FROM professor WHERE id = %s", (professor_id,))

        # 3. Apagar user associado
        if user_id:
            cur.execute("DELETE FROM users WHERE id = %s", (user_id,))

        conn.commit()

        return {
            "success": True,
            "message": "Professor e utilizador removidos com sucesso",
            "rows_affected": cur.rowcount,
            "error": None
        }

    except Exception as e:
        conn.rollback()
        return {"success": False, "message": "Erro ao remover professor", "error": str(e)}

    finally:
        cur.close()
        conn.close()








@mcp.tool
def list_users() -> dict:
    """
    Lista todos os users disponíveis no sistema.
    Retorna: {success, rows, row_count, error}
    """
    conn = get_connection()
    if conn is None:
        return {"success": False, "message": "Sem conexão ao banco", "error": "get_connection retornou None"}

    cur = conn.cursor(dictionary=True)
    try:
        cur.execute("SELECT * FROM `users`")
        rows = cur.fetchall()
        return {"success": True, "rows": rows, "row_count": len(rows), "error": None}
    except Exception as e:
        return {"success": False, "message": "Erro ao listar cursos", "error": str(e)}
    finally:
        cur.close()
        conn.close()


@mcp.tool
def insert_users(data: str) -> dict:
    """
    Insere um novo  na tabela ''
    '.
    data: dict ou JSON string com os campos do aluno.
    Ex: {"nome":"Ibra camara", "data_nascimento":"2005-01-01"}
    Obs:o campo nome comtem o nome completo de aluno primeiro nome e segundo nome.
    """
    conn = get_connection()
    if conn is None:
        return {"success": False, "message": "Sem conexão ao banco", "error": "get_connection retornou None"}

    cur = conn.cursor()
    try:
        data_dict = json.loads(data) if isinstance(data, str) else data
        if not isinstance(data_dict, dict):
            return {"success": False, "message": "Dados devem ser um dict/JSON", "error": "Formato inválido"}

        cur.execute("DESCRIBE `aluno`")
        col_names = [c[0] for c in cur.fetchall()]
        valid_data = {k: v for k, v in data_dict.items() if k in col_names}

        if not valid_data:
            return {"success": False, "message": "Nenhuma coluna válida para 'aluno'",
                    "error": f"Colunas disponíveis: {', '.join(col_names)}"}

        columns = ", ".join([f"`{c}`" for c in valid_data.keys()])
        placeholders = ", ".join(["%s"] * len(valid_data))
        sql = f"INSERT INTO `aluno` ({columns}) VALUES ({placeholders})"
        cur.execute(sql, tuple(valid_data.values()))
        conn.commit()

        return {"success": True, "message": "Aluno inserido com sucesso",
                "row_id": cur.lastrowid, "rows_affected": cur.rowcount, "error": None}

    except Exception as e:
        conn.rollback()
        return {"success": False, "message": "Erro ao inserir aluno", "error": str(e)}
    finally:
        cur.close()
        conn.close()

# ---------------------------------------------------------------------------
# STARTUP
# ---------------------------------------------------------------------------

if __name__ == "__main__":
    port = int(os.getenv("MCP_PORT", 8000))
    print(f"Starting MCP server (sse) on http://0.0.0.0:{port}/sse")
    mcp.run(transport="sse", host="0.0.0.0", port=port)
