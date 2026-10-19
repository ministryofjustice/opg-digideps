#!/bin/sh
echo """
===== RUNNING ACTION LINT =====

"""

actionlint -ignore 'at "uses" is not following the format "owner/' /github/workflows/*.yml

echo """

===== RUNNING ZIZMOR =====

"""

zizmor --collect=all /github/workflows/*.yml

echo "===== FINISHED ANALYSING GITHUB ACTIONS ====="
