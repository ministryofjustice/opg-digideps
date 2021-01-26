import os

# The purpose of this script is to bulk edit annotations
# It could equally be amended for any other bulk update where a simple find replace isn't enough

def find_between( s, first, last ):
    try:
        start = s.index( first ) + len( first )
        end = s.index( last, start )
        return s[start:end]
    except ValueError:
        return ""


for root, dirs, files in os.walk('.'):
    for file in files:
        if ".py" not in file:
            file = f"{root}/{file}"

            f = open(file, "r")
            contents = f.readlines()
            f.close()

            prev_start_annot = 0
            current_line = 0
            prev_end_annot = 0
            string_line = False
            already_done = False
            groups = ""
            lines_numbers_to_insert = []

            for line in contents:

                current_line = current_line + 1

                if "/**" in line:
                    prev_start_annot=current_line

                if "*/" in line:
                    prev_end_annot=current_line

                if prev_start_annot > prev_end_annot:
                    if "@var string" in line:
                        string_line = True
                    if "TextNoSpecialCharacters" in line:
                        already_done = True

                    if "@Assert" in line:
                        if "groups=" in line:
                            groups = "(groups=" + find_between(line, "groups=", "}") + "})"

                else:
                    if not already_done and string_line:
                        index = prev_end_annot - 1
                        contents.insert(index, f"     * @AppAssert\TextNoSpecialCharacters{groups}\n")

                    already_done = False
                    string_line = False
                    groups = ""

            f = open(file, "w")
            f.writelines(contents)
            f.close()
