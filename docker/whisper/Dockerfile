FROM python:3.10-slim

WORKDIR /app

RUN apt-get update && apt-get install -y ffmpeg git && \
    pip install --upgrade pip && \
    pip install git+https://github.com/openai/whisper.git flask

COPY . /app

CMD ["python", "app.py"]