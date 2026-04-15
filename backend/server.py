import os
from fastmcp import FastMCP
from typing import Any, Dict, List, Tuple
import json
from conexao import get_connection, init_db

mcp = FastMCP("gestor de escola MCP Tool")


# ---------------------------------------------------------------------------
# ALUNO
# ---------------------------------------------------------------------------

@mcp.tool
def insert_aluno(data: str) -> dict:
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







# @mcp.tool
# def list_aluno() -> dict:
#     """
#     Lista todos os alunos registados no sistema, mostrando:
#     - nome do curso em vez de curso_id
#     - código da turma em vez de turma_id
#     - nome do encarregado principal em vez de encarregado_principal_id
#     - nome do encarregado secundário em vez de encarregado_secundario_id

#     Retorna: {success, rows, columns, row_count, error}
#     """
#     conn = get_connection()
#     if conn is None:
#         return {
#             "success": False,
#             "message": "Sem conexão ao banco",
#             "error": "get_connection retornou None"
#         }

#     cur = conn.cursor(dictionary=True)
#     try:
#         query = """
#             SELECT
#                 a.numero_aluno,
#                 a.nome,
#                 a.data_nascimento,
#                 a.contato,
#                 a.bi,
#                 a.email,
#                 a.morada,
#                 a.genero,
#                 a.distrito,
#                 a.freguesia,
#                 a.user_id,
#                 c.nome AS curso_nome,
#                 t.codigo AS turma_codigo,
#                 ep.nome AS encarregado_principal_nome,
#                 es.nome AS encarregado_secundario_nome
#             FROM aluno a
#             LEFT JOIN curso c
#                 ON a.curso_id = c.id
#             LEFT JOIN turma t
#                 ON a.turma_id = t.id
#             LEFT JOIN encarregado ep
#                 ON a.encarregado_principal_id = ep.id
#             LEFT JOIN encarregado es
#                 ON a.encarregado_secundario_id = es.id
#             ORDER BY a.nome ASC
#         """

#         cur.execute(query)
#         rows = cur.fetchall()
#         columns = list(rows[0].keys()) if rows else []

#         return {
#             "success": True,
#             "rows": rows,
#             "columns": columns,
#             "row_count": len(rows),
#             "error": None
#         }

#     except Exception as e:
#         return {
#             "success": False,
#             "message": "Erro ao listar alunos",
#             "error": str(e)
#         }
#     finally:
#         cur.close()
#         conn.close()


@mcp.tool
def list_aluno() -> dict:
    """
    Lista todos os alunos registados no sistema, mostrando:
    - nome do curso em vez de curso_id
    - código da turma em vez de turma_id
    - nome do encarregado principal em vez de encarregado_principal_id
    - nome do encarregado secundário em vez de encarregado_secundario_id

    Retorna: {success, rows, columns, row_count, error}
    """
    conn = get_connection()
    if conn is None:
        return {
            "success": False,
            "message": "Sem conexão ao banco",
            "error": "get_connection retornou None"
        }

    cur = conn.cursor(dictionary=True)
    try:
        query = """
            SELECT
                a.numero_aluno,
                a.nome,
                a.data_nascimento,
                a.contato,
                a.bi,
                a.email,
                a.morada,
                a.genero,
                a.distrito,
                a.freguesia,
                a.user_id,
                c.nome AS curso_nome,
                t.codigo AS turma_codigo,
                ep.nome AS encarregado_principal_nome,
                es.nome AS encarregado_secundario_nome
            FROM aluno a
            LEFT JOIN curso c
                ON a.curso_id = c.id
            LEFT JOIN turma t
                ON a.turma_id = t.id
            LEFT JOIN encarregado ep
                ON a.encarregado_principal_id = ep.id
            LEFT JOIN encarregado es
                ON a.encarregado_secundario_id = es.id
            ORDER BY a.nome ASC
        """

        cur.execute(query)
        rows = cur.fetchall()
        columns = list(rows[0].keys()) if rows else []

        return {
            "success": True,
            "rows": rows,
            "columns": columns,
            "row_count": len(rows),
            "error": None
        }

    except Exception as e:
        return {
            "success": False,
            "message": "Erro ao listar alunos",
            "error": str(e)
        }
    finally:
        cur.close()
        conn.close()
