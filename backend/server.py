import os
from fastmcp import FastMCP
from typing import Any, Dict, List, Tuple
import json
import hashlib
from conexao import get_connection, init_db


mcp = FastMCP("gestor de escola MCP Tool")


# ---------------------------------------------------------------------------
# ALUNO
# ---------------------------------------------------------------------------



@mcp.tool
def insert_aluno(data: str) -> dict:
    """
    Insere um aluno no sistema.

    Recebe:
    - dados pessoais do aluno;
    - nome do curso;
    - código da turma;
    - nome do encarregado principal;
    - nome do encarregado secundário (opcional).

    Fluxo:
    1. cria utilizador em users com username=email e senha baseada no BI;
    2. procura os IDs de curso, turma e encarregados;
    3. insere o aluno com esses IDs.
    """
    conn = get_connection()
    if conn is None:
        return {
            "success": False,
            "message": "Sem conexão à base de dados.",
            "error": "get_connection retornou None"
        }

    cur = conn.cursor(dictionary=True)

    try:
        data_dict = json.loads(data) if isinstance(data, str) else data

        if not isinstance(data_dict, dict):
            return {
                "success": False,
                "message": "Os dados devem ser um objeto JSON.",
                "error": "Formato inválido"
            }

        nome = str(data_dict.get("nome", "")).strip()
        email = str(data_dict.get("email", "")).strip().lower()
        bi = str(data_dict.get("bi", "")).strip()
        contato = str(data_dict.get("contato", "")).strip()
        data_nascimento = str(data_dict.get("data_nascimento", "")).strip()
        morada = str(data_dict.get("morada", "")).strip()
        genero = str(data_dict.get("genero", "")).strip().lower()
        distrito = str(data_dict.get("distrito", "")).strip()
        freguesia = str(data_dict.get("freguesia", "")).strip()

        nome_curso = str(data_dict.get("curso", "")).strip()
        codigo_turma = str(data_dict.get("turma", "")).strip()
        nome_enc_principal = str(data_dict.get("encarregado_principal", "")).strip()
        nome_enc_secundario = str(data_dict.get("encarregado_secundario", "")).strip()

        if not nome or not email or not bi or not contato or not data_nascimento:
            return {
                "success": False,
                "message": "Faltam dados obrigatórios do aluno.",
                "error": "nome, email, bi, contato e data_nascimento são obrigatórios"
            }

        if not nome_curso or not codigo_turma or not nome_enc_principal:
            return {
                "success": False,
                "message": "Faltam curso, turma ou encarregado principal.",
                "error": "curso, turma e encarregado_principal são obrigatórios"
            }

        conn.start_transaction()

        # verificar duplicados
        cur.execute("SELECT id FROM users WHERE username = %s", (email,))
        if cur.fetchone():
            conn.rollback()
            return {
                "success": False,
                "message": "Já existe utilizador com este email.",
                "error": email
            }

        cur.execute("SELECT numero_aluno FROM aluno WHERE email = %s OR bi = %s", (email, bi))
        if cur.fetchone():
            conn.rollback()
            return {
                "success": False,
                "message": "Já existe aluno com este email ou BI.",
                "error": f"{email} / {bi}"
            }

        # buscar curso_id pelo nome
        cur.execute("SELECT id FROM curso WHERE nome = %s LIMIT 1", (nome_curso,))
        curso = cur.fetchone()
        if not curso:
            conn.rollback()
            return {
                "success": False,
                "message": "Curso não encontrado.",
                "error": nome_curso
            }
        curso_id = curso["id"]

        # buscar turma_id pelo código e curso
        cur.execute(
            "SELECT id FROM turma WHERE codigo = %s AND curso_id = %s LIMIT 1",
            (codigo_turma, curso_id)
        )
        turma = cur.fetchone()
        if not turma:
            conn.rollback()
            return {
                "success": False,
                "message": "Turma não encontrada para o curso indicado.",
                "error": codigo_turma
            }
        turma_id = turma["id"]

        # buscar encarregado principal
        cur.execute("SELECT id FROM encarregado WHERE nome = %s LIMIT 1", (nome_enc_principal,))
        enc_principal = cur.fetchone()
        if not enc_principal:
            conn.rollback()
            return {
                "success": False,
                "message": "Encarregado principal não encontrado.",
                "error": nome_enc_principal
            }
        encarregado_principal_id = enc_principal["id"]

        # buscar encarregado secundário, se existir
        encarregado_secundario_id = None
        if nome_enc_secundario:
            cur.execute("SELECT id FROM encarregado WHERE nome = %s LIMIT 1", (nome_enc_secundario,))
            enc_secundario = cur.fetchone()
            if not enc_secundario:
                conn.rollback()
                return {
                    "success": False,
                    "message": "Encarregado secundário não encontrado.",
                    "error": nome_enc_secundario
                }
            encarregado_secundario_id = enc_secundario["id"]

        # criar utilizador
        senha_hash = hashlib.sha256(bi.encode("utf-8")).hexdigest()

        cur.execute("""
            INSERT INTO users (username, senha, categoria, foto, primeiro_login)
            VALUES (%s, %s, %s, %s, %s)
        """, (email, senha_hash, "aluno", None, 0))

        user_id = cur.lastrowid

        # inserir aluno
        cur.execute("""
            INSERT INTO aluno (
                user_id,
                curso_id,
                turma_id,
                encarregado_principal_id,
                encarregado_secundario_id,
                nome,
                email,
                bi,
                contato,
                data_nascimento,
                morada,
                genero,
                distrito,
                freguesia
            ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """, (
            user_id,
            curso_id,
            turma_id,
            encarregado_principal_id,
            encarregado_secundario_id,
            nome,
            email,
            bi,
            contato,
            data_nascimento,
            morada or None,
            genero or None,
            distrito or None,
            freguesia or None
        ))

        numero_aluno = cur.lastrowid
        conn.commit()

        return {
            "success": True,
            "message": "Aluno inserido com sucesso.",
            "data": {
                "numero_aluno": numero_aluno,
                "user_id": user_id,
                "curso_id": curso_id,
                "turma_id": turma_id,
                "encarregado_principal_id": encarregado_principal_id,
                "encarregado_secundario_id": encarregado_secundario_id
            },
            "error": None
        }

    except Exception as e:
        conn.rollback()
        return {
            "success": False,
            "message": "Erro ao inserir aluno.",
            "error": str(e)
        }
    finally:
        cur.close()
        conn.close()
        


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
        
        
# @mcp.tool
# def update_aluno(numero_aluno: int, updates: str) -> dict:
#     """
#     Atualiza dados de um aluno pelo seu número de identificação.
#     updates: dict ou JSON string com os campos a alterar.
#     Ex: {"email":"novo@email.com", "contato":"912345678"}
#     """
#     conn = get_connection()
#     if not conn:
#         return {"success": False, "message": "Sem conexão ao banco"}

