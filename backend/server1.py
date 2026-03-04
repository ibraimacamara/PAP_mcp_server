import os
from fastmcp import FastMCP
from typing import Any, Dict, List, Tuple, Union
import json
from conexao import get_connection, init_db

mcp = FastMCP("gestor de escola MCP Tool")


# ---------------------------------------------------------------------------
# ALUNO
# ---------------------------------------------------------------------------

@mcp.tool
def insert_aluno(data: Union[str, dict]) -> dict:
    """
    Insere um novo aluno na tabela 'aluno'.
    data: dict ou JSON string com os campos do aluno.
    Ex: {"nome":"Ibra", "apelido":"Camara", "data_nascimento":"2005-01-01"}
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
def update_aluno(numero_aluno: int, updates: Union[dict, str]) -> dict:
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
def insert_encarregado(data: Union[str, dict]) -> dict:
    """
    Insere um novo encarregado de educação.
    data: dict ou JSON string com os campos do encarregado.
    Ex: {"nome":"Maria Silva", "bi":"001234567LA0"}
    """
    conn = get_connection()
    if conn is None:
        return {"success": False, "message": "Sem conexão ao banco", "error": "get_connection retornou None"}

    cur = conn.cursor()
    try:
        data_dict = json.loads(data) if isinstance(data, str) else data
        if not isinstance(data_dict, dict):
            return {"success": False, "message": "Dados devem ser um dict/JSON", "error": "Formato inválido"}

        cur.execute("DESCRIBE `encarregado`")
        col_names = [c[0] for c in cur.fetchall()]
        valid_data = {k: v for k, v in data_dict.items() if k in col_names}

        if not valid_data:
            return {"success": False, "message": "Nenhuma coluna válida para 'encarregado'",
                    "error": f"Colunas disponíveis: {', '.join(col_names)}"}

        columns = ", ".join([f"`{c}`" for c in valid_data.keys()])
        placeholders = ", ".join(["%s"] * len(valid_data))
        sql = f"INSERT INTO `encarregado` ({columns}) VALUES ({placeholders})"
        cur.execute(sql, tuple(valid_data.values()))
        conn.commit()

        return {"success": True, "message": "Encarregado inserido com sucesso",
                "row_id": cur.lastrowid, "rows_affected": cur.rowcount, "error": None}
    except Exception as e:
        conn.rollback()
        return {"success": False, "message": "Erro ao inserir encarregado", "error": str(e)}
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
def update_encarregado(encarregado_id: int, updates: Union[dict, str]) -> dict:
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
def get_aluno_encarregado(
    aluno_input: str | None = None,
    numero_aluno: int | None = None,
) -> dict:
    """
    Consulta o encarregado de educação de um aluno.
    Pode pesquisar por nome completo/parcial ou por número do aluno.
    Retorna dados do aluno e dos seus encarregados com o laço familiar.
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
                WHERE nome = %s OR apelido = %s OR CONCAT(nome, ' ', apelido) = %s
                OR nome LIKE %s OR apelido LIKE %s OR CONCAT(nome, ' ', apelido) LIKE %s
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
        cur.execute("SELECT numero_aluno, nome, apelido FROM aluno WHERE numero_aluno = %s", (numero_aluno,))
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
                "message": f"Aluno {aluno['nome']} {aluno['apelido']} inscrito na turma {turma['codigo']} com sucesso.",
                "rows_affected": cur.rowcount, "error": None}
    except Exception as e:
        conn.rollback()
        return {"success": False, "message": "Erro ao inscrever aluno na turma.", "error": str(e)}
    finally:
        cur.close()
        conn.close()


@mcp.tool
def get_aluno_turma(aluno_input: str | None = None, numero_aluno: int | None = None) -> dict:
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
                WHERE nome = %s OR apelido = %s OR CONCAT(nome, ' ', apelido) = %s
                OR nome LIKE %s OR apelido LIKE %s OR CONCAT(nome, ' ', apelido) LIKE %s
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
def insert_professor(data: Union[str, dict]) -> dict:
    """
    Insere um novo professor no sistema.
    data: dict ou JSON string com os campos do professor.
    Ex: {"nome":"João", "apelido":"Costa", "email":"joao@escola.com"}
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


# ---------------------------------------------------------------------------
# STARTUP
# ---------------------------------------------------------------------------

if __name__ == "__main__":
    port = int(os.getenv("MCP_PORT", 8000))
    print(f"Starting MCP server (SSE) on http://0.0.0.0:{port}/sse")
    mcp.run(transport="sse", host="0.0.0.0", port=port)
