import os
from fastmcp import FastMCP
from typing import Any, Dict, List, Tuple
import json
from conexao import get_connection, init_db

mcp = FastMCP("gestor de escola MCP Tool")

@mcp.tool
def db_crud(
    table: str,
    action: str,
    data: str = "",
    where: str = ""
) -> dict:
    """
    CRUD genérico para qualquer tabela da base de dados.

    action:
        - select
        - insert
        - update
        - delete

    Exemplos:

    SELECT:
    db_crud(table="aluno", action="select")

    INSERT:
    db_crud(table="aluno", action="insert", data='{"nome":"Ibra","apelido":"Camara"}')

    UPDATE:
    db_crud(table="aluno", action="update",
            data='{"email":"novo@email.com"}',
            where="numero_aluno=1")

    DELETE:
    db_crud(table="aluno", action="delete", where="numero_aluno=1")
    """

    conn = get_connection()
    if not conn:
        return {"success": False, "message": "Sem conexão com o banco"}

    cur = conn.cursor(dictionary=True)

    try:

        # bloquear comandos perigosos
        if action.lower() not in ["select", "insert", "update", "delete"]:
            return {
                "success": False,
                "message": "Ação não permitida",
                "error": "Permitido apenas select, insert, update, delete"
            }

        if action == "select":

            sql = f"SELECT * FROM `{table}`"
            if where:
                sql += f" WHERE {where}"

            cur.execute(sql)
            rows = cur.fetchall()

            return {
                "success": True,
                "rows": rows,
                "row_count": len(rows)
            }

        elif action == "insert":

            data_dict = json.loads(data)

            columns = ", ".join([f"`{c}`" for c in data_dict.keys()])
            placeholders = ", ".join(["%s"] * len(data_dict))

            sql = f"INSERT INTO `{table}` ({columns}) VALUES ({placeholders})"

            cur.execute(sql, tuple(data_dict.values()))
            conn.commit()

            return {
                "success": True,
                "message": "Registro inserido com sucesso",
                "row_id": cur.lastrowid
            }

        elif action == "update":

            data_dict = json.loads(data)

            set_clause = ", ".join([f"`{k}`=%s" for k in data_dict.keys()])

            sql = f"UPDATE `{table}` SET {set_clause}"

            if where:
                sql += f" WHERE {where}"

            cur.execute(sql, tuple(data_dict.values()))
            conn.commit()

            return {
                "success": True,
                "rows_affected": cur.rowcount
            }

        elif action == "delete":

            sql = f"DELETE FROM `{table}`"

            if where:
                sql += f" WHERE {where}"

            cur.execute(sql)
            conn.commit()

            return {
                "success": True,
                "rows_affected": cur.rowcount
            }

    except Exception as e:
        conn.rollback()
        return {
            "success": False,
            "message": "Erro na operação",
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
