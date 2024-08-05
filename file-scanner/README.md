# File scanner

## Quick Start

From root or the repo run just the clamav container:
```bash
docker compose up -d file-scanner-rest
```

From this folder, test that service detects common test virus signature:

```bash
$ curl -i -F "file=@eicar.com.txt" http://localhost:8085/scan
HTTP/1.1 100 Continue

HTTP/1.1 406 Not Acceptable
Content-Type: application/json; charset=utf-8
Date: Mon, 28 Aug 2017 20:22:34 GMT
Content-Length: 56

File Accepted: false
```

Test that service returns 200 for clean file:

```bash
$ curl -i -F "file=@main.go" http://localhost:8085/scan

HTTP/1.1 100 Continue

HTTP/1.1 200 OK
Content-Type: application/json; charset=utf-8
Date: Mon, 28 Aug 2017 20:23:16 GMT
Content-Length: 33

File Accepted: true
```

### Status Codes
- 200 - clean file = no KNOWN infections
- 400 - ClamAV returned general error for file
- 406 - INFECTED
- 412 - unable to parse file
- 501 - unknown request

## Configuration

### Environment Variables

Below is the complete list of available options that can be used to customize your installation.

| Parameter | Description |
|-----------|-------------|
| `MAX_SCAN_SIZE` | Amount of data scanned for each file - Default `100M` |
| `MAX_FILE_SIZE` | Don't scan files larger than this size - Default `25M` |
| `MAX_RECURSION` | How many nested archives to scan - Default `16` |
| `MAX_FILES` | Number of files to scan withn archive - Default `10000` |
| `MAX_EMBEDDEDPE` | Maximum file size for embedded PE - Default `10M` |
| `MAX_HTMLNORMALIZE` | Maximum size of HTML to normalize - Default `10M` |
| `MAX_HTMLNOTAGS` | Maximum size of Normlized HTML File to scan- Default `2M` |
| `MAX_SCRIPTNORMALIZE` | Maximum size of a Script to normalize - Default `5M` |
| `MAX_ZIPTYPERCG` | Maximum size of ZIP to reanalyze type recognition - Default `1M` |
| `MAX_PARTITIONS` | How many partitions per Raw disk to scan - Default `50` |
| `MAX_ICONSPE` | How many Icons in PE to scan - Default `100` |
| `PCRE_MATCHLIMIT` | Maximum PCRE Match Calls - Default `100000` |
| `PCRE_RECMATCHLIMIT` | Maximum Recursive Match Calls to PCRE - Default `2000` |
| `SIGNATURE_CHECKS` | Check times per day for a new database signature. Must be between 1 and 50. - Default `2` |

### Networking

| Port | Description |
|-----------|-------------|
| `3310`    | ClamD Listening Port |

## Monitoring

```bash
curl -i http://localhost:8085/health-check
```

```bash
HTTP/1.1 200 OK
Date: Mon, 31 Oct 2022 16:19:01 GMT
Content-Length: 0
```
