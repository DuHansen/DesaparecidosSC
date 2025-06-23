from deepface import DeepFace
import numpy as np
import json
from App.db import atualizar_embedding, get_embeddings
from App.image_utils import download_image
import os

def gerar_embedding(image_path: str, model_name="VGG-Face"):
    embedding_data = DeepFace.represent(img_path=image_path, model_name=model_name, enforce_detection=False)
    return embedding_data[0]['embedding']

def treinar_desaparecidos(lista):
    for item in lista:
        try:
            print(f"Treinando ID {item['id']} - {item['foto']}")
            path = download_image(item["foto"])
            embedding = gerar_embedding(path)
            os.remove(path)
            atualizar_embedding(item["id"], json.dumps(embedding))
            print(f"✅ ID {item['id']} treinado com sucesso.")
        except Exception as e:
            print(f"[ERRO] ID {item['id']}: {e}")
            continue

def carregar_embeddings_otimizado():
    desaparecidos = get_embeddings()
    embeddings = []
    ids = []
    nomes = []
    datas = []
    cidades = []
    fotos = []

    for pessoa in desaparecidos:
        emb_vetor = np.array(json.loads(pessoa['face_embedding']), dtype=np.float32)
        embeddings.append(emb_vetor)
        ids.append(pessoa['id'])
        nomes.append(pessoa['nome_completo'])
        data_obj = pessoa.get("data_desaparecimento")
        datas.append(data_obj.strftime("%Y-%m-%d") if data_obj else None)
        cidades.append(pessoa.get("cidade"))
        fotos.append(pessoa.get("foto"))

    embeddings_np = np.stack(embeddings)
    return embeddings_np, ids, nomes, datas, cidades, fotos

class ReconhecimentoFacial:
    def __init__(self, threshold=0.35):
        self.threshold = threshold
        self.embeddings_db, self.ids, self.nomes, self.datas, self.cidades, self.fotos = carregar_embeddings_otimizado()

    def comparar(self, image_path: str):
        query_emb = np.array(gerar_embedding(image_path), dtype=np.float32)
        distancias = np.linalg.norm(self.embeddings_db - query_emb, axis=1)

        menor_distancia_idx = np.argmin(distancias)
        menor_distancia = distancias[menor_distancia_idx]

        # ✅ Cálculo da similaridade como percentual (acurácia)
        accuracy = max(0, 100 - (menor_distancia / self.threshold) * 100)
        accuracy = float(round(accuracy, 2))  # <- fix: convert to native float

        if menor_distancia < self.threshold:
            return {
                "match": True,
                "id": self.ids[menor_distancia_idx],
                "nome": self.nomes[menor_distancia_idx],
                "data": self.datas[menor_distancia_idx],
                "cidade": self.cidades[menor_distancia_idx],
                "foto": self.fotos[menor_distancia_idx],
                "distancia": float(menor_distancia),
                "accuracy": accuracy
            }

        return {
            "match": False,
            "distancia": float(menor_distancia),
            "accuracy": accuracy
        }
