from fastmcp import FastMCP
from typing import Any, Dict, List, Tuple, Union
import json
from conexao import get_connection, init_db

mcp = FastMCP("gestor de escola MCP Tool")


def _consume_meta(*args, **kwargs):
    return None
@mcp.tool
def insert_aluno(
    data: Union[str, dict],
    sessionId: str | None = None,
    action: str | None = None,
    chatInput: str | None = None,
    toolCallId: str | None = None,
) -> dict:
    """
    Insere um registro na tabela 'aluno'.
    Somente colunas válidas da tabela 'aluno' serão aceitas.

    data: dict ou JSON string com os campos do aluno
    Ex: {"nome":"João", "idade":16, "email":"joao@gmail.com"}
    """
    _consume_meta(sessionId, action, chatInput, toolCallId)

    conn = get_connection()
    if conn is None:
        return {
            "success": False,
            "message": "Sem conexão ao banco",
            "error": "get_connection retornou None",
        }

    cur = conn.cursor()
    try:
        # Converte string JSON para dict
        data_dict = json.loads(data) if isinstance(data, str) else data

        if not isinstance(data_dict, dict):
            return {
                "success": False,
                "message": "Dados devem ser um dict/JSON",
                "error": "Formato inválido",
            }

        # Descreve tabela aluno para validar colunas
        cur.execute("DESCRIBE `aluno`")
        cols_info = cur.fetchall()
        col_names = [c[0] for c in cols_info]

        # Filtra somente colunas válidas
        valid_data = {k: v for k, v in data_dict.items() if k in col_names}

        if not valid_data:
            return {
                "success": False,
                "message": "Nenhuma coluna válida para 'aluno'",
                "error": f"Colunas disponíveis: {', '.join(col_names)}",
            }

        columns = ", ".join([f"`{c}`" for c in valid_data.keys()])
        placeholders = ", ".join(["%s"] * len(valid_data))
        values = tuple(valid_data.values())

        sql = f"INSERT INTO `aluno` ({columns}) VALUES ({placeholders})"

        cur.execute(sql, values)
        conn.commit()

        return {
            "success": True,
            "message": "Aluno inserido com sucesso",
            "row_id": cur.lastrowid,
            "rows_affected": cur.rowcount,
            "error": None,
        }

    except Exception as e:
        conn.rollback()
        return {
            "success": False,
            "message": "Erro ao inserir aluno",
            "error": str(e),
        }

    finally:
        cur.close()
        conn.close()

@mcp.tool
def list_aluno(
    sessionId: str | None = None,
    action: str | None = None,
    chatInput: str | None = None,
    toolCallId: str | None = None,
) -> dict:
    """
    Lista todos os registros da tabela 'aluno'.
    Retorna: {success, rows, columns, row_count, error}
    """
    _consume_meta(sessionId, action, chatInput, toolCallId)

    conn = get_connection()
    if conn is None:
        return {
            "success": False,
            "message": "Sem conexão ao banco",
            "error": "get_connection retornou None",
        }

    cur = conn.cursor(dictionary=True)
    try:
        cur.execute("SELECT * FROM `aluno`")
        rows = cur.fetchall()
        columns = list(rows[0].keys()) if rows else []

        return {
            "success": True,
            "rows": rows,
            "columns": columns,
            "row_count": len(rows),
            "error": None,
        }

    except Exception as e:
        return {
            "success": False,
            "message": "Erro ao listar aluno",
            "error": str(e),
        }

    finally:
        cur.close()
        conn.close()

