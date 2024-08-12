import unittest

from app.block_ips import filter_logs


class TestFilterLogs(unittest.TestCase):

    def test_filter_logs(self):
        logs = [
            {
                "real_forwarded_for": "192.168.1.1",
                "status": "404",
                "request_uri": "/file.zip",
            },
            {
                "real_forwarded_for": "192.168.1.1",
                "status": "404",
                "request_uri": "/file.zip",
            },
            {
                "real_forwarded_for": "192.168.1.1",
                "status": "404",
                "request_uri": "/file.zip",
            },
            {
                "real_forwarded_for": "192.168.1.1",
                "status": "404",
                "request_uri": "/file.zip",
            },
            {
                "real_forwarded_for": "192.168.1.1",
                "status": "404",
                "request_uri": "/file.zip",
            },
            {
                "real_forwarded_for": "192.168.1.1",
                "status": "404",
                "request_uri": "/file.zip",
            },
            {
                "real_forwarded_for": "192.168.1.1",
                "status": "404",
                "request_uri": "/file.zip",
            },
            {
                "real_forwarded_for": "192.168.1.1",
                "status": "404",
                "request_uri": "/file.zip",
            },
            {
                "real_forwarded_for": "192.168.1.1",
                "status": "404",
                "request_uri": "/file.zip",
            },
            {
                "real_forwarded_for": "192.168.1.1",
                "status": "404",
                "request_uri": "/file.zip",
            },
            {
                "real_forwarded_for": "192.168.1.1",
                "status": "404",
                "request_uri": "/file.zip",
            },
            {
                "real_forwarded_for": "192.168.1.1",
                "status": "200",
                "request_uri": "/admin",
            },
            {
                "real_forwarded_for": "192.168.1.1",
                "status": "404",
                "request_uri": "/file.html",
            },
            {
                "real_forwarded_for": "192.168.1.2",
                "status": "404",
                "request_uri": "/page.html",
            },
            {
                "real_forwarded_for": "192.168.1.2",
                "status": "404",
                "request_uri": "/index.php",
            },
            {
                "real_forwarded_for": "192.168.1.3",
                "status": "200",
                "request_uri": "/test",
            },
            {
                "real_forwarded_for": "192.168.1.3",
                "status": "404",
                "request_uri": "/script.js",
            },
        ]

        expected_ips = [
            "192.168.1.1",  # Matches "404_with_suffix" > 10 and "2xx_or_3xx_not_root" < 5
            "192.168.1.2",  # Matches "404_without_suffix" > 5 and "2xx_or_3xx_not_root" < 1
        ]

        result_ips = filter_logs(logs)

        self.assertEqual(result_ips, expected_ips)


if __name__ == "__main__":
    unittest.main()
