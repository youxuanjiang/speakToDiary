from flask import Flask, request, jsonify
import os
import model_manager  # 👈 載入你剛剛那份快取邏輯

app = Flask(__name__)

@app.route("/transcribe", methods=["POST"])
def transcribe():
    model_name = request.args.get("model", "base")
    file = request.files.get("audio")
    if not file:
        return jsonify({"error": "Missing audio file"}), 400

    file_path = f"/tmp/{file.filename}"
    file.save(file_path)

    try:
        model = model_manager.get_model(model_name)
        result = model.transcribe(file_path, language="zh")
        os.remove(file_path)
        return jsonify({"transcript": result["text"]})
    except Exception as e:
        return jsonify({"error": "Transcription failed", "details": str(e)}), 500

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000)