#     cur = conn.cursor()
#     try:
#         updates_dict = json.loads(updates) if isinstance(updates, str) else updates
#         if not isinstance(updates_dict, dict):
#             return {"success": False, "message": "Updates deve ser dict/JSON"}

#         cur.execute("DESCRIBE `aluno`")
#         colunas = [c[0] for c in cur.fetchall()]
#         validos = {k: v for k, v in updates_dict.items() if k in colunas}

#         if not validos:
#             return {"success": False, "message": "Nenhuma coluna válida para update",
#                     "error": f"Colunas disponíveis: {', '.join(colunas)}"}

#         set_clause = ", ".join(f"`{k}`=%s" for k in validos.keys())
#         sql = f"UPDATE `aluno` SET {set_clause} WHERE numero_aluno=%s"
#         cur.execute(sql, list(validos.values()) + [numero_aluno])
#         conn.commit()

#         return {"success": True, "message": "Aluno atualizado com sucesso",
#                 "rows_affected": cur.rowcount, "error": None}
#     except Exception as e:
#         conn.rollback()
#         return {"success": False, "message": "Erro ao atualizar aluno", "error": str(e)}
#     finally:
#         cur.close()
#         conn.close()



@mcp.tool
def update_aluno(numero_aluno: int, updates: str) -> dict:
    """
    Atualiza dados de um aluno pelo número de identificação.

    Aceita tanto colunas diretas da tabela `aluno` como campos relacionados:
    Ex:
    {
        "email": "novo@email.com",
        "contato": "912345678",
        "curso_nome": "Informática",
        "turma_codigo": "T10A",
        "encarregado_principal_nome": "João Silva",
        "encarregado_secundario_nome": "Maria Silva"
    }
    """
    conn = get_connection()
    if not conn:
        return {"success": False, "message": "Sem conexão ao banco"}

    cur = conn.cursor(dictionary=True)
    try:
        updates_dict = json.loads(updates) if isinstance(updates, str) else updates
        if not isinstance(updates_dict, dict):
            return {"success": False, "message": "Updates deve ser dict/JSON"}

        # Verificar se o aluno existe
        cur.execute("SELECT numero_aluno FROM aluno WHERE numero_aluno = %s", (numero_aluno,))
        aluno = cur.fetchone()
        if not aluno:
            return {
                "success": False,
                "message": "Aluno não encontrado",
                "error": f"numero_aluno {numero_aluno} não existe"
            }

        # Buscar colunas reais da tabela aluno
        cur.execute("DESCRIBE `aluno`")
        colunas_aluno = [c["Field"] for c in cur.fetchall()]

        validos = {}

        # 1) Campos diretos da tabela aluno
        for k, v in updates_dict.items():
            if k in colunas_aluno:
                validos[k] = v

        # 2) Campos relacionados -> resolver para IDs
        relation_map = {
            "curso_nome": {
                "table": "curso",
                "lookup_column": "nome",
                "target_column": "curso_id"
            },
            "turma_codigo": {
                "table": "turma",
                "lookup_column": "codigo",
                "target_column": "turma_id"
            },
            "encarregado_principal_nome": {
                "table": "encarregado",
                "lookup_column": "nome",
                "target_column": "encarregado_principal_id"
            },
            "encarregado_secundario_nome": {
                "table": "encarregado",
                "lookup_column": "nome",
                "target_column": "encarregado_secundario_id"
            }
        }

        for campo_virtual, conf in relation_map.items():
            if campo_virtual in updates_dict:
                valor = updates_dict[campo_virtual]

                cur.execute(
                    f"SELECT id FROM `{conf['table']}` WHERE `{conf['lookup_column']}` = %s",
                    (valor,)
                )
                encontrado = cur.fetchone()

                if not encontrado:
                    return {
                        "success": False,
                        "message": f"Valor relacionado não encontrado para {campo_virtual}",
                        "error": f"{conf['table']}.{conf['lookup_column']}='{valor}' não existe"
                    }

                validos[conf["target_column"]] = encontrado["id"]

        if not validos:
            return {
                "success": False,
                "message": "Nenhuma coluna válida para update",
                "error": f"Colunas disponíveis em aluno: {', '.join(colunas_aluno)}; "
                         f"Relacionais aceitas: {', '.join(relation_map.keys())}"
            }

        set_clause = ", ".join(f"`{k}`=%s" for k in validos.keys())
        sql = f"UPDATE `aluno` SET {set_clause} WHERE numero_aluno=%s"

        cur.execute(sql, list(validos.values()) + [numero_aluno])
        conn.commit()

        return {
            "success": True,
            "message": "Aluno atualizado com sucesso",
            "rows_affected": cur.rowcount,
            "updated_fields": list(validos.keys()),
            "error": None
        }

    except Exception as e:
        conn.rollback()
        return {
            "success": False,
            "message": "Erro ao atualizar aluno",
            "error": str(e)
        }
    finally:
        cur.close()
        conn.close()
        
        
        
