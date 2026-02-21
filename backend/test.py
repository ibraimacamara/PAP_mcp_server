from fastmcp import FastMCP
from typing import Union
import json
from conexao import get_connection

mcp = FastMCP("Gestor Escolar Inteligente MCP")


# ==========================================================
# 🔹 UTILITÁRIOS
# ==========================================================

def _consume_meta(*args, **kwargs):
    pass


def get_all_tables(cur):
    cur.execute("SHOW TABLES")
    return [list(t.values())[0] for t in cur.fetchall()]


def get_foreign_keys(cur, table_name: str):
    cur.execute("""
        SELECT
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_NAME = %s
        AND REFERENCED_TABLE_NAME IS NOT NULL
    """, (table_name,))
    return cur.fetchall()


def format_rows(rows: list[dict]) -> str:
    if not rows:
        return "📭 Nenhum registo encontrado."

    # Caso seja apenas uma coluna (ex: SHOW TABLES)
    if rows and len(rows[0]) == 1:
        col = list(rows[0].keys())[0]
        texto = "📋 Resultados encontrados:\n\n"
        for i, row in enumerate(rows, 1):
            texto += f"- {row[col]}\n"
        return texto.strip()

    # Caso geral
    texto = "📋 Resultados encontrados:\n\n"
    for i, row in enumerate(rows, 1):
        texto += f"{i}.\n"
        for k, v in row.items():
            texto += f"   {k}: {v}\n"
        texto += "\n"
    return texto.strip()


# ==========================================================
# 🔹 LISTAR QUALQUER TABELA
# ==========================================================

@mcp.tool
def listar_tabela(
    table: str,
    sessionId: str | None = None,
    action: str | None = None,
    chatInput: str | None = None,
    toolCallId: str | None = None,
    id: str | None = None,
    tool: str | None = None
) -> str:
    _consume_meta(sessionId, action, chatInput, toolCallId)

    conn = get_connection()
    if not conn:
        return "❌ Sem conexão com a base de dados."

    cur = conn.cursor(dictionary=True)
    try:
        tabelas = get_all_tables(cur)
        if table not in tabelas:
            return f"❌ A tabela '{table}' não existe."

        cur.execute(f"SELECT * FROM `{table}`")
        rows = cur.fetchall()
        return format_rows(rows)

    except Exception as e:
        return f"❌ Erro: {str(e)}"
    finally:
        cur.close()
        conn.close()


# ==========================================================
# 🔹 CONSULTA RELACIONAL AUTOMÁTICA
# ==========================================================

@mcp.tool
def buscar_relacionado(
    table: str,
    column: str,
    value: Union[str, int],
    sessionId: str | None = None,
    action: str | None = None,
    chatInput: str | None = None,
    toolCallId: str | None = None,
    id: str | None = None,
    tool: str | None = None
) -> str:
    _consume_meta(sessionId, action, chatInput, toolCallId)

    conn = get_connection()
    if not conn:
        return "❌ Sem conexão."

    cur = conn.cursor(dictionary=True)
    try:
        # Buscar registo principal
        sql = f"SELECT * FROM `{table}` WHERE `{column}` = %s LIMIT 1"
        cur.execute(sql, (value,))
        principal = cur.fetchone()
        if not principal:
            return "❌ Registo não encontrado."

        resultado = f"📌 Dados de {table}:\n\n"
        for k, v in principal.items():
            resultado += f"{k}: {v}\n"

        # Buscar relações
        foreign_keys = get_foreign_keys(cur, table)
        for fk in foreign_keys:
            col = fk["COLUMN_NAME"]
            ref_table = fk["REFERENCED_TABLE_NAME"]
            ref_col = fk["REFERENCED_COLUMN_NAME"]

            if col in principal and principal[col] is not None:
                cur.execute(
                    f"SELECT * FROM `{ref_table}` WHERE `{ref_col}` = %s",
                    (principal[col],)
                )
                relacionados = cur.fetchall()
                if relacionados:
                    resultado += f"\n🔗 Relacionado com {ref_table}:\n"
                    for r in relacionados:
                        for k, v in r.items():
                            resultado += f"   {k}: {v}\n"
                        resultado += "\n"

        return resultado.strip()

    except Exception as e:
        return f"❌ Erro: {str(e)}"
    finally:
        cur.close()
        conn.close()


