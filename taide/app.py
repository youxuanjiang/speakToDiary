from flask import Flask, request, jsonify
import subprocess
import os
import re

app = Flask(__name__)

app.config['JSON_AS_ASCII'] = False 

# 模型與 llama 可執行檔的路徑
MODEL_PATH = "/models/taide-8.1b.gguf"
LLAMA_BIN = "/app/llama.cpp/build/bin/llama-cli"

@app.route("/health")
def health():
    return jsonify({"status": "ok"})

@app.route("/summarize", methods=["POST"])
def summarize():
    try:
        data = request.get_json()
        if not data or "text" not in data:
            return jsonify({"error": "Missing 'text' in request body"}), 400

        prompt = f"""你是一位日記整理師，請幫我以條列式簡要整理以下文字內容：
                        - 每項重點限制在一行內
                        - 不要加入延伸想像或情感投射
                        - 僅根據原文整理實際發生的事件與感受
                        - 最後給一句簡短評價（不超過 20 字）
                        - 可以使用 emoji 增加活潑感
                        - 產出的內容開頭請用 「@qwer@日記整理：」開始

                        以下是文字內容：
                        {data['text']}
                        """

        # 呼叫 llama.cpp CLI
        result = subprocess.run(
            [LLAMA_BIN, "-m", MODEL_PATH, "-p", prompt, "-n", "512", "--repeat-penalty", "1.2"],
            capture_output=True,
            text=True
        )

        if result.returncode != 0:
            return jsonify({"error": "LLM execution failed", "details": result.stderr}), 500

        raw_output = result.stdout.strip()
        clean_output = extract_summary(raw_output)

        return jsonify({"summary": clean_output})

    except Exception as e:
        return jsonify({"error": "Internal error", "details": str(e)}), 500


def extract_summary(output: str) -> str:
    """
    萃取從第二個 @qwer@日記整理： 開始到 > EOF by user 結束的條列摘要與評價。
    """
    try:
        # 1. 先找到所有 "@qwer@日記整理：" 出現的位置
        parts = output.split("@qwer@日記整理：")

        if len(parts) < 2:
            # 沒有兩個的話，只能保底硬撈
            lines = output.strip().splitlines()
            bullet_lines = [line for line in lines if line.strip().startswith("-")]
            comment_lines = [line for line in lines if "評價" in line]
            return "\n".join(bullet_lines + comment_lines)

        # 2. 取第二個 @qwer@日記整理：之後的文字
        content_after_marker = parts[2]

        # 3. 移除 > EOF by user 或其後雜訊
        content_clean = content_after_marker.split("> EOF by user")[0]

        # 4. 去除頭尾空白
        content_clean = content_clean.strip()

        return content_clean

    except Exception as e:
        return f"⚠️ 摘要處理失敗：{str(e)}"

if __name__ == "__main__":
    port = int(os.environ.get("PORT", 5000))
    app.run(host="0.0.0.0", port=port)