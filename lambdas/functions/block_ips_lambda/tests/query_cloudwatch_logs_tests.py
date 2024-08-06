import unittest
from unittest.mock import patch, MagicMock

from app.block_ips import query_cloudwatch_logs


class TestQueryCloudwatchLogs(unittest.TestCase):

    @patch("boto3.client")
    def test_query_cloudwatch_logs(self, mock_boto_client):
        # Mock CloudWatch Logs client
        mock_cloudwatch_logs = MagicMock()
        mock_boto_client.return_value = mock_cloudwatch_logs

        # Mock response for start_query
        mock_cloudwatch_logs.start_query.return_value = {"queryId": "test-query-id"}

        # Mock response for get_query_results
        mock_cloudwatch_logs.get_query_results.return_value = {
            "status": "Complete",
            "results": [
                [
                    {"field": "real_forwarded_for", "value": "192.0.2.1"},
                    {"field": "request_uri", "value": "/test"},
                    {"field": "status", "value": "200"},
                ]
            ],
        }

        log_group_name = "test-log-group"
        log_stream_prefix = "test-log-stream"

        expected_output = [
            {
                "real_forwarded_for": "192.0.2.1/32",
                "request_uri": "/test",
                "status": "200",
            }
        ]

        # Use patch to skip time.sleep calls
        with patch("time.sleep", return_value=None):
            result = query_cloudwatch_logs(log_group_name, log_stream_prefix)
            self.assertEqual(result, expected_output)

        # Check that start_query was called with correct parameters
        mock_cloudwatch_logs.start_query.assert_called_once()
        args, kwargs = mock_cloudwatch_logs.start_query.call_args
        self.assertEqual(kwargs["logGroupName"], log_group_name)
        self.assertIn("startTime", kwargs)
        self.assertIn("endTime", kwargs)
        self.assertIn("queryString", kwargs)
        self.assertIn(log_stream_prefix, kwargs["queryString"])

        # Check that get_query_results was called
        mock_cloudwatch_logs.get_query_results.assert_called()


if __name__ == "__main__":
    unittest.main()