@mcp.tool
def update_aluno(
    numero_aluno: int,
    updates: dict | str,
    sessionId: str | None = None,
    action: str | None = None,
    chatInput: str | None = None,
    toolCallId: str | None = None,
) -> dict:
    """
    Atualiza um aluno na tabela 'aluno' usando o ID.
    updates pode ser dict ou JSON string.
    """
    _consume_meta(sessionId, action, chatInput, toolCallId)

    conn = get_connection()
    if not conn:
        return {"success": False, "message": "Sem conexão ao banco"}

    cur = conn.cursor()
    try:
        updates_dict = json.loads(updates) if isinstance(updates, str) else updates

        if not isinstance(updates_dict, dict):
            return {"success": False, "message": "Updates deve ser dict/JSON"}

        # validar colunas da tabela
        cur.execute("DESCRIBE `aluno`")
        colunas = [c[0] for c in cur.fetchall()]

        validos = {k: v for k, v in updates_dict.items() if k in colunas}

        if not validos:
            return {
                "success": False,
                "message": "Nenhuma coluna válida para update",
                "error": f"Colunas disponíveis: {', '.join(colunas)}"
            }

        set_clause = ", ".join(f"`{k}`=%s" for k in validos.keys())
        params = list(validos.values()) + [numero_aluno]

        sql = f"UPDATE `aluno` SET {set_clause} WHERE numero_aluno=%s"
        cur.execute(sql, params)
        conn.commit()

        return {
            "success": True,
            "message": "Aluno atualizado com sucesso",
            "rows_affected": cur.rowcount,
            "error": None
        }

    except Exception as e:
        conn.rollback()
        return {"success": False, "message": "Erro ao atualizar aluno", "error": str(e)}

    finally:
        cur.close()
        conn.close()

@mcp.tool
def delete_aluno(
    numero_aluno: int,
    sessionId: str | None = None,
    action: str | None = None,
    chatInput: str | None = None,
    toolCallId: str | None = None,
) -> dict:
    """
    Remove um aluno da tabela 'aluno' pelo ID.
    """
    _consume_meta(sessionId, action, chatInput, toolCallId)

    conn = get_connection()
    if not conn:
        return {"success": False, "message": "Sem conexão ao banco"}

    cur = conn.cursor()
    try:
        sql = "DELETE FROM `aluno` WHERE numero_aluno = %s"
        cur.execute(sql, (numero_aluno,))
        conn.commit()

        return {
            "success": True,
            "message": "Aluno removido com sucesso",
            "rows_affected": cur.rowcount,
            "error": None
        }

    except Exception as e:
        conn.rollback()
        return {"success": False, "message": "Erro ao remover aluno", "error": str(e)}

    finally:
        cur.close()
        conn.close()

#######################################--alunos_encarregados--####################################

@mcp.tool
def insert_encarregado(
    data: Union[str, dict],
    sessionId: str | None = None,
    action: str | None = None,
    chatInput: str | None = None,
    toolCallId: str | None = None,
) -> dict:
    """
    Insere um registro na tabela 'encarregados'.
    Somente colunas válidas da tabela 'encarregados' serão aceitas.

    data: dict ou JSON string com os campos do encarregado
    Ex: {"nome":"João", "idade":16, "email":"joao@gmail.com"}
    """
    _consume_meta(sessionId, action, chatInput, toolCallId)

    conn = get_connection()
    if conn is None:
        return {
            "success": False,
            "message": "Sem conexão ao banco",
            "error": "get_connection retornou None",
        }

    cur = conn.cursor()
    try:
        # Converte string JSON para dict
        data_dict = json.loads(data) if isinstance(data, str) else data

        if not isinstance(data_dict, dict):
            return {
                "success": False,
                "message": "Dados devem ser um dict/JSON",
                "error": "Formato inválido",
            }

        # Descreve tabela encarregado para validar colunas
        cur.execute("DESCRIBE `encarregados`")
        cols_info = cur.fetchall()
        col_names = [c[0] for c in cols_info]

        # Filtra somente colunas válidas
        valid_data = {k: v for k, v in data_dict.items() if k in col_names}

        if not valid_data:
            return {
                "success": False,
                "message": "Nenhuma coluna válida para 'encarregados'",
                "error": f"Colunas disponíveis: {', '.join(col_names)}",
            }

        columns = ", ".join([f"`{c}`" for c in valid_data.keys()])
        placeholders = ", ".join(["%s"] * len(valid_data))
        values = tuple(valid_data.values())

        sql = f"INSERT INTO `encarregados` ({columns}) VALUES ({placeholders})"

        cur.execute(sql, values)
        conn.commit()

        return {
            "success": True,
            "message": "encarregado inserido com sucesso",
            "row_id": cur.lastrowid,
            "rows_affected": cur.rowcount,
            "error": None,
        }

    except Exception as e:
        conn.rollback()
        return {
            "success": False,
            "message": "Erro ao inserir encarregado",
            "error": str(e),
        }

    finally:
        cur.close()
        conn.close()

