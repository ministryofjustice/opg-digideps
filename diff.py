import difflib
import os

f = open('./diff_results.txt', "w")

directory = r'/Users/loanuser/CHECK_DIFFS/NEW'
for entry in os.scandir(directory):
    print()

    old = open('/Users/loanuser/CHECK_DIFFS/OLD/' + entry.name).readlines()
    new = open('/Users/loanuser/CHECK_DIFFS/NEW/' + entry.name).readlines()

    diff_lines = []
    for line in difflib.unified_diff(old, new, lineterm="", n=0):
        for prefix in ("---", "+++", "@@", "-}", "+}"):
            if line.startswith(prefix):
                break
        else:
            diff_lines.append(line)

    if len(diff_lines) > 0:
        f.write(
            "Difference in file %s\n"
            % entry.name
        )
        for diff_line in diff_lines:
            f.write(diff_line)

f.close()
