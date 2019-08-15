#!/usr/bin/awk -f
BEGIN {
    # remove special characters
    # note: gsub mutates the target variable (ARGV[1])
    gsub(/[!"#$%&'()*+-.\/:;<=>?@[\\\]^_`{|}~]/,"", ARGV[1])
    print substr(tolower(ARGV[1]),0,14)
}