@mcp.tool
def list_encarregado(
    sessionId: str | None = None,
    action: str | None = None,
    chatInput: str | None = None,
    toolCallId: str | None = None,
) -> dict:
    """
    Lista todos os registros da tabela 'encarregado'.
    Retorna: {success, rows, columns, row_count, error}
    """
    _consume_meta(sessionId, action, chatInput, toolCallId)

    conn = get_connection()
    if conn is None:
        return {
            "success": False,
            "message": "Sem conexão ao banco",
            "error": "get_connection retornou None",
        }

    cur = conn.cursor(dictionary=True)
    try:
        cur.execute("SELECT * FROM `encarregado`")
        rows = cur.fetchall()
        columns = list(rows[0].keys()) if rows else []

        return {
            "success": True,
            "rows": rows,
            "columns": columns,
            "row_count": len(rows),
            "error": None,
        }

    except Exception as e:
        return {
            "success": False,
            "message": "Erro ao listar encarregado",
            "error": str(e),
        }

    finally:
        cur.close()
        conn.close()

@mcp.tool
def update_encaregado(
    encarregado_id: int,
    updates: dict | str,
    sessionId: str | None = None,
    action: str | None = None,
    chatInput: str | None = None,
    toolCallId: str | None = None,
) -> dict:
    """
    Atualiza um encarregado na tabela 'encarregados' usando o ID.
    updates pode ser dict ou JSON string.
    """
    _consume_meta(sessionId, action, chatInput, toolCallId)

    conn = get_connection()
    if not conn:
        return {"success": False, "message": "Sem conexão ao banco"}

    cur = conn.cursor()
    try:
        updates_dict = json.loads(updates) if isinstance(updates, str) else updates

        if not isinstance(updates_dict, dict):
            return {"success": False, "message": "Updates deve ser dict/JSON"}

        # validar colunas da tabela
        cur.execute("DESCRIBE `encarregados`")
        colunas = [c[0] for c in cur.fetchall()]

        validos = {k: v for k, v in updates_dict.items() if k in colunas}

        if not validos:
            return {
                "success": False,
                "message": "Nenhuma coluna válida para update",
                "error": f"Colunas disponíveis: {', '.join(colunas)}"
            }

        set_clause = ", ".join(f"`{k}`=%s" for k in validos.keys())
        params = list(validos.values()) + [encarregado_id]

        sql = f"UPDATE `encarregados` SET {set_clause} WHERE id=%s"
        cur.execute(sql, params)
        conn.commit()

        return {
            "success": True,
            "message": "encarregado atualizado com sucesso",
            "rows_affected": cur.rowcount,
            "error": None
        }

    except Exception as e:
        conn.rollback()
        return {"success": False, "message": "Erro ao atualizar aluno", "error": str(e)}

    finally:
        cur.close()
        conn.close()

@mcp.tool
def delete_encarregado(
    encarregado_id: int,
    sessionId: str | None = None,
    action: str | None = None,
    chatInput: str | None = None,
    toolCallId: str | None = None,
) -> dict:
    """
    Remove um encarregado da tabela 'encarregados' pelo ID.
    """
    _consume_meta(sessionId, action, chatInput, toolCallId)

    conn = get_connection()
    if not conn:
        return {"success": False, "message": "Sem conexão ao banco"}

    cur = conn.cursor()
    try:
        sql = "DELETE FROM `encarregados` WHERE id = %s"
        cur.execute(sql, (encarregado_id,))
        conn.commit()

        return {
            "success": True,
            "message": "encarregado removido com sucesso",
            "rows_affected": cur.rowcount,
            "error": None
        }

    except Exception as e:
        conn.rollback()
        return {"success": False, "message": "Erro ao remover encarregado", "error": str(e)}

    finally:
        cur.close()
        conn.close()

#####################################--alunos_encarregados--##########################################


