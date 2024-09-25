def get_service_url(region: str, service: str) -> str:
    return f"https://console.aws.amazon.com/{service}/home?region={region}"
