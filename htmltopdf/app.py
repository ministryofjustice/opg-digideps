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
    if request.method != "POST":
        return Response("Only POST allowed.", status=405)

    request_is_json = request.content_type and request.content_type.endswith("json")

    # Create a stable temp dir for html + pdf
    with tempfile.TemporaryDirectory() as tmpdir:
        html_path = f"{tmpdir}/input.html"
        pdf_path = f"{tmpdir}/output.pdf"

        # ---------------------------
        # 1. Extract HTML bytes safely
        # ---------------------------
        raw_bytes = None

        try:
            if request_is_json:
                payload = json.loads(request.data)
                raw_bytes = base64.b64decode(payload["contents"])
            elif request.files:
                raw_bytes = request.files["file"].read()
            else:
                return Response("No HTML provided", status=400)
        except Exception as e:
            logger.error("Failed to read input HTML: %s", e)
            return Response("Invalid input HTML", status=400)

        # ---------------------------------
        # 2. Decode HTML safely into UTF‑8
        # ---------------------------------
        html_string = safe_decode(raw_bytes)

        # Write the HTML to disk
        with open(html_path, "w", encoding="utf-8") as f:
            f.write(html_string)

        # ---------------------------------
        # 3. Render PDF (wrapped in try/except)
        # ---------------------------------
        try:
            HTML(html_path, media_type="screen", encoding="utf-8").write_pdf(
                pdf_path,
                stylesheets=[
                    CSS(
                        string="@page {size: Letter;margin: 0.2in 0.44in 0.2in 0.44in;}"
                    )
                ],
            )
            logger.info("%s rendered successfully", html_path)

        except Exception as e:
            logger.error("PDF generation failed:\n%s", traceback.format_exc())
            return Response("PDF generation error", status=500)

        # Return the PDF as a stream
        return Response(
            wrap_file(request.environ, open(pdf_path, "rb")),
            mimetype="application/pdf",
            direct_passthrough=True,
        )


if __name__ == "__main__":
    from werkzeug.serving import run_simple

    run_simple("127.0.0.1", 5000, application, use_debugger=True, use_reloader=True)
