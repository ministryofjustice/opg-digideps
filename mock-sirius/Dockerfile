FROM muonsoft/openapi-mock:0.3.9@sha256:c9afe1295484a9713de49258de94b994af3eb9d4e55ff05bdad1b1d6be12a88b

WORKDIR /app

ADD https://raw.githubusercontent.com/ministryofjustice/opg-data-deputy-reporting/master/lambda_functions/v2/openapi/deputy-reporting-openapi.yml \
    /app/deputy-reporting-openapi.yml
USER root
RUN chmod 644 /app/deputy-reporting-openapi.yml
USER openapi