#--------------------------------------------------------------------------------------
#
############################--Encarregado--############################################
@mcp.tool
def list_encarregado() -> dict:
    """
    Lista todos os encarregados registados no sistema,
    incluindo os nomes dos seus educandos.
    """
    conn = get_connection()
    if conn is None:
        return {
            "success": False,
            "message": "Sem conexão ao banco",
            "error": "get_connection retornou None"
        }

    cur = conn.cursor(dictionary=True)
    try:
        query = """
            SELECT
                e.nome,
                e.contato,
                e.email,
                e.bi,
                e.morada,
                e.genero,
                e.distrito,
                e.freguesia,
                GROUP_CONCAT(
                    DISTINCT a.nome
                    ORDER BY a.nome ASC
                    SEPARATOR ', '
                ) AS educandos
            FROM encarregado e
            LEFT JOIN aluno a
                ON a.encarregado_principal_id = e.id
                OR a.encarregado_secundario_id = e.id
            GROUP BY
                e.nome,
                e.contato,
                e.email,
                e.bi,
                e.morada,
                e.genero,
                e.distrito,
                e.freguesia
            ORDER BY e.nome ASC
        """

        cur.execute(query)
        rows = cur.fetchall()
        columns = list(rows[0].keys()) if rows else []

        return {
            "success": True,
            "rows": rows,
            "columns": columns,
            "row_count": len(rows),
            "error": None
        }

    except Exception as e:
        return {
            "success": False,
            "message": "Erro ao listar encarregados",
            "error": str(e)
        }
    finally:
        cur.close()
        conn.close()



@mcp.tool
def list_curso() -> dict:
    """
    Lista todos os cursos registados no sistema, mostrando:
    - nome do coordenador em vez do id
    - quantidade de turmas ligadas ao curso
    - quantidade de alunos ligados ao curso

    Retorna: {success, rows, columns, row_count, error}
    """
    conn = get_connection()
    if conn is None:
        return {
            "success": False,
            "message": "Sem conexão ao banco",
            "error": "get_connection retornou None"
        }

    cur = conn.cursor(dictionary=True)

    try:
        query = """
            SELECT
                c.nome,
                c.descricao,
                c.imagem,
                p.nome AS coordenador_nome,
                COUNT(DISTINCT t.id) AS total_turmas,
                COUNT(DISTINCT a.numero_aluno) AS total_alunos,
                c.inserido_em
            FROM curso c
            LEFT JOIN professor p
                ON c.coordenador = p.id
            LEFT JOIN turma t
                ON t.curso_id = c.id
            LEFT JOIN aluno a
                ON a.curso_id = c.id
            GROUP BY
                c.id,
                c.nome,
                c.descricao,
                c.imagem,
                p.nome,
                c.inserido_em
            ORDER BY c.nome ASC
        """

        cur.execute(query)
        rows = cur.fetchall()
        columns = list(rows[0].keys()) if rows else []

        return {
            "success": True,
            "rows": rows,
            "columns": columns,
            "row_count": len(rows),
            "error": None
        }

    except Exception as e:
        return {
            "success": False,
            "message": "Erro ao listar cursos",
            "error": str(e)
        }

    finally:
        cur.close()
        conn.close()



@mcp.tool
def list_turma() -> dict:
    """
    Lista todas as turmas registadas no sistema, mostrando:
    - código da turma
    - ciclo de formação
    - nome do curso
    - nome do diretor de turma
    - total de alunos associados

    Retorna: {success, rows, columns, row_count, error}
    """
    conn = get_connection()
    if conn is None:
        return {
            "success": False,
            "message": "Sem conexão ao banco",
            "error": "get_connection retornou None"
        }

    cur = conn.cursor(dictionary=True)

    try:
        query = """
            SELECT
                t.codigo AS turma_codigo,
                t.ciclo_formacao,
                c.nome AS curso_nome,
                p.nome AS diretor_nome,
                COUNT(DISTINCT a.numero_aluno) AS total_alunos
            FROM turma t
            LEFT JOIN curso c
                ON t.curso_id = c.id
            LEFT JOIN professor p
                ON t.diretor = p.id
            LEFT JOIN aluno a
                ON a.turma_id = t.id
            GROUP BY
                t.id,
                t.codigo,
                t.ciclo_formacao,
                c.nome,
                p.nome
            ORDER BY t.codigo ASC
        """

        cur.execute(query)
        rows = cur.fetchall()
        columns = list(rows[0].keys()) if rows else []

        return {
            "success": True,
            "rows": rows,
            "columns": columns,
            "row_count": len(rows),
            "error": None
        }

    except Exception as e:
        return {
            "success": False,
            "message": "Erro ao listar turmas",
            "error": str(e)
        }

    finally:
        cur.close()
        conn.close()
        
