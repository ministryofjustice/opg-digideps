#!/usr/bin/env python
import base64
import json
import os
import tempfile
import logging
import sys
import boto3
import traceback

from weasyprint import HTML, CSS
from werkzeug.wsgi import wrap_file
from werkzeug.wrappers import Request, Response

logger = logging.getLogger("weasyprint")
handler = logging.StreamHandler(sys.stdout)
logger.addHandler(handler)
logger.setLevel(logging.INFO)


debug_css = CSS(
    string="""
  /* Ensure deterministic page size and margins */
  @page { size: A4; margin: 12mm; }

  /* Reset media queries: apply same rules for print */
  @media print {
    * {
      break-before: auto !important;
      break-after: auto !important;
      break-inside: auto !important;
      page-break-before: auto !important;
      page-break-after: auto !important;
      page-break-inside: auto !important;
    }
  }

  /* Kill floats and positioned layout that cause recursive layout work */
  *[style*="float"],
  .float, img[align],
  [class*="float-"],
  [style*="position: absolute"],
  [style*="position:absolute"],
  [style*="position: fixed"],
  [style*="position:fixed"] {
    float: none !important;
    position: static !important;
  }

  /* Make images safe */
  img, svg {
    max-width: 100% !important;
    height: auto !important;
    page-break-inside: avoid !important;
  }

  /* Tables – allow breaks inside the table, but avoid breaking inside a row */
  table { width: 100% !important; table-layout: fixed !important; page-break-inside: auto !important; }
  tr, td, th { page-break-inside: avoid !important; }

  /* Avoid tiny content area from huge margins/paddings */
  html, body { margin: 0; padding: 0; }
  * { box-sizing: border-box; }

  /* Remove min-heights/overflow that can force pathological breaks */
  [style*="min-height"], [style*="min-height:"] { min-height: 0 !important; }
  [style*="overflow"], [style*="overflow:"] { overflow: visible !important; }
"""
)


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
            raw_bytes = base64.b64decode(payload["contents"])
        elif request.files:
            raw_bytes = request.files["file"].read()
        else:
            return Response("No HTML provided", status=400)

        html_string = safe_decode(raw_bytes)

        file_name = source_file.name
        file_parts = file_name.split(".html")
        safe_file_name = f"{file_parts[0]}_tmp.html"
        logger.warning(f"Raw bytes length: {len(raw_bytes)}")
        with open(safe_file_name, "w", encoding="utf-8") as f:
            f.write(html_string)

        # !/usr/bin/env python
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

        debug_css = CSS(
            string="""
          /* Ensure deterministic page size and margins */
          @page { size: A4; margin: 12mm; }

          /* Reset media queries: apply same rules for print */
          @media print {
            * {
              break-before: auto !important;
              break-after: auto !important;
              break-inside: auto !important;
              page-break-before: auto !important;
              page-break-after: auto !important;
              page-break-inside: auto !important;
            }
          }

          /* Kill floats and positioned layout that cause recursive layout work */
          *[style*="float"],
          .float, img[align],
          [class*="float-"],
          [style*="position: absolute"],
          [style*="position:absolute"],
          [style*="position: fixed"],
          [style*="position:fixed"] {
            float: none !important;
            position: static !important;
          }

          /* Make images safe */
          img, svg {
            max-width: 100% !important;
            height: auto !important;
            page-break-inside: avoid !important;
          }

          /* Tables – allow breaks inside the table, but avoid breaking inside a row */
          table { width: 100% !important; table-layout: fixed !important; page-break-inside: auto !important; }
          tr, td, th { page-break-inside: avoid !important; }

          /* Avoid tiny content area from huge margins/paddings */
          html, body { margin: 0; padding: 0; }
          * { box-sizing: border-box; }

          /* Remove min-heights/overflow that can force pathological breaks */
          [style*="min-height"], [style*="min-height:"] { min-height: 0 !important; }
          [style*="overflow"], [style*="overflow:"] { overflow: visible !important; }
        """
        )

        def safe_decode(b):
            """Decode bytes to UTF‑8 safely, replacing invalid bytes."""
            try:
                return b.decode("utf-8")
            except UnicodeDecodeError:
                logger.error("Invalid UTF‑8 detected. Using replacement decoding.")
                return b.decode("utf-8", errors="replace")

        @Request.application
        def application(request, boto3=None):
            """
            To use this application, the user must send a POST request with
            base64 or form encoded encoded HTML content and the htmltopdf Options in
            request data, with keys 'base64_html' and 'options'.
            The application will return a response with the PDF file.
            """
            if request.method != "POST":
                return Response("Only POST allowed.", status=405)

            request_is_json = request.content_type and request.content_type.endswith(
                "json"
            )

            with tempfile.NamedTemporaryFile(suffix=".html") as source_file:

                if request_is_json:
                    payload = json.loads(request.data)
                    raw_bytes = base64.b64decode(payload["contents"])
                elif request.files:
                    raw_bytes = request.files["file"].read()
                else:
                    return Response("No HTML provided", status=400)

                html_string = safe_decode(raw_bytes)

                file_name = source_file.name
                file_parts = file_name.split(".html")
                safe_file_name = f"{file_parts[0]}_tmp.html"
                logger.warning(f"Raw bytes length: {len(raw_bytes)}")
                with open(safe_file_name, "w", encoding="utf-8") as f:
                    f.write(html_string)

                # Upload the saved HTML to S3 (bucket name from env var BUCKET)
                bucket_name = os.environ.get("BUCKET")
                if not bucket_name:
                    logger.error(
                        "BUCKET environment variable is not set; skipping S3 upload."
                    )
                else:
                    s3_key = os.path.basename(safe_file_name)
                    s3 = boto3.client("s3")
                    try:
                        s3.upload_file(
                            Filename=safe_file_name,
                            Bucket=bucket_name,
                            Key=s3_key,
                            ExtraArgs={
                                "ContentType": "text/html; charset=utf-8",
                                "ServerSideEncryption": "AES256",
                            },
                        )
                        logger.info("Uploaded HTML to s3://%s/%s", bucket_name, s3_key)
                    except Exception as e:
                        logger.error("S3 upload failed: %s", e)

                base_url = "/tmp"  # adjust to your runtime folder
                css_text = "@page {size: Letter;margin: 0.2in 0.44in 0.2in 0.44in;}"

                pdf_file_name = f"{file_name}.pdf"
                try:
                    # Split out additional CSS into a file if we need more in the future...
                    # HTML(string=html_string, base_url=base_url, media_type="print", encoding="utf-8").write_pdf(
                    #     pdf_file_name,
                    #     stylesheets=[
                    #         CSS(
                    #             string="@page {size: Letter;margin: 0.2in 0.44in 0.2in 0.44in;}"
                    #         )
                    #     ],
                    # )

                    doc = HTML(
                        string=html_string,
                        base_url=base_url,
                        media_type="print",  # force print rules while debugging
                    ).render(
                        stylesheets=[
                            CSS(string="@page { size: A4; margin: 12mm; }"),
                            debug_css,
                        ]
                    )

                    page_count = len(doc.pages)
                    logger.info("Rendered pages (debug): %d", page_count)

                    if page_count > 150:
                        return Response(
                            "Document too complex (excessive pagination). Try simplifying layout.",
                            status=422,
                        )

                    doc.write_pdf(pdf_file_name)

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

            run_simple(
                "127.0.0.1", 5000, application, use_debugger=True, use_reloader=True
            )

        base_url = "/tmp"  # adjust to your runtime folder
        css_text = "@page {size: Letter;margin: 0.2in 0.44in 0.2in 0.44in;}"

        pdf_file_name = f"{file_name}.pdf"
        try:
            # Split out additional CSS into a file if we need more in the future...
            # HTML(string=html_string, base_url=base_url, media_type="print", encoding="utf-8").write_pdf(
            #     pdf_file_name,
            #     stylesheets=[
            #         CSS(
            #             string="@page {size: Letter;margin: 0.2in 0.44in 0.2in 0.44in;}"
            #         )
            #     ],
            # )

            doc = HTML(
                string=html_string,
                base_url=base_url,
                media_type="print",  # force print rules while debugging
            ).render(
                stylesheets=[CSS(string="@page { size: A4; margin: 12mm; }"), debug_css]
            )

            page_count = len(doc.pages)
            logger.info("Rendered pages (debug): %d", page_count)

            if page_count > 150:
                return Response(
                    "Document too complex (excessive pagination). Try simplifying layout.",
                    status=422,
                )

            doc.write_pdf(pdf_file_name)

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
