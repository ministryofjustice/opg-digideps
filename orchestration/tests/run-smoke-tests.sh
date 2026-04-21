#! /usr/bin/env sh

set -e
set -o pipefail

echo "===== Running smoke tests ====="
python3 tests/smoke-tests-python/smoke_tests.py
echo ""
echo ""
echo "===== Smoke tests completed without any issues ====="
