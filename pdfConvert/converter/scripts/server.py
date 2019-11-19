import os
import logging
import re
import io
from uuid import uuid4
from flask import Flask, render_template, request, jsonify, send_from_directory, send_file
from subprocess import TimeoutExpired
from common.docx2pdf import LibreOfficeError, convert_to
from common.errors import RestAPIError, InternalServerErrorError
from common.files import save_to
from werkzeug.serving import run_simple
from werkzeug.wsgi import DispatcherMiddleware
import requests
import base64
import json
from datetime import datetime
from PyPDF2 import PdfFileMerger

app = Flask(__name__, static_url_path='')
app.config['APPLICATION_ROOT'] = '/converter'


@app.after_request  # blueprint can also be app~~
def after_request(response):
    header = response.headers
    header['Access-Control-Allow-Origin'] = '*'
    header['Access-Control-Allow-Headers'] = '*'
    return response


@app.route('/')
def index():
    return 'hello world'


@app.route('/convert', methods=['GET'])
def upload_file_get():
    app.logger.info('Testlog')
    return 'convert here'


@app.route('/convert', methods=['POST'])
def upload_file():
    app.logger.info(request.headers)
    app.logger.info(request.cookies)
    app.logger.info(request.json)
    fileList = request.get_json()
    app.logger.info(fileList)
    results = []
    submissionId = 0
    stageId = 0

    for file in fileList['files']:
        app.logger.info('Convert file ' + file['fileName'])
        submissionId = file['submissionId']
        stageId = file['stageId']
        date_object = datetime.strptime(
            file['dateUploaded'], '%Y-%m-%d %H:%M:%S')
        try:
            db_stage_id = 2
            if file['stageId'] == 3:
                db_stage_id = 4

            filename = str(file['submissionId']) + '-' + str(file['genreId']) + '-' + str(file['fileId']) + \
                '-' + str(file['revision']) + '-' + str(db_stage_id) + \
                '-' + date_object.strftime("%Y%m%d")

            m = re.search('(.*)\.(.*)', file['fileName'])

            if len(m.groups()) != 2:
                continue

            extension = m.group(2)

            filename = filename + '.' + extension

            app.logger.info('Filename on disk is ' + filename)
            app.logger.info(os.path.join('/var/www/files/journals/1/articles/' +
                                         str(file['submissionId']) + '/submission', filename))

            full_path = '/var/www/files/journals/1/articles/' + \
                str(file['submissionId']) + '/submission'

            if file['stageId'] == 3:
                full_path = full_path + '/review'

            results.append(convert_to(full_path, filename, timeout=30))
        except LibreOfficeError as e:
            raise InternalServerErrorError(
                {'message': 'Error when converting file to PDF', 'error': e.args})
        except TimeoutExpired:
            raise InternalServerErrorError(
                {'message': 'Timeout when converting file to PDF'})

    merger = PdfFileMerger()
    app.logger.info('start merging')
    for file in results:
        merger.append(fileobj=open(file, 'rb'))

    app.logger.info('finished merging')

    path = '/var/www/files/journals/1/articles/' + \
        str(submissionId) + '/submission'

    if stageId == 1:
        path = path + '/converted/merged.pdf'
    elif stageId == 3:
        path = path + '/review/converted/merged.pdf'

    output = open(path, 'wb')
    merger.write(output)

    return jsonify({'merge': 'success'})


@app.errorhandler(500)
def handle_500_error():
    return InternalServerErrorError().to_response()


@app.errorhandler(RestAPIError)
def handle_rest_api_error(error):
    return error.to_response()


app.wsgi_app = DispatcherMiddleware(index, {'/converter': app.wsgi_app})

if __name__ != '__main__':
    gunicorn_logger = logging.getLogger('gunicorn.error')
    app.logger.handlers = gunicorn_logger.handlers
    app.logger.setLevel(gunicorn_logger.level)

if __name__ == '__main__':
    app.run(host='0.0.0.0', threaded=True)