@mcp.tool
def insert_alunos_encarregados(
    data: Union[str, dict],
    sessionId: str | None = None,
    action: str | None = None,
    chatInput: str | None = None,
    toolCallId: str | None = None,
) -> dict:
    """
    Insere um registro de relação na tabela 'alunos_encarregados'.
    Somente colunas válidas da tabela 'alunos_encarregados' serão aceitas.

    data: dict ou JSON string com os campos do encarregado
    Ex: {"nome":"João", "idade":16, "email":"joao@gmail.com"}
    """
    _consume_meta(sessionId, action, chatInput, toolCallId)

    conn = get_connection()
    if conn is None:
        return {
            "success": False,
            "message": "Sem conexão ao banco",
            "error": "get_connection retornou None",
        }

    cur = conn.cursor()
    try:
        # Converte string JSON para dict
        data_dict = json.loads(data) if isinstance(data, str) else data

        if not isinstance(data_dict, dict):
            return {
                "success": False,
                "message": "Dados devem ser um dict/JSON",
                "error": "Formato inválido",
            }

        # Descreve tabela encarregado para validar colunas
        cur.execute("DESCRIBE `alunos_encarregados`")
        cols_info = cur.fetchall()
        col_names = [c[0] for c in cols_info]

        # Filtra somente colunas válidas
        valid_data = {k: v for k, v in data_dict.items() if k in col_names}

        if not valid_data:
            return {
                "success": False,
                "message": "Nenhuma coluna válida para 'alunos_encarregados'",
                "error": f"Colunas disponíveis: {', '.join(col_names)}",
            }

        columns = ", ".join([f"`{c}`" for c in valid_data.keys()])
        placeholders = ", ".join(["%s"] * len(valid_data))
        values = tuple(valid_data.values())

        sql = f"INSERT INTO `alunos_encarregados` ({columns}) VALUES ({placeholders})"

        cur.execute(sql, values)
        conn.commit()

        return {
            "success": True,
            "message": "encarregado inserido com sucesso",
            "row_id": cur.lastrowid,
            "rows_affected": cur.rowcount,
            "error": None,
        }

    except Exception as e:
        conn.rollback()
        return {
            "success": False,
            "message": "Erro ao inserir encarregado",
            "error": str(e),
        }

    finally:
        cur.close()
        conn.close()

@mcp.tool
def list_alunos_encarregados(
    sessionId: str | None = None,
    action: str | None = None,
    chatInput: str | None = None,
    toolCallId: str | None = None,
) -> dict:
    """
    Lista todos os registros da tabela 'alunos_encarregados'.
    Retorna: {success, rows, columns, row_count, error}
    """
    _consume_meta(sessionId, action, chatInput, toolCallId)

    conn = get_connection()
    if conn is None:
        return {
            "success": False,
            "message": "Sem conexão ao banco",
            "error": "get_connection retornou None",
        }

    cur = conn.cursor(dictionary=True)
    try:
        cur.execute("SELECT * FROM `alunos_encarregados`")
        rows = cur.fetchall()
        columns = list(rows[0].keys()) if rows else []

        return {
            "success": True,
            "rows": rows,
            "columns": columns,
            "row_count": len(rows),
            "error": None,
        }

    except Exception as e:
        return {
            "success": False,
            "message": "Erro ao listar alunos_encarregados",
            "error": str(e),
        }

    finally:
        cur.close()
        conn.close()

