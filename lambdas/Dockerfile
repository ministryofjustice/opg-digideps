FROM python:3.11.2-slim-buster
COPY requirements/requirements-dev.txt /requirements.txt
RUN pip install -r /requirements.txt
RUN mkdir workdir && cd workdir && mkdir lambdas
WORKDIR /workdir/lambdas
COPY functions /workdir/lambdas/functions
COPY tests /workdir/lambdas/tests
WORKDIR /workdir
CMD ["tail", "-f", "/dev/null"]
