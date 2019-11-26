import sys
import subprocess
import re
import logging
import shutil
import os

def convert_to(folder, filename, timeout=None):
    gunicorn_logger = logging.getLogger('gunicorn.error')
    os.makedirs(folder + '/converted', exist_ok=True)

    if filename.endswith('.pdf'):
        shutil.copyfile(folder + '/' + filename, folder + '/converted/' + filename)
        return folder + '/converted/' + filename


    args = [libreoffice_exec(), '--headless', '--convert-to', 'pdf',
            '--outdir', folder + '/converted', folder + '/' + filename]

    gunicorn_logger = logging.getLogger('gunicorn.error')
    gunicorn_logger.info(args)
    process = subprocess.run(args, stdout=subprocess.PIPE,
                             stderr=subprocess.PIPE, timeout=timeout)
    filename = re.search('-> (.*?) using filter', process.stdout.decode())

    if filename is None:
        raise LibreOfficeError(process.stdout.decode())
    else:
        return filename.group(1)


def libreoffice_exec():
    return 'libreoffice'


class LibreOfficeError(Exception):
    def __init__(self, output):
        self.output = output


if __name__ == '__main__':
    print('Converted to ' + convert_to(sys.argv[1], sys.argv[2]))
