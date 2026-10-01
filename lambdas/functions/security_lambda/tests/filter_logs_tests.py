import unittest

from app.security import LogRecord, parse_log_records


class TestParseLogRecords(unittest.TestCase):

    def setUp(self):
        self.results = [
            [
                {
                    "field": "real_forwarded_for",
                    "value": "192.168.1.1",
                },
                {
                    "field": "request_uri",
                    "value": "/hackurl1",
                },
                {
                    "field": "status",
                    "value": "404",
                },
            ],
            [
                {
                    "field": "real_forwarded_for",
                    "value": "192.168.1.2",
                },
                {
                    "field": "request_uri",
                    "value": "/report",
                },
                {
                    "field": "status",
                    "value": "200",
                },
            ],
        ]

    def test_parse_log_records(self):
        self.assertEqual(
            parse_log_records(self.results),
            [
                LogRecord(
                    real_forwarded_for="192.168.1.1/32",
                    request_uri="/hackurl1",
                    status=404,
                ),
                LogRecord(
                    real_forwarded_for="192.168.1.2/32",
                    request_uri="/report",
                    status=200,
                ),
            ],
        )


if __name__ == "__main__":
    unittest.main()
