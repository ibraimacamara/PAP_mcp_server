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




# ---------------------------------------------------------------------------
# STARTUP
# ---------------------------------------------------------------------------

if __name__ == "__main__":
    port = int(os.getenv("MCP_PORT", 8000))
    print(f"Starting MCP server (sse) on http://0.0.0.0:{port}/sse")
    mcp.run(transport="sse", host="0.0.0.0", port=port)