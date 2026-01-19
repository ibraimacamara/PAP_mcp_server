####__ferramenta de buscat encarregado na tabela alunos encarregados


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
    aluno_nome: str | None = None,
    numero_aluno: int | None = None,
    sessionId: str | None = None,
    action: str | None = None,
    chatInput: str | None = None,
    toolCallId: str | None = None,
) -> dict:

    _consume_meta(sessionId, action, chatInput, toolCallId)

    if not aluno_nome and not numero_aluno:
        return {
            "success": False,
            "message": "É necessário informar aluno_nome ou numero_aluno.",
            "error": "missing_parameters"
        }

    conn = get_connection()
    if conn is None:
        return {
            "success": False,
            "message": "Sem conexão ao banco",
            "error": "get_connection retornou None",
        }

    cur = conn.cursor(dictionary=True)

    try:
        # 1️⃣ Buscar o aluno
        if aluno_nome:
            cur.execute("SELECT * FROM alunos WHERE nome = %s", (aluno_nome,))
        else:
            cur.execute("SELECT * FROM alunos WHERE numero_aluno = %s", (numero_aluno,))

        aluno = cur.fetchone()

        if not aluno:
            return {
                "success": False,
                "message": "Aluno não encontrado.",
                "error": None
            }

        aluno_id = aluno["numero_aluno"]

        # 2️⃣ Buscar relações na tabela ALUNOS_ENCARREGADOS (COLUNA CORRETA!)
        cur.execute(
            "SELECT id_encarregado, laco_familiar FROM alunos_encarregados WHERE numero_aluno = %s",
            (aluno_id,)
        )
        relacoes = cur.fetchall()

        if not relacoes:
            return {
                "success": True,
                "message": "O aluno existe, mas não possui encarregados associados.",
                "aluno": aluno,
                "encarregados": [],
                "error": None
            }

        ids = [r["id_encarregado"] for r in relacoes]

        # 3️⃣ Buscar encarregados pelo ID
        placeholders = ", ".join(["%s"] * len(ids))
        cur.execute(
            f"SELECT * FROM encarregados WHERE id IN ({placeholders})",
            tuple(ids)
        )
        encarregados = cur.fetchall()

        # anexar laço familiar
        for enc in encarregados:
            for rel in relacoes:
                if rel["id_encarregado"] == enc["id"]:
                    enc["laco_familiar"] = rel["laco_familiar"]

        return {
            "success": True,
            "message": "Consulta realizada com sucesso.",
            "aluno": aluno,
            "encarregados": encarregados,
            "error": None
        }

    except Exception as e:
        return {
            "success": False,
            "message": "Erro ao consultar encarregados do aluno.",
            "error": str(e)
        }

    finally:
        cur.close()
        conn.close()