@mcp.tool
def delete_aluno(identifier: str) -> dict:
    """
    Remove um aluno e o respectivo usuário.
    O identifier pode ser numero_aluno (int em string) ou nome.
    """
    conn = get_connection()
    if not conn:
        return {"success": False, "message": "Sem conexão ao banco"}

    cur = conn.cursor()

    try:
        
        conn.start_transaction()

        if identifier.isdigit():
            cur.execute("""
                SELECT numero_aluno, user_id 
                FROM aluno 
                WHERE numero_aluno = %s
                LIMIT 1
            """, (int(identifier),))
        else:
            cur.execute("""
                SELECT numero_aluno, user_id 
                FROM aluno 
                WHERE nome = %s
                LIMIT 1
            """, (identifier,))

        aluno = cur.fetchone()

        if not aluno:
            raise Exception("Aluno não encontrado.")

        numero_aluno, user_id = aluno

        # Apagar aluno
        cur.execute("""
            DELETE FROM aluno 
            WHERE numero_aluno = %s
        """, (numero_aluno,))

        # Apagar user
        if user_id:
            cur.execute("""
                DELETE FROM users 
                WHERE id = %s
            """, (user_id,))

        conn.commit()

        return {
            "success": True,
            "message": f"Aluno {numero_aluno} removido com sucesso",
            "error": None
        }

    except Exception as e:
        conn.rollback()
        return {
            "success": False,
            "message": "Erro ao remover aluno",
            "error": str(e)
        }

    finally:
        cur.close()
        conn.close()
