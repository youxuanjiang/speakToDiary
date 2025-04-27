import whisper

_current_model = None
_current_model_name = None

def get_model(model_name):
    global _current_model, _current_model_name

    # 如果模型一樣，就直接重用
    if _current_model and model_name == _current_model_name:
        return _current_model

    print(f"[Whisper] 🔄 重新載入模型：{model_name}")
    _current_model = whisper.load_model(model_name)
    _current_model_name = model_name
    return _current_model