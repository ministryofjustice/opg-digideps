#! /usr/bin/env python
"""
WSGI APP to convert HTML to PDF as a web service.

:copyright: (c) 2013 by Openlabs Technologies & Consulting (P) Limited
:license: BSD, see LICENSE for more details.
"""

import base64
import json
import logging
import multiprocessing
import sys
import tempfile
import resource

from weasyprint import HTML, CSS
from werkzeug.wrappers import Request, Response
from werkzeug.wsgi import wrap_file

logger = logging.getLogger("weasyprint")

handler = logging.StreamHandler(sys.stdout)
logger.addHandler(handler)
logger.setLevel(logging.INFO)

PDF_TIMEOUT_SECONDS = 30


def generate_pdf(html_file, pdf_file):
    """
    Run WeasyPrint in a separate process so that it can be safely
    terminated if rendering takes too long.
    """
    resource.setrlimit(
        resource.RLIMIT_AS,
        (256 * 1024 * 1024, 256 * 1024 * 1024),
    )

    html = HTML(
        html_file,
        base_url="/tmp",
        media_type="screen",
        encoding="utf-8",
    )

    html.write_pdf(
        pdf_file,
        stylesheets=[
            CSS(string="@page {size: Letter; margin: 0.2in 0.44in 0.2in 0.44in;}")
        ],
    )


@Request.application
def application(request):
    """
    Accepts:

    JSON:
        {
            "contents": "<base64 encoded html>"
        }

    Multipart form:
        file=<html file>

    Returns:
        PDF document
    """

    if request.method != "POST":
        return Response("Method not allowed", status=405)

    request_is_json = request.content_type and request.content_type.endswith("json")

    with tempfile.NamedTemporaryFile(suffix=".html") as source_file:
        if request_is_json:
            payload = json.loads(request.data)
            source_file.write(base64.b64decode(payload["contents"]))
        elif request.files:
            source_file.write(request.files["file"].read())
        else:
            return Response(
                "No HTML content provided",
                status=400,
            )

        source_file.flush()

        html_file = source_file.name
        pdf_file = f"{html_file}.pdf"

        process = multiprocessing.Process(
            target=generate_pdf,
            args=(html_file, pdf_file),
        )

        process.start()
        process.join(timeout=PDF_TIMEOUT_SECONDS)

        if process.is_alive():
            logger.error(
                "PDF generation timed out after %s seconds for %s",
                PDF_TIMEOUT_SECONDS,
                html_file,
            )

            process.terminate()
            process.join()

            return Response(
                "PDF generation timed out",
                status=408,
            )

        if process.exitcode != 0:
            logger.error(
                "PDF generation failed for %s (exit code %s)",
                html_file,
                process.exitcode,
            )

            return Response(
                "Unable to generate PDF",
                status=500,
            )

        try:
            logger.info("%s processed successfully", html_file)

            return Response(
                wrap_file(request.environ, open(pdf_file, "rb")),
                mimetype="application/pdf",
                direct_passthrough=True,
            )

        except FileNotFoundError:
            logger.error(
                "PDF file was not created for %s",
                html_file,
            )

            return Response(
                "Unable to generate PDF",
                status=500,
            )


if __name__ == "__main__":
    from werkzeug.serving import run_simple

    run_simple(
        "127.0.0.1",
        5000,
        application,
        use_debugger=True,
        use_reloader=True,
    )