@mcp.tool
def update_encaregados(
    encarregado_id: int,
    updates: dict | str,
    sessionId: str | None = None,
    action: str | None = None,
    chatInput: str | None = None,
    toolCallId: str | None = None,
) -> dict:
    """
    Atualiza um encarregado na tabela 'alunos_encarregados' usando o ID.
    updates pode ser dict ou JSON string.
    """
    _consume_meta(sessionId, action, chatInput, toolCallId)

    conn = get_connection()
    if not conn:
        return {"success": False, "message": "Sem conexão ao banco"}

    cur = conn.cursor()
    try:
        updates_dict = json.loads(updates) if isinstance(updates, str) else updates

        if not isinstance(updates_dict, dict):
            return {"success": False, "message": "Updates deve ser dict/JSON"}

        # validar colunas da tabela
        cur.execute("DESCRIBE `alunos_encarregados`")
        colunas = [c[0] for c in cur.fetchall()]

        validos = {k: v for k, v in updates_dict.items() if k in colunas}

        if not validos:
            return {
                "success": False,
                "message": "Nenhuma coluna válida para update",
                "error": f"Colunas disponíveis: {', '.join(colunas)}"
            }

        set_clause = ", ".join(f"`{k}`=%s" for k in validos.keys())
        params = list(validos.values()) + [encarregado_id]

        sql = f"UPDATE `alunos_encarregados` SET {set_clause} WHERE id=%s"
        cur.execute(sql, params)
        conn.commit()

        return {
            "success": True,
            "message": "encarregado atualizado com sucesso",
            "rows_affected": cur.rowcount,
            "error": None
        }

    except Exception as e:
        conn.rollback()
        return {"success": False, "message": "Erro ao atualizar aluno", "error": str(e)}

    finally:
        cur.close()
        conn.close()

@mcp.tool
def delete_alunos_encarregados(
    encarregado_id: int,
    sessionId: str | None = None,
    action: str | None = None,
    chatInput: str | None = None,
    toolCallId: str | None = None,
) -> dict:
    """
    Remove um encarregado da tabela 'alunos_encarregados' pelo ID.
    """
    _consume_meta(sessionId, action, chatInput, toolCallId)

    conn = get_connection()
    if not conn:
        return {"success": False, "message": "Sem conexão ao banco"}

    cur = conn.cursor()
    try:
        sql = "DELETE FROM `alunos_encarregados` WHERE id = %s"
        cur.execute(sql, (encarregado_id,))
        conn.commit()

        return {
            "success": True,
            "message": "encarregado removido com sucesso",
            "rows_affected": cur.rowcount,
            "error": None
        }

    except Exception as e:
        conn.rollback()
        return {"success": False, "message": "Erro ao remover encarregado", "error": str(e)}

    finally:
        cur.close()
        conn.close()
@mcp.tool
def get_aluno_encarregado(
    aluno_input: str | None = None,   # nome, apelido ou nome completo
    numero_aluno: int | None = None,
    sessionId: str | None = None,
    action: str | None = None,
    chatInput: str | None = None,
    toolCallId: str | None = None,
) -> dict:

    _consume_meta(sessionId, action, chatInput, toolCallId)

    if not aluno_input and not numero_aluno:
        return {
            "success": False,
            "message": "Informe número do aluno ou nome completo.",
            "error": "missing_parameters"
        }

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
            """, (
                termo, termo, termo,
                f"%{termo}%", f"%{termo}%", f"%{termo}%"
            ))

            aluno = cur.fetchone()

        if not aluno:
            return {
                "success": False,
                "message": f"Aluno '{aluno_input}' não encontrado.",
                "error": "not_found"
            }

        aluno_id = aluno["numero_aluno"]


        cur.execute("""
            SELECT id_encarregado, laco_familiar
            FROM alunos_encarregados 
            WHERE numero_aluno = %s
        """, (aluno_id,))

        relacoes = cur.fetchall()

        if not relacoes:
            return {
                "success": True,
                "message": "Aluno encontrado, mas não possui encarregado.",
                "aluno": aluno,
                "encarregados": []
            }

        # IDs dos encarregados
        ids = [r["id_encarregado"] for r in relacoes]
        placeholders = ", ".join(["%s"] * len(ids))

        cur.execute(f"SELECT * FROM encarregados WHERE id IN ({placeholders})", tuple(ids))
        encarregados = cur.fetchall()

        # adicionar laço familiar
        for enc in encarregados:
            for r in relacoes:
                if enc["id"] == r["id_encarregado"]:
                    enc["laco_familiar"] = r["laco_familiar"]

        return {
            "success": True,
            "message": "Consulta realizada com sucesso.",
            "aluno": aluno,
            "encarregados": encarregados
        }

    except Exception as e:
        return {"success": False, "message": "Erro interno.", "error": str(e)}

    finally:
        cur.close()
        conn.close()


if __name__ == "__main__":
    mcp.run()

