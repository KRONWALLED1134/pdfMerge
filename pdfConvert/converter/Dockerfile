FROM ubuntu:latest

RUN apt-get update
RUN apt-get install -y python3 python3-pip build-essential libssl-dev libffi-dev python-dev
RUN pip3 install gunicorn flask requests pypdf2

ENV LC_ALL=C.UTF-8
ENV LANG=C.UTF-8

CMD cd /converter/scripts && gunicorn --bind 0.0.0.0:5000 --reload --access-logfile '-' --error-logfile '-' --log-level info --timeout 120 wsgi:app
