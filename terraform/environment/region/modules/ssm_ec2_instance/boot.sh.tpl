#!/bin/bash
exec > >(tee /var/log/user-data.log | logger -t user-data -s 2>/dev/console) 2>&1
set -euxo pipefail

cat << 'EOF' > /opt/bootstrap.sh
#!/bin/bash
set -euxo pipefail

dnf update -y
rm -rf /home/ec2-user/tmp/*
rm -rf /tmp/*
touch /opt/hello.txt
dnf install -y https://s3.eu-west-1.amazonaws.com/amazon-ssm-eu-west-1/latest/linux_amd64/amazon-ssm-agent.rpm
systemctl enable amazon-ssm-agent
systemctl start amazon-ssm-agent
yum install -y postgresql15
echo "SSM Instance Loaded!" | tee /dev/console
EOF

chmod +x /opt/bootstrap.sh

cat << 'EOF' > /etc/systemd/system/bootstrap.service
[Unit]
Description=Run bootstrap script at every boot
After=network.target

[Service]
ExecStart=/opt/bootstrap.sh
Type=oneshot

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reexec
systemctl daemon-reload
systemctl enable bootstrap.service
