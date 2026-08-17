FROM python:3.13.15-alpine3.24@sha256:540c7d91f98ff6880174c40e99067bf5941eb54d818a7a5e094d188b196a934d AS python

# Copy source files
COPY requirements.txt /requirements.txt
COPY check_actions.sh /check_actions.sh

# Install all needed packages
RUN pip install -r /requirements.txt
RUN apk add actionlint
RUN chmod +x /check_actions.sh

CMD ["/check_actions.sh"]
