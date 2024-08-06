#! /usr/bin/env python
"""
    WSGI APP to convert htmltopdf As a webservice

    :copyright: (c) 2013 by Openlabs Technologies & Consulting (P) Limited
    :license: BSD, see LICENSE for more details.
"""
import base64
import json
import tempfile
import logging
import sys

logger = logging.getLogger("weasyprint")
from weasyprint import HTML, CSS

from werkzeug.wsgi import wrap_file
from werkzeug.wrappers import Request, Response

handler = logging.StreamHandler(sys.stdout)

logger.addHandler(handler)
logger.setLevel(40)


@Request.application
def application(request):
    """
    To use this application, the user must send a POST request with
    base64 or form encoded encoded HTML content and the htmltopdf Options in
    request data, with keys 'base64_html' and 'options'.
    The application will return a response with the PDF file.
    """
    if request.method != "POST":
        return

    request_is_json = request.content_type.endswith("json")

    with tempfile.NamedTemporaryFile(suffix=".html") as source_file:

        if request_is_json:
            payload = json.loads(request.data)
            source_file.write(base64.b64decode(payload["contents"]))
        elif request.files:
            source_file.write(request.files["file"].read())

        file_name = source_file.name

        try:
            # Split out additional CSS into a file if we need more in the future...
            HTML(file_name, media_type="screen", encoding="utf-8").write_pdf(
                file_name + ".pdf",
                stylesheets=[
                    CSS(
                        string="@page {size: Letter;margin: 0.2in 0.44in 0.2in 0.44in;}"
                    )
                ],
            )
            logger.log(41, f"{file_name} has been processed successfully")
        except Exception as e:
            logger.log(41, f"{file_name} has failed to process")
            logger.log(41, e)

        return Response(
            wrap_file(request.environ, open(file_name + ".pdf", "rb")),
            mimetype="application/pdf",
            direct_passthrough=True,
        )


if __name__ == "__main__":
    from werkzeug.serving import run_simple

    run_simple("127.0.0.1", 5000, application, use_debugger=True, use_reloader=True)
