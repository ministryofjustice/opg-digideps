FROM muonsoft/openapi-mock:0.3.9

WORKDIR /app

ADD https://raw.githubusercontent.com/ministryofjustice/opg-data-deputy-reporting/master/lambda_functions/v2/openapi/deputy-reporting-openapi.yml \
    /app/deputy-reporting-openapi.yml
USER root
RUN chmod 644 /app/deputy-reporting-openapi.yml
USER openapi
