"""
Move api unit tests which are really integration tests into a new directory.
This leaves just the true unit tests in the src directory.

To use, put this script and the test_analysis.txt file into the same directory.
Then do:

python remove_slow_tests.py <path to opg-digideps clone>/api/app/tests/unit <destination directory>

The script looks for any lines in test_analysis.txt starting with "* ✗", which marks that
test as a bogus unit test.

After doing this, any non-unit tests are in <destination directory>, leaving just
the real unit tests in api/app/tests/unit.
"""

import re
import shutil
import sys

from pathlib import Path

src_dir = Path(sys.argv[1])
dest_dir = Path(sys.argv[2])

with open("test_analysis.txt") as f:
    files = f.readlines()

files = [line for line in files if line.startswith("* ✗")]

bad_filenames = []
matcher = re.compile("^.* (\\./.+\\.php).*$")

for f in files:
    filename = matcher.match(f)[1]
    src_path = src_dir / filename
    dest_path = dest_dir / filename

    if not dest_path.parent.exists():
        dest_path.parent.mkdir(parents=True)

    src_path.rename(dest_path)
