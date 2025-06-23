import mysql.connector
import os
from dotenv import load_dotenv

load_dotenv()

def get_connection():
    return mysql.connector.connect(
        host=os.getenv("DB_HOST"),
        port=int(os.getenv("DB_PORT")),
        user=os.getenv("DB_USER"),
        password=os.getenv("DB_PASSWORD"),
        database=os.getenv("DB_NAME")
    )

def get_desaparecidos_sem_embedding():
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("""
        SELECT id, foto FROM desaparecidos 
        WHERE foto IS NOT NULL AND face_embedding IS NULL
    """)
    data = cursor.fetchall()
    conn.close()
    return data

def atualizar_embedding(id, embedding):
    conn = get_connection()
    cursor = conn.cursor()
    cursor.execute("""
        UPDATE desaparecidos 
        SET face_embedding = %s 
        WHERE id = %s
    """, (embedding, id))
    conn.commit()
    conn.close()

def get_embeddings():
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("""
        SELECT id, foto, nome_completo, face_embedding, data_desaparecimento, cidade
        FROM desaparecidos 
        WHERE face_embedding IS NOT NULL
    """)
    data = cursor.fetchall()
    conn.close()
    return data

