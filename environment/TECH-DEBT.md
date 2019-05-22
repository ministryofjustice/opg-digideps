# Weirdness
staging had no cache - not needed?
staging DB db.m4.xlarge

#TODO:
Regular check of service limits - Trusted Advisor
File scanner versions/images
File scanner api/worker/redis - does this need to be so robust, perhaps a single task scanner?
PHP app logs need to go to stdout
google analytics user agents - unique per env?
Fix NAT
Modernise instance types
Replace hibernation jobs w/ scheduled scaling
Production rds is multi-az - needs adding to terraform
Import & manage ACM certificates

Test client security group has no rules - can be removed?
Api client security group has no rules - can be removed?
Check and remove extra buckets
Bucket lifecycles not aligned with terraform

Add terraform for email-feedback sns topic
Add terraform for additional production dns records
Check for MX records

Move postgres to aurora
Move ami images to management
Move bootstrap modules into userdata as cloudinit template sections
