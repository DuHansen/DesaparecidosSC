import requests
import uuid
import os
import shutil

def download_image(url: str, folder="/tmp"):
    os.makedirs(folder, exist_ok=True)
    file_path = os.path.join(folder, f"{uuid.uuid4()}.jpg")
    try:
        response = requests.get(url, stream=True, timeout=5)
        if response.status_code == 200:
            with open(file_path, 'wb') as f:
                shutil.copyfileobj(response.raw, f)
            return file_path
        raise Exception(f"Erro ao baixar imagem: {url} - Código: {response.status_code}")
    except Exception as e:
        raise Exception(f"Erro ao baixar imagem: {url} - {e}")

