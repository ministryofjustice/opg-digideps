import unittest

from app.security import IpActivitySummary, get_ips_to_alert_on


class TestGetIpsToAlertOn(unittest.TestCase):

    def setUp(self):
        self.summaries = {
            # Suspicious and authenticated -> should be returned
            "192.168.1.1": IpActivitySummary(
                not_found_without_suffix=10,
                authenticated_requests=2,
            ),
            # Suspicious but not authenticated -> should not be returned
            "192.168.1.2": IpActivitySummary(
                not_found_without_suffix=10,
            ),
            # Authenticated but not suspicious -> should not be returned
            "192.168.1.3": IpActivitySummary(
                successful_requests=5,
                authenticated_requests=2,
            ),
            # Suspicious via suffix rule and authenticated -> should be returned
            "192.168.1.4": IpActivitySummary(
                not_found_with_suffix=1,
                authenticated_requests=1,
            ),
        }

    def test_get_ips_to_alert_on(self):
        self.assertEqual(
            get_ips_to_alert_on(self.summaries),
            [
                "192.168.1.1",
                "192.168.1.4",
            ],
        )


if __name__ == "__main__":
    unittest.main()
