import unittest
from unittest.mock import MagicMock, patch
from datetime import datetime, timedelta

from app.block_ips import update_dynamodb_table


class TestUpdateDynamoDBTable(unittest.TestCase):

    @patch("boto3.client")
    def test_update_dynamodb_table(self, mock_dynamodb_client):
        # Mock boto3 DynamoDB client
        dynamodb_mock = MagicMock()
        mock_dynamodb_client.return_value = dynamodb_mock

        # Mock parameters
        table_name = "mock-table"
        ips = ["192.168.1.1", "192.168.1.3", "192.168.1.2"]

        # Mock responses for get_item
        get_item_responses = [
            # Response for existing item
            {
                "Item": {
                    "IP": {"S": "192.168.1.1"},
                    "TimeoutExpiry": {
                        "N": str(
                            int((datetime.utcnow() + timedelta(minutes=20)).timestamp())
                        )
                    },
                    "UpdatedAt": {
                        "N": str(
                            int((datetime.utcnow() - timedelta(minutes=20)).timestamp())
                        )
                    },
                    "ExpiresTTL": {
                        "N": str(
                            int((datetime.utcnow() - timedelta(hours=12)).timestamp())
                        )
                    },
                    "BlockCounter": {"N": "1"},
                }
            },
            # Response for existing item that was modified less than 10 mins ago
            {
                "Item": {
                    "IP": {"S": "192.168.1.3"},
                    "TimeoutExpiry": {
                        "N": str(
                            int((datetime.utcnow() + timedelta(minutes=20)).timestamp())
                        )
                    },
                    "UpdatedAt": {
                        "N": str(
                            int((datetime.utcnow() - timedelta(minutes=1)).timestamp())
                        )
                    },
                    "ExpiresTTL": {
                        "N": str(
                            int((datetime.utcnow() - timedelta(hours=12)).timestamp())
                        )
                    },
                    "BlockCounter": {"N": "1"},
                }
            },
            # Response for non-existing item
            {},
        ]

        # Attach responses to mocked client
        dynamodb_mock.get_item.side_effect = lambda **kwargs: get_item_responses.pop(0)

        # Mock datetime and timestamp
        mock_current_time = datetime.utcnow()
        mock_datetime = MagicMock()
        mock_datetime.utcnow.return_value = mock_current_time

        # Patch datetime to mock current time
        with patch("datetime.datetime", mock_datetime):
            # Call function under test
            update_dynamodb_table(ips)

        # Assertions
        expected_update_calls = [
            # First call is a update_item for existing item
            {
                "TableName": table_name,
                "Key": {"IP": {"S": "192.168.1.1"}},
                "UpdateExpression": "SET BlockCounter = BlockCounter + :inc, TimeoutExpiry = :timeout, ExpiresTTL = :ttl, UpdatedAt = :now",
                "ExpressionAttributeValues": {
                    ":inc": {"N": "1"},
                    ":timeout": {
                        "N": str(
                            int((mock_current_time + timedelta(hours=4)).timestamp())
                        )
                    },
                    ":ttl": {
                        "N": str(
                            int((mock_current_time + timedelta(hours=12)).timestamp())
                        )
                    },
                    ":now": {"N": str(int(mock_current_time.timestamp()))},
                },
            },
            # Second call is a put_item for non-existing item
            {
                "TableName": table_name,
                "Item": {
                    "IP": {"S": "192.168.1.2"},
                    "TimeoutExpiry": {
                        "N": str(
                            int((mock_current_time + timedelta(minutes=30)).timestamp())
                        )
                    },
                    "BlockCounter": {"N": "1"},
                    "ExpiresTTL": {
                        "N": str(
                            int((mock_current_time + timedelta(hours=12)).timestamp())
                        )
                    },
                    "UpdatedAt": {"N": str(int(mock_current_time.timestamp()))},
                },
            },
        ]

        actual_ip_address_1 = dynamodb_mock.update_item.call_args_list[0].kwargs["Key"][
            "IP"
        ]["S"]
        actual_timeout_expiry_1 = dynamodb_mock.update_item.call_args_list[0].kwargs[
            "ExpressionAttributeValues"
        ][":timeout"]["N"]

        actual_ip_address_2 = dynamodb_mock.put_item.call_args_list[0].kwargs["Item"][
            "IP"
        ]["S"]
        actual_timeout_expiry_2 = dynamodb_mock.put_item.call_args_list[0].kwargs[
            "Item"
        ]["TimeoutExpiry"]["N"]

        expected_ip_address_1 = expected_update_calls[0]["Key"]["IP"]["S"]
        expected_timeout_expiry_1 = expected_update_calls[0][
            "ExpressionAttributeValues"
        ][":timeout"]["N"]

        expected_ip_address_2 = expected_update_calls[1]["Item"]["IP"]["S"]
        expected_timeout_expiry_2 = expected_update_calls[1]["Item"]["TimeoutExpiry"][
            "N"
        ]

        # This proves that "192.168.1.3" does not create a update_item call
        self.assertEqual(len(dynamodb_mock.update_item.call_args_list), 1)
        # Check that the expected calls were made to the mock client
        self.assertEqual(actual_ip_address_1, expected_ip_address_1)
        self.assertEqual(actual_timeout_expiry_1, expected_timeout_expiry_1)

        self.assertEqual(actual_ip_address_2, expected_ip_address_2)
        self.assertEqual(actual_timeout_expiry_2, expected_timeout_expiry_2)


if __name__ == "__main__":
    unittest.main()
