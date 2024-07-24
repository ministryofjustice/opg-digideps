import unittest

from app.block_ips import filter_logs


class TestFilterLogs(unittest.TestCase):

    def test_filter_logs(self):
        logs = [
            {
                "real_forwarded_for": "192.168.1.1",
                "status": "404",
                "request_uri": "/hackurl1",
            },
            {
                "real_forwarded_for": "192.168.1.1",
                "status": "404",
                "request_uri": "/hackurl2",
            },
            {
                "real_forwarded_for": "192.168.1.1",
                "status": "404",
                "request_uri": "/hackurl3",
            },
            {
                "real_forwarded_for": "192.168.1.1",
                "status": "404",
                "request_uri": "/hackurl4",
            },
            {
                "real_forwarded_for": "192.168.1.1",
                "status": "404",
                "request_uri": "/hackurl5",
            },
            {
                "real_forwarded_for": "192.168.1.1",
                "status": "404",
                "request_uri": "/hackurl6",
            },
            {
                "real_forwarded_for": "192.168.1.2",
                "status": "404",
                "request_uri": "/hack.html",
            },
            {
                "real_forwarded_for": "192.168.1.2",
                "status": "404",
                "request_uri": "/hack.zip",
            },
            {
                "real_forwarded_for": "192.168.1.3",
                "status": "200",
                "request_uri": "/report",
            },
            {
                "real_forwarded_for": "192.168.1.3",
                "status": "404",
                "request_uri": "/hackscript.js",
            },
            {
                "real_forwarded_for": "192.168.1.3",
                "status": "200",
                "request_uri": "/report",
            },
            {
                "real_forwarded_for": "192.168.1.4",
                "status": "404",
                "request_uri": "/hackurl1",
            },
            {
                "real_forwarded_for": "192.168.1.4",
                "status": "404",
                "request_uri": "/hackurl2",
            },
            {
                "real_forwarded_for": "192.168.1.4",
                "status": "404",
                "request_uri": "/hackurl3",
            },
            {
                "real_forwarded_for": "192.168.1.4",
                "status": "404",
                "request_uri": "/hackurl4",
            },
            {
                "real_forwarded_for": "192.168.1.4",
                "status": "404",
                "request_uri": "/hackurl5",
            },
            {
                "real_forwarded_for": "192.168.1.4",
                "status": "404",
                "request_uri": "/hackurl6",
            },
            {
                "real_forwarded_for": "192.168.1.4",
                "status": "200",
                "request_uri": "/report",
            },
        ]

        expected_ips = [
            "192.168.1.2",  # Matches "404_with_suffix" > 1 and "2xx_or_3xx_not_root" < 1
            "192.168.1.1",  # Matches "404_without_suffix" > 5 and "2xx_or_3xx_not_root" < 1
        ]

        result_ips = filter_logs(logs)

        self.assertEqual(result_ips, expected_ips)


if __name__ == "__main__":
    unittest.main()