# ---------------------------------------------------------------------------
# Encarregado
# ---------------------------------------------------------------------------

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

import json

@mcp.tool
def update_encarregado(nome_atual: str, updates: str) -> dict:
    """
    Atualiza um encarregado procurando pelo nome atual.
    Ex:
    nome_atual = "Adão Lopes"
    updates = {"nome": "Adão Lope"}
    """
    conn = get_connection()
    if not conn:
        return {"success": False, "message": "Sem conexão ao banco"}

    cur = conn.cursor(dictionary=True)
    try:
        updates_dict = json.loads(updates) if isinstance(updates, str) else updates
        if not isinstance(updates_dict, dict):
            return {"success": False, "message": "Updates deve ser dict/JSON"}

        # Procurar encarregados com esse nome
        cur.execute("""
            SELECT id, nome
            FROM encarregado
            WHERE nome = %s
        """, (nome_atual,))
        encontrados = cur.fetchall()

        if not encontrados:
            return {
                "success": False,
                "message": "Encarregado não encontrado",
                "error": f"Nenhum encarregado com o nome '{nome_atual}'"
            }

        if len(encontrados) > 1:
            return {
                "success": False,
                "message": "Nome ambíguo",
                "error": f"Existem {len(encontrados)} encarregados com o nome '{nome_atual}'. Use o ID."
            }

        encarregado_id = encontrados[0]["id"]

        cur.execute("DESCRIBE `encarregado`")
        colunas = [c["Field"] for c in cur.fetchall()]

        bloqueadas = {"id", "user_id", "inserido_em"}
        validos = {
            k: v for k, v in updates_dict.items()
            if k in colunas and k not in bloqueadas
        }

        if not validos:
            return {
                "success": False,
                "message": "Nenhuma coluna válida para update",
                "error": f"Colunas permitidas: {', '.join([c for c in colunas if c not in bloqueadas])}"
            }

        set_clause = ", ".join(f"`{k}`=%s" for k in validos.keys())
        sql = f"UPDATE `encarregado` SET {set_clause} WHERE id=%s"
        cur.execute(sql, list(validos.values()) + [encarregado_id])
        conn.commit()

        cur.execute("""
            SELECT id, nome, contato, email
            FROM encarregado
            WHERE id = %s
        """, (encarregado_id,))
        atualizado = cur.fetchone()

        return {
            "success": True,
            "message": "Encarregado atualizado com sucesso",
            "rows_affected": cur.rowcount,
            "data": atualizado,
            "error": None
        }

    except Exception as e:
        conn.rollback()
        return {
            "success": False,
            "message": "Erro ao atualizar encarregado",
            "error": str(e)
        }
    finally:
        cur.close()
        conn.close()