@mcp.tool
def list_professor() -> dict:
    """
    Lista todos os professores registados no sistema, mostrando:
    - dados do professor
    - turmas onde é diretor
    - cursos onde é coordenador

    Não mostra user_id por defeito.

    Retorna: {success, rows, columns, row_count, error}
    """
    conn = get_connection()
    if conn is None:
        return {
            "success": False,
            "message": "Sem conexão ao banco",
            "error": "get_connection retornou None"
        }

    cur = conn.cursor(dictionary=True)

    try:
        query = """
            SELECT
                p.id,
                p.nome,
                p.bi,
                p.email,
                p.contato,
                p.data_nascimento,
                p.morada,
                p.nacionalidade,
                p.nif,
                p.genero,
                p.distrito,
                p.freguesia,
                p.grupo_d,
                p.tipo_c,
                p.h_profissional,
                p.h_academica,
                GROUP_CONCAT(
                    DISTINCT t.codigo
                    ORDER BY t.codigo ASC
                    SEPARATOR ', '
                ) AS turmas_como_diretor,
                GROUP_CONCAT(
                    DISTINCT c.nome
                    ORDER BY c.nome ASC
                    SEPARATOR ', '
                ) AS cursos_como_coordenador,
                p.inserido_em
            FROM professor p
            LEFT JOIN turma t
                ON t.diretor = p.id
            LEFT JOIN curso c
                ON c.coordenador = p.id
            GROUP BY
                p.id,
                p.nome,
                p.bi,
                p.email,
                p.contato,
                p.data_nascimento,
                p.morada,
                p.nacionalidade,
                p.nif,
                p.genero,
                p.distrito,
                p.freguesia,
                p.grupo_d,
                p.tipo_c,
                p.h_profissional,
                p.h_academica,
                p.inserido_em
            ORDER BY p.nome ASC
        """

        cur.execute(query)
        rows = cur.fetchall()
        columns = list(rows[0].keys()) if rows else []

        return {
            "success": True,
            "rows": rows,
            "columns": columns,
            "row_count": len(rows),
            "error": None
        }

    except Exception as e:
        return {
            "success": False,
            "message": "Erro ao listar professores",
            "error": str(e)
        }

    finally:
        cur.close()
        conn.close()
        
@mcp.tool
def list_funcionario_com_user_id() -> dict:
    """
    Lista todos os funcionários registados no sistema, incluindo user_id.
    Usar apenas quando o utilizador pedir explicitamente.
    """
    conn = get_connection()
    if conn is None:
        return {
            "success": False,
            "message": "Sem conexão ao banco",
            "error": "get_connection retornou None"
        }

    cur = conn.cursor(dictionary=True)

    try:
        query = """
            SELECT
                f.id,
                f.user_id,
                f.nome,
                f.bi,
                f.email,
                f.contato,
                f.data_nascimento,
                f.morada,
                f.nacionalidade,
                f.nif,
                f.genero,
                f.distrito,
                f.freguesia,
                f.cargo,
                f.tipo_c,
                f.h_profissional,
                f.h_academica,
                f.inserido_em
            FROM funcionario f
            ORDER BY f.nome ASC
        """

        cur.execute(query)
        rows = cur.fetchall()
        columns = list(rows[0].keys()) if rows else []

        return {
            "success": True,
            "rows": rows,
            "columns": columns,
            "row_count": len(rows),
            "error": None
        }

    except Exception as e:
        return {
            "success": False,
            "message": "Erro ao listar funcionários",
            "error": str(e)
        }

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