from fastapi import FastAPI, UploadFile, File, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from App.db import get_desaparecidos_sem_embedding
from App.face_utils import (
    treinar_desaparecidos,
    ReconhecimentoFacial
)
import shutil
import uuid
import os
import tempfile
from dotenv import load_dotenv

# 🔧 Carrega o .env
load_dotenv(dotenv_path="App/.env")

app = FastAPI()


app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Carregar embeddings uma única vez ao iniciar a aplicação
reconhecedor = ReconhecimentoFacial(threshold=0.35)

@app.post("/treinar/")
def treinar_embeddings():
    lista = get_desaparecidos_sem_embedding()

    if not lista:
        return {"mensagem": "Todos já foram treinados."}

    treinar_desaparecidos(lista)

    # Após treinamento, recarrega embeddings atualizados
    global reconhecedor
    reconhecedor = ReconhecimentoFacial(threshold=0.35)

    return {
        "mensagem": "Embeddings atualizados com sucesso.",
        "total_treinados": len(lista)
    }

@app.post("/comparar/")
async def comparar_imagem(file: UploadFile = File(...)):
    try:
        temp_dir = tempfile.gettempdir()
        temp_path = os.path.join(temp_dir, f"{uuid.uuid4()}.jpg")

        with open(temp_path, "wb") as f:
            shutil.copyfileobj(file.file, f)

        resultado = reconhecedor.comparar(temp_path)
        os.remove(temp_path)
        
        return resultado

    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