# ---------------------------------------------------------------------------
# curso
# ---------------------------------------------------------------------------
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
def update_curso(nome_atual_curso: str, updates: str) -> dict:
    """
    Atualiza um curso a partir do nome atual do curso.

    Pode atualizar:
    - nome
    - descricao
    - coordenador, usando nome_professor

    Entrada:
    nome_atual_curso: nome atual do curso a localizar
    updates: dict ou JSON string com campos a alterar

    Campos aceites em updates:
    {
        "nome": "Novo nome do curso",
        "descricao": "Nova descrição",
        "nome_professor": "Helga Candeias"
    }

    Regras:
    - nome_professor é convertido para o id correspondente da tabela professor
    - o campo coordenador da tabela curso recebe esse id
    """
    conn = get_connection()
    if not conn:
        return {
            "success": False,
            "message": "Sem conexão ao banco"
        }

    cur = conn.cursor(dictionary=True)
    try:
        updates_dict = json.loads(updates) if isinstance(updates, str) else updates

        if not isinstance(updates_dict, dict):
            return {
                "success": False,
                "message": "Updates deve ser dict/JSON"
            }

        # 1. Procurar curso pelo nome atual
        cur.execute("""
            SELECT id, nome, descricao, coordenador
            FROM curso
            WHERE nome = %s
        """, (nome_atual_curso,))
        cursos = cur.fetchall()

        if not cursos:
            return {
                "success": False,
                "message": "Curso não encontrado",
                "error": f"Não existe curso com o nome '{nome_atual_curso}'"
            }

        if len(cursos) > 1:
            return {
                "success": False,
                "message": "Nome de curso ambíguo",
                "error": f"Existem {len(cursos)} cursos com o nome '{nome_atual_curso}'. Use um identificador único."
            }

        curso = cursos[0]
        curso_id = curso["id"]

        # 2. Validar campos permitidos
        campos_permitidos = {"nome", "descricao", "nome_professor"}
        invalidos = [k for k in updates_dict.keys() if k not in campos_permitidos]

        if invalidos:
            return {
                "success": False,
                "message": "Existem campos inválidos no update",
                "error": f"Campos permitidos: nome, descricao, nome_professor. Inválidos: {', '.join(invalidos)}"
            }

        dados_update = {}

        # 3. Atualizar nome, se vier
        if "nome" in updates_dict:
            novo_nome = updates_dict["nome"]
            if not isinstance(novo_nome, str) or not novo_nome.strip():
                return {
                    "success": False,
                    "message": "Nome inválido",
                    "error": "O campo 'nome' deve ser uma string não vazia"
                }
            dados_update["nome"] = novo_nome.strip()

        # 4. Atualizar descricao, se vier
        if "descricao" in updates_dict:
            nova_descricao = updates_dict["descricao"]
            if nova_descricao is None:
                nova_descricao = ""
            if not isinstance(nova_descricao, str):
                return {
                    "success": False,
                    "message": "Descrição inválida",
                    "error": "O campo 'descricao' deve ser uma string"
                }
            dados_update["descricao"] = nova_descricao.strip()

        # 5. Atualizar coordenador, se vier nome_professor
        if "nome_professor" in updates_dict:
            nome_professor = updates_dict["nome_professor"]

            if not isinstance(nome_professor, str) or not nome_professor.strip():
                return {
                    "success": False,
                    "message": "Professor inválido",
                    "error": "O campo 'nome_professor' deve ser uma string não vazia"
                }

            cur.execute("""
                SELECT id, nome
                FROM professor
                WHERE nome = %s
            """, (nome_professor.strip(),))
            professores = cur.fetchall()

            if not professores:
                return {
                    "success": False,
                    "message": "Professor não encontrado",
                    "error": f"Não existe professor com o nome '{nome_professor}'"
                }

            if len(professores) > 1:
                return {
                    "success": False,
                    "message": "Nome de professor ambíguo",
                    "error": f"Existem {len(professores)} professores com o nome '{nome_professor}'. Use um identificador único."
                }

            dados_update["coordenador"] = professores[0]["id"]

        if not dados_update:
            return {
                "success": False,
                "message": "Nenhum campo válido para atualização",
                "error": "Envie pelo menos um destes campos: nome, descricao, nome_professor"
            }

        # 6. Evitar update inútil
        mudancas_reais = {}
        for campo, valor in dados_update.items():
            if curso.get(campo) != valor:
                mudancas_reais[campo] = valor

        if not mudancas_reais:
            cur.execute("""
                SELECT
                    c.id,
                    c.nome,
                    c.descricao,
                    c.imagem,
                    c.coordenador,
                    p.nome AS coordenador_nome,
                    c.inserido_em
                FROM curso c
                LEFT JOIN professor p
                    ON c.coordenador = p.id
                WHERE c.id = %s
            """, (curso_id,))
            atual = cur.fetchone()

            return {
                "success": False,
                "message": "Nenhuma alteração foi feita",
                "rows_affected": 0,
                "data": atual,
                "error": "Os novos valores são iguais aos atuais"
            }

        # 7. Executar update
        set_clause = ", ".join(f"`{campo}` = %s" for campo in mudancas_reais.keys())
        valores = list(mudancas_reais.values()) + [curso_id]

        sql = f"UPDATE `curso` SET {set_clause} WHERE id = %s"
        cur.execute(sql, valores)
        conn.commit()

        # 8. Confirmar alteração
        cur.execute("""
            SELECT
                c.id,
                c.nome,
                c.descricao,
                c.imagem,
                c.coordenador,
                p.nome AS coordenador_nome,
                c.inserido_em
            FROM curso c
            LEFT JOIN professor p
                ON c.coordenador = p.id
            WHERE c.id = %s
        """, (curso_id,))
        atualizado = cur.fetchone()

        return {
            "success": True,
            "message": "Curso atualizado com sucesso",
            "rows_affected": cur.rowcount,
            "updated_fields": list(mudancas_reais.keys()),
            "data": atualizado,
            "error": None
        }

    except Exception as e:
        conn.rollback()
        return {
            "success": False,
            "message": "Erro ao atualizar curso",
            "error": str(e)
        }

    finally:
        cur.close()
        conn.close()

