from fastmcp import FastMCP
from typing import Optional, Union
import json
from conexao import get_connection

mcp = FastMCP("Gestor Escolar MCP")

ALLOWED_TABLES = {
    "aluno",
    "encarregado",
    "aluno_encarregado",
    "professor",
    "curso",
    "turma",
    "aluno_turma",
    "aluno_encarregado",
    "aluno_curso",
    "users"
}


@mcp.tool
def crud(
    table: str,
    operation: str,
    data: Optional[Union[str, dict]] = None,
    filters: Optional[Union[str, dict]] = None,
    sessionId: str | None = None,
    action: str | None = None,
    chatInput: str | None = None,
    toolCallId: str | None = None,
) -> dict:

    if table not in ALLOWED_TABLES:
        return {"success": False, "error": "Tabela não permitida"}

    conn = get_connection()
    if not conn:
        return {"success": False, "error": "Sem conexão"}

    cur = conn.cursor(dictionary=True)

    try:

        if isinstance(data, str):
            data = json.loads(data)

        if isinstance(filters, str):
            filters = json.loads(filters)

        # obter colunas válidas
        cur.execute(f"DESCRIBE `{table}`")
        table_info = cur.fetchall()
        valid_columns = {col["Field"] for col in table_info}

        # ---------------- INSERT ----------------
        if operation == "insert":

            if not isinstance(data, dict):
                return {"success": False, "error": "Data inválida"}

            filtered = {k: v for k, v in data.items() if k in valid_columns}

            if not filtered:
                return {"success": False, "error": "Nenhuma coluna válida"}

            cols = ", ".join(f"`{c}`" for c in filtered.keys())
            placeholders = ", ".join(["%s"] * len(filtered))
            values = tuple(filtered.values())

            sql = f"INSERT INTO `{table}` ({cols}) VALUES ({placeholders})"
            cur.execute(sql, values)
            conn.commit()

            return {
                "success": True,
                "operation": "insert",
                "rows_affected": cur.rowcount
            }

        # ---------------- LIST ----------------
        elif operation == "list":

            if filters:
                where_clause = " AND ".join(f"`{k}`=%s" for k in filters.keys() if k in valid_columns)
                values = tuple(filters[k] for k in filters if k in valid_columns)

                sql = f"SELECT * FROM `{table}` WHERE {where_clause}"
                cur.execute(sql, values)
            else:
                sql = f"SELECT * FROM `{table}`"
                cur.execute(sql)

            rows = cur.fetchall()

            return {
                "success": True,
                "operation": "list",
                "rows": rows,
                "row_count": len(rows)
            }

        # ---------------- UPDATE ----------------
        elif operation == "update":

            if not data or not filters:
                return {"success": False, "error": "Data e filters obrigatórios"}

            filtered_data = {k: v for k, v in data.items() if k in valid_columns}
            filtered_filters = {k: v for k, v in filters.items() if k in valid_columns}

            if not filtered_data or not filtered_filters:
                return {"success": False, "error": "Dados ou filtros inválidos"}

            set_clause = ", ".join(f"`{k}`=%s" for k in filtered_data.keys())
            where_clause = " AND ".join(f"`{k}`=%s" for k in filtered_filters.keys())

            values = list(filtered_data.values()) + list(filtered_filters.values())

            sql = f"UPDATE `{table}` SET {set_clause} WHERE {where_clause}"
            cur.execute(sql, values)
            conn.commit()

            return {
                "success": True,
                "operation": "update",
                "rows_affected": cur.rowcount
            }

        # ---------------- DELETE ----------------
        elif operation == "delete":

            if not filters:
                return {"success": False, "error": "Filters obrigatórios"}

            filtered_filters = {k: v for k, v in filters.items() if k in valid_columns}

            if not filtered_filters:
                return {"success": False, "error": "Filtros inválidos"}

            where_clause = " AND ".join(f"`{k}`=%s" for k in filtered_filters.keys())
            values = tuple(filtered_filters.values())

            sql = f"DELETE FROM `{table}` WHERE {where_clause}"
            cur.execute(sql, values)
            conn.commit()

            return {
                "success": True,
                "operation": "delete",
                "rows_affected": cur.rowcount
            }

        else:
            return {"success": False, "error": "Operação inválida"}

    except Exception as e:
        conn.rollback()
        return {"success": False, "error": str(e)}

    finally:
        cur.close()
        conn.close()


if __name__ == "__main__":
    mcp.run()