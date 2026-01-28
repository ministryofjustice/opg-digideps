#!/usr/bin/env python
import base64
import json
import tempfile
import logging
import sys
import traceback

from weasyprint import HTML, CSS
from werkzeug.wsgi import wrap_file
from werkzeug.wrappers import Request, Response

logger = logging.getLogger("weasyprint")
handler = logging.StreamHandler(sys.stdout)
logger.addHandler(handler)
logger.setLevel(logging.INFO)


def safe_decode(b):
    """Decode bytes to UTF‑8 safely, replacing invalid bytes."""
    try:
        return b.decode("utf-8")
    except UnicodeDecodeError:
        logger.error("Invalid UTF‑8 detected. Using replacement decoding.")
        return b.decode("utf-8", errors="replace")


@Request.application
def application(request):
    """
    To use this application, the user must send a POST request with
    base64 or form encoded encoded HTML content and the htmltopdf Options in
    request data, with keys 'base64_html' and 'options'.
    The application will return a response with the PDF file.
    """
    if request.method != "POST":
        return Response("Only POST allowed.", status=405)

    request_is_json = request.content_type and request.content_type.endswith("json")

    with tempfile.NamedTemporaryFile(suffix=".html") as source_file:

        if request_is_json:
            payload = json.loads(request.data)
            # source_file.write(base64.b64decode(payload["contents"]))
            raw_bytes = base64.b64decode(payload["contents"])
        elif request.files:
            # source_file.write(request.files["file"].read())
            raw_bytes = request.files["file"].read()
        else:
            return Response("No HTML provided", status=400)

        html_string = safe_decode(raw_bytes)
        file_name = source_file.name
        file_parts = file_name.split(".html")
        safe_file_name = f"{file_parts[0]}_tmp.html"

        with open(safe_file_name, "w", encoding="utf-8") as f:
            f.write(html_string)

        # with open(file_name) as f:  # The with keyword automatically closes the file when you are done
        #     print(f.read())
        #
        with open(
            safe_file_name
        ) as f:  # The with keyword automatically closes the file when you are done
            print(f.read())

        pdf_file_name = f"{file_name}.pdf"
        try:
            # Split out additional CSS into a file if we need more in the future...
            HTML(safe_file_name, media_type="screen", encoding="utf-8").write_pdf(
                pdf_file_name,
                stylesheets=[
                    CSS(
                        string="@page {size: Letter;margin: 0.2in 0.44in 0.2in 0.44in;}"
                    )
                ],
            )
            logger.info("%s rendered successfully", file_name)
        except Exception as e:
            logger.error("PDF generation failed:\n%s", traceback.format_exc())
            return Response("PDF generation error", status=500)

        return Response(
            wrap_file(request.environ, open(pdf_file_name, "rb")),
            mimetype="application/pdf",
            direct_passthrough=True,
        )


if __name__ == "__main__":
    from werkzeug.serving import run_simple

    run_simple("127.0.0.1", 5000, application, use_debugger=True, use_reloader=True)
