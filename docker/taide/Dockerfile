FROM debian:bullseye-slim

ENV DEBIAN_FRONTEND=noninteractive
ENV LLAMA_REPO=https://github.com/ggerganov/llama.cpp

WORKDIR /app

# 安裝必要套件
RUN apt-get update && apt-get install -y \
    build-essential cmake git python3 python3-pip ffmpeg curl libcurl4-openssl-dev \
    && apt-get clean

# 1. clone repo
RUN git clone https://github.com/ggerganov/llama.cpp llama.cpp

# 2. 編譯（已經在 llama.cpp 目錄內）
RUN mkdir -p llama.cpp/build && \
    cd llama.cpp/build && \
    cmake .. && \
    make -j

# 安裝 Flask
RUN pip3 install flask

# 複製 API 檔案
COPY app.py /app/app.py

CMD ["python3", "app.py"]