# ==========================================================
# 🔹 CONSULTA ESPECÍFICA: ENCARREGADO DO ALUNO
# ==========================================================

@mcp.tool
def encarregado_do_aluno(
    numero_aluno: int,
    sessionId: str | None = None,
    action: str | None = None,
    chatInput: str | None = None,
    toolCallId: str | None = None,
    id: str | None = None,
    tool: str | None = None
) -> str:
    """
    Busca os encarregados de um aluno pelo numero_aluno.
    Retorna apenas os dados da tabela 'encarregados', com laco_familiar da relação.
    """
    _consume_meta(sessionId, action, chatInput, toolCallId)

    conn = get_connection()
    if not conn:
        return "❌ Sem conexão com o banco de dados."

    cur = conn.cursor(dictionary=True)
    try:
        # Busca o aluno
        cur.execute("SELECT * FROM aluno WHERE numero_aluno = %s", (numero_aluno,))
        aluno = cur.fetchone()
        if not aluno:
            return "❌ Aluno não encontrado."

        # Busca os IDs dos encarregados na tabela de relação
        cur.execute("""
            SELECT id_encarregado, laco_familiar
            FROM aluno_encarregados
            WHERE numero_aluno = %s
        """, (numero_aluno,))
        relacoes = cur.fetchall()
        if not relacoes:
            return f"📌 O aluno {aluno['nome']} não possui encarregado registado."

        # Buscar dados completos da tabela encarregados
        ids = [r["id_encarregado"] for r in relacoes]
        placeholders = ", ".join(["%s"] * len(ids))
        cur.execute(f"SELECT * FROM encarregado WHERE id IN ({placeholders})", tuple(ids))
        encarregados = cur.fetchall()

        # Combinar laco_familiar da relação com dados do encarregado
        texto = f"👨‍👩‍👧 Encarregado(s) de {aluno['nome']}:\n\n"
        for enc in encarregados:
            # Encontrar laço familiar
            laco = next((r["laco_familiar"] for r in relacoes if r["id_encarregado"] == enc["id"]), "Desconhecido")
            texto += f"Nome: {enc.get('nome')}\n"
            texto += f"Email: {enc.get('email')}\n"
            texto += f"Laço Familiar: {laco}\n"
            texto += "\n"

        return texto.strip()

    except Exception as e:
        return f"❌ Erro: {str(e)}"
    finally:
        cur.close()
        conn.close()


# ==========================================================
# 🔹 EXECUTOR SQL SEGURO
# ==========================================================

@mcp.tool
def safe_sql_executor(
    sql: str,
    sessionId: str | None = None,
    action: str | None = None,
    chatInput: str | None = None,
    toolCallId: str | None = None,
    id: str | None = None,
    tool: str | None = None
) -> str:
    _consume_meta(sessionId, action, chatInput, toolCallId)

    forbidden = ["DROP", "TRUNCATE", "ALTER", "CREATE"]
    if any(word in sql.upper() for word in forbidden):
        return "❌ Comando não permitido."

    conn = get_connection()
    if not conn:
        return "❌ Sem conexão com a base de dados."

    cur = conn.cursor(dictionary=True)
    try:
        cur.execute(sql)
        if sql.strip().upper().startswith("SELECT"):
            rows = cur.fetchall()
            return format_rows(rows)

        conn.commit()
        return f"✅ Operação concluída. Registos afetados: {cur.rowcount}"

    except Exception as e:
        conn.rollback()
        return f"❌ Erro ao executar comando: {str(e)}"
    finally:
        cur.close()
        conn.close()


if __name__ == "__main__":
    mcp.run()
