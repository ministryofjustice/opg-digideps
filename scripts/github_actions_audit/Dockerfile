FROM python:3.14.7-alpine3.24@sha256:c6ead215bfd31f1e433d968853b7a769989117115b728874824e6c0a27cb96fc AS python

# Copy source files
COPY requirements.txt /requirements.txt
COPY check_actions.sh /check_actions.sh

# Install all needed packages
RUN pip install -r /requirements.txt
RUN apk add actionlint
RUN chmod +x /check_actions.sh

CMD ["/check_actions.sh"]
