# Use imagem com suporte ao TensorFlow e dependências científicas
FROM python:3.10-slim

# Evita prompts interativos
ENV DEBIAN_FRONTEND=noninteractive

# Instalações de sistema para DeepFace + OpenCV + requests
RUN apt-get update && apt-get install -y \
    build-essential \
    cmake \
    libglib2.0-0 \
    libsm6 \
    libxrender-dev \
    libxext6 \
    ffmpeg \
    git \
    curl \
    && apt-get clean

# Criação da pasta de trabalho
WORKDIR /app

# Copiar arquivos
COPY . /app

# Instalar dependências
RUN pip install --upgrade pip
RUN pip install -r requirements.txt

# Expor a porta padrão
EXPOSE 8000

# Comando padrão
CMD ["uvicorn", "App.main:app", "--host", "0.0.0.0", "--port", "8000"]
