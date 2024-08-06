import unittest
from unittest.mock import patch, Mock
from datetime import datetime, timedelta

from app.block_ips import get_blocked_ips


class TestGetBlockedIps(unittest.TestCase):
    @patch("boto3.client")
    def test_get_blocked_ips(self, mock_boto_client):
        # Setup the mock DynamoDB client
        mock_dynamodb = Mock()
        mock_boto_client.return_value = mock_dynamodb

        # Current time for testing
        current_time = datetime.utcnow()

        # Setup the mock response
        mock_response = {
            "Items": [
                {
                    "IP": {"S": "192.168.1.1"},
                    "TimeoutExpiry": {
                        "N": str(int((current_time + timedelta(minutes=5)).timestamp()))
                    },
                },
                {
                    "IP": {"S": "192.168.1.2"},
                    "TimeoutExpiry": {
                        "N": str(int((current_time - timedelta(minutes=5)).timestamp()))
                    },
                },
            ]
        }
        mock_dynamodb.scan.return_value = mock_response

        result = get_blocked_ips()

        # Expected result should only include the IP with a TimeoutExpiry in the future
        expected_result = ["192.168.1.1"]

        self.assertEqual(result, expected_result)
        mock_dynamodb.scan.assert_called_once_with(
            TableName="BlockedIPs", ProjectionExpression="IP, TimeoutExpiry"
        )


if __name__ == "__main__":
    unittest.main()
