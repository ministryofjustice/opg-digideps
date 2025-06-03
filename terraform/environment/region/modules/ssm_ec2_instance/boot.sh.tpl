#!/bin/bash

dnf update -y

rm -rf /home/ec2-user/tmp/*

rm -rf /tmp/*

dnf install -y https://s3.eu-west-1.amazonaws.com/amazon-ssm-eu-west-1/latest/linux_amd64/amazon-ssm-agent.rpm
systemctl enable amazon-ssm-agent
systemctl start amazon-ssm-agent

echo "SSM Instance Loaded!" | tee /dev/console
