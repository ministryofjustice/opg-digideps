#! /usr/bin/env sh

set -e
set -o pipefail

echo "Waiting for 1 minute to allow environment to spin up properly..."
sleep 60
echo "===== Running smoke test against Admin ====="
node tests/smoke-tests/AdminSmokeTest.js
echo ""
echo ""
echo "===== Running smoke test against Frontend ====="
node tests/smoke-tests/FrontSmokeTest.js
echo ""
echo ""
echo "===== Smoke tests completed without any issues ====="
