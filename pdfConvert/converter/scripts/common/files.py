# common/files.py
import os
from werkzeug.utils import secure_filename

def save_to(folder, filename, file):
    os.makedirs(folder, exist_ok=True)
    save_path = os.path.join(folder, secure_filename(filename))
    
    bytearray(file)
    newFile = open(save_path, "wb")
    newFile.write(file)

    return save_path