# ---------------------------------------------------------------------------
# Turma
# ---------------------------------------------------------------------------

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
        

import json

@mcp.tool
def update_turma(codigo_atual: str, updates: str) -> dict:
    """
    Atualiza dados de uma turma a partir do seu código atual.

    Pode atualizar:
    - codigo
    - ciclo_formacao
    - curso_id (a partir de novo_nome_curso)
    - diretor (a partir de novo_nome_diretor)

    Exemplo:
    {
        "novo_codigo": "PI 2ºano",
        "novo_ciclo_formacao": "2027/2030",
        "novo_nome_curso": "Programador de Informática",
        "novo_nome_diretor": "Helga Candeias"
    }
    """
    conn = get_connection()
    if not conn:
        return {
            "success": False,
            "message": "Sem conexão ao banco"
        }

    cur = conn.cursor(dictionary=True)
    try:
        updates_dict = json.loads(updates) if isinstance(updates, str) else updates

        if not isinstance(updates_dict, dict):
            return {
                "success": False,
                "message": "Updates deve ser dict/JSON"
            }

        # 1. Procurar turma pelo código atual
        cur.execute("""
            SELECT id, curso_id, diretor, codigo, ciclo_formacao
            FROM turma
            WHERE codigo = %s
        """, (codigo_atual,))
        turmas = cur.fetchall()

        if not turmas:
            return {
                "success": False,
                "message": "Turma não encontrada",
                "error": f"Não existe turma com o código '{codigo_atual}'"
            }

        if len(turmas) > 1:
            return {
                "success": False,
                "message": "Código de turma ambíguo",
                "error": f"Existem {len(turmas)} turmas com o código '{codigo_atual}'. Usa um identificador único."
            }

        turma = turmas[0]
        turma_id = turma["id"]

        # 2. Validar campos aceites
        campos_permitidos = {
            "novo_codigo",
            "novo_ciclo_formacao",
            "novo_nome_curso",
            "novo_nome_diretor"
        }

        invalidos = [k for k in updates_dict.keys() if k not in campos_permitidos]
        if invalidos:
            return {
                "success": False,
                "message": "Existem campos inválidos no update",
                "error": (
                    "Campos permitidos: novo_codigo, novo_ciclo_formacao, "
                    f"novo_nome_curso, novo_nome_diretor. Inválidos: {', '.join(invalidos)}"
                )
            }

        dados_update = {}

        # 3. Atualizar código
        if "novo_codigo" in updates_dict:
            novo_codigo = updates_dict["novo_codigo"]
            if not isinstance(novo_codigo, str) or not novo_codigo.strip():
                return {
                    "success": False,
                    "message": "Código inválido",
                    "error": "O campo 'novo_codigo' deve ser uma string não vazia"
                }
            dados_update["codigo"] = novo_codigo.strip()

        # 4. Atualizar ciclo_formacao
        if "novo_ciclo_formacao" in updates_dict:
            novo_ciclo = updates_dict["novo_ciclo_formacao"]
            if not isinstance(novo_ciclo, str) or not novo_ciclo.strip():
                return {
                    "success": False,
                    "message": "Ciclo de formação inválido",
                    "error": "O campo 'novo_ciclo_formacao' deve ser uma string não vazia"
                }
            dados_update["ciclo_formacao"] = novo_ciclo.strip()

        # 5. Atualizar curso pelo nome do curso
        if "novo_nome_curso" in updates_dict:
            novo_nome_curso = updates_dict["novo_nome_curso"]
            if not isinstance(novo_nome_curso, str) or not novo_nome_curso.strip():
                return {
                    "success": False,
                    "message": "Curso inválido",
                    "error": "O campo 'novo_nome_curso' deve ser uma string não vazia"
                }

            cur.execute("""
                SELECT id, nome
                FROM curso
                WHERE nome = %s
            """, (novo_nome_curso.strip(),))
            cursos = cur.fetchall()

            if not cursos:
                return {
                    "success": False,
                    "message": "Curso não encontrado",
                    "error": f"Não existe curso com o nome '{novo_nome_curso}'"
                }

            if len(cursos) > 1:
                return {
                    "success": False,
                    "message": "Nome de curso ambíguo",
                    "error": f"Existem {len(cursos)} cursos com o nome '{novo_nome_curso}'. Usa um identificador único."
                }

            dados_update["curso_id"] = cursos[0]["id"]

        # 6. Atualizar diretor pelo nome do professor
        if "novo_nome_diretor" in updates_dict:
            novo_nome_diretor = updates_dict["novo_nome_diretor"]
            if not isinstance(novo_nome_diretor, str) or not novo_nome_diretor.strip():
                return {
                    "success": False,
                    "message": "Diretor inválido",
                    "error": "O campo 'novo_nome_diretor' deve ser uma string não vazia"
                }

            cur.execute("""
                SELECT id, nome
                FROM professor
                WHERE nome = %s
            """, (novo_nome_diretor.strip(),))
            professores = cur.fetchall()

            if not professores:
                return {
                    "success": False,
                    "message": "Professor não encontrado",
                    "error": f"Não existe professor com o nome '{novo_nome_diretor}'"
                }

            if len(professores) > 1:
                return {
                    "success": False,
                    "message": "Nome de professor ambíguo",
                    "error": f"Existem {len(professores)} professores com o nome '{novo_nome_diretor}'. Usa um identificador único."
                }

            dados_update["diretor"] = professores[0]["id"]

        if not dados_update:
            return {
                "success": False,
                "message": "Nenhum campo válido para atualização",
                "error": "Envie pelo menos um destes campos: novo_codigo, novo_ciclo_formacao, novo_nome_curso, novo_nome_diretor"
            }

        # 7. Evitar update inútil
        mudancas_reais = {}
        for campo, valor in dados_update.items():
            if turma.get(campo) != valor:
                mudancas_reais[campo] = valor

        if not mudancas_reais:
            cur.execute("""
                SELECT
                    t.id,
                    t.codigo,
                    t.ciclo_formacao,
                    c.nome AS curso_nome,
                    p.nome AS diretor_nome,
                    t.inserido_em
                FROM turma t
                LEFT JOIN curso c ON t.curso_id = c.id
                LEFT JOIN professor p ON t.diretor = p.id
                WHERE t.id = %s
            """, (turma_id,))
            atual = cur.fetchone()

            return {
                "success": False,
                "message": "Nenhuma alteração foi feita",
                "rows_affected": 0,
                "data": atual,
                "error": "Os novos valores são iguais aos atuais"
            }

        # 8. Validar se novo código já existe noutra turma
        if "codigo" in mudancas_reais:
            cur.execute("""
                SELECT id
                FROM turma
                WHERE codigo = %s AND id <> %s
            """, (mudancas_reais["codigo"], turma_id))
            codigo_existente = cur.fetchone()

            if codigo_existente:
                return {
                    "success": False,
                    "message": "Código de turma já em uso",
                    "error": f"Já existe outra turma com o código '{mudancas_reais['codigo']}'"
                }

        # 9. Executar update
        set_clause = ", ".join(f"`{campo}` = %s" for campo in mudancas_reais.keys())
        valores = list(mudancas_reais.values()) + [turma_id]

        sql = f"UPDATE `turma` SET {set_clause} WHERE id = %s"
        cur.execute(sql, valores)
        conn.commit()

        # 10. Confirmar alteração
        cur.execute("""
            SELECT
                t.id,
                t.codigo,
                t.ciclo_formacao,
                t.curso_id,
                c.nome AS curso_nome,
                t.diretor,
                p.nome AS diretor_nome,
                t.inserido_em
            FROM turma t
            LEFT JOIN curso c ON t.curso_id = c.id
            LEFT JOIN professor p ON t.diretor = p.id
            WHERE t.id = %s
        """, (turma_id,))
        atualizada = cur.fetchone()

        return {
            "success": True,
            "message": "Turma atualizada com sucesso",
            "rows_affected": cur.rowcount,
            "updated_fields": list(mudancas_reais.keys()),
            "data": atualizada,
            "error": None
        }

    except Exception as e:
        conn.rollback()
        return {
            "success": False,
            "message": "Erro ao atualizar turma",
            "error": str(e)
        }

    finally:
        cur.close()
        conn.close()
        
