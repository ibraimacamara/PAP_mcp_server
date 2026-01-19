import mysql.connector
from mysql.connector import Error

def get_connection():
    try:
        conn= mysql.connector.connect(
            host= "localhost",
            user= "root",
            password="",
            db= "gestor_escola"
        )
        return conn
    except Error as e:
        print("Falha na conexão:", e)
        return None

def init_db():

    conn= get_connection()
    if conn is None:
        return
    
    conn.close()