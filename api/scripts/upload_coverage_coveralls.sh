#!/bin/bash
# exit on error
set -e

wget https://github.com/php-coveralls/php-coveralls/releases/download/v2.2.0/php-coveralls.phar
chmod +x php-coveralls.phar

./php-coveralls.phar -v --json_path ./coveralls-upload.json --coverage_clover tests/coverage/merged.xml --root_dir .