# ---------------------------------------------------------------------------
# Professor
# ---------------------------------------------------------------------------        

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
        

import json

@mcp.tool
def update_professor(professor_id: int, updates: str) -> dict:
    """
    Atualiza um professor pelo ID.
    Permite editar todos os campos, excepto:
    - id
    - user_id
    - inserido_em
    """
    conn = get_connection()
    if not conn:
        return {"success": False, "message": "Sem conexão ao banco"}

    cur = conn.cursor(dictionary=True)
    try:
        updates_dict = json.loads(updates) if isinstance(updates, str) else updates

        if not isinstance(updates_dict, dict):
            return {"success": False, "message": "Updates deve ser dict/JSON"}

        # verificar se professor existe
        cur.execute("SELECT * FROM professor WHERE id = %s", (professor_id,))
        professor = cur.fetchone()

        if not professor:
            return {
                "success": False,
                "message": "Professor não encontrado",
                "error": f"Não existe professor com id={professor_id}"
            }

        # campos bloqueados
        bloqueados = {"id", "user_id", "inserido_em"}

        # filtrar campos válidos
        validos = {k: v for k, v in updates_dict.items() if k in professor and k not in bloqueados}

        if not validos:
            return {
                "success": False,
                "message": "Nenhum campo válido para atualizar",
                "error": "Campos não permitidos: id, user_id, inserido_em"
            }

        # montar update
        set_clause = ", ".join(f"`{campo}` = %s" for campo in validos.keys())
        valores = list(validos.values()) + [professor_id]

        sql = f"UPDATE professor SET {set_clause} WHERE id = %s"
        cur.execute(sql, valores)
        conn.commit()

        # buscar dados atualizados
        cur.execute("""
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
                p.inserido_em
            FROM professor p
            WHERE p.id = %s
        """, (professor_id,))
        atualizado = cur.fetchone()

        return {
            "success": True,
            "message": "Professor atualizado com sucesso",
            "rows_affected": cur.rowcount,
            "data": atualizado,
            "error": None
        }

    except Exception as e:
        conn.rollback()
        return {
            "success": False,
            "message": "Erro ao atualizar professor",
            "error": str(e)
        }

    finally:
        cur.close()
        conn.close()
# ---------------------------------------------------------------------------
# Funcionario
# ---------------------------------------------------------------------------
        
@mcp.tool
def list_funcionario() -> dict:
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
# ---------------------------------------------------------------------------
# STARTUP
# ---------------------------------------------------------------------------

if __name__ == "__main__":
    port = int(os.getenv("MCP_PORT", 8000))
    print(f"Starting MCP server (sse) on http://0.0.0.0:{port}/sse")
    mcp.run(transport="sse", host="0.0.0.0", port=port)