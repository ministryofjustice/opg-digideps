FROM python:3.11.1-slim-buster
COPY requirements/requirements-dev.txt /requirements.txt
RUN pip install -r /requirements.txt
RUN mkdir workdir && cd workdir && mkdir lambda_functions
WORKDIR /workdir/lambda_functions
COPY functions /workdir/lambda_functions/functions
COPY tests /workdir/lambda_functions/tests
WORKDIR /workdir
CMD ["tail", "-f", "/dev/null"]
