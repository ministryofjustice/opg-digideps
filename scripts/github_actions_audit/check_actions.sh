#!/bin/sh
echo """
===== RUNNING ACTION LINT =====

"""

actionlint /github/workflows/*.yml

echo """

===== RUNNING ZIZMOR =====

"""

zizmor --collect=all /github/workflows/*.yml

echo "===== FINISHED ANALYSING GITHUB ACTIONS ====="
