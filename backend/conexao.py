import os
import mysql.connector
from mysql.connector import Error
from dotenv import load_dotenv

load_dotenv()

def get_connection():
    try:
        conn = mysql.connector.connect(
            host=os.getenv("DB_HOST", "localhost"),
            user=os.getenv("DB_USER", "root"),
            password=os.getenv("DB_PASSWORD", ""),
            database=os.getenv("DB_NAME", "sgei")
        )
        return conn
    except Error as e:
        print("Falha na conexão:", e)
        return None

def init_db():
    conn = get_connection()
    if conn is None:
        return
    conn.close()
