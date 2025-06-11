#!/bin/bash
exec > >(tee /var/log/user-data.log | logger -t user-data -s 2>/dev/console) 2>&1 #Allow us to see the log if something was to go wrong.

# This runs every time the instance boots up
cat << 'EOF' > /opt/bootstrap.sh
#!/bin/bash

dnf update -y
rm -rf /home/ec2-user/tmp/*
rm -rf /tmp/*
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

/opt/bootstrap.sh

systemctl daemon-reexec
systemctl daemon-reload
systemctl enable bootstrap.service

# This runs every midnight. If no SSM session, then it will shutdown.
cat << 'EOF' > /opt/autoshutdown.sh
#!/bin/bash

if pgrep -f "ssm-session-worker" > /dev/null; then
  echo "Active SSM session detected, skipping shutdown."
  exit 0
fi

echo "No active sessions detected. Shutting down."
shutdown -h now
EOF

chmod +x /opt/autoshutdown.sh

cat << 'EOF' > /etc/systemd/system/autoshutdown.service
[Unit]
Description=Auto shutdown if no users are logged in
After=network.target

[Service]
ExecStart=/opt/autoshutdown.sh
Type=oneshot
EOF

cat << 'EOF' > /etc/systemd/system/autoshutdown.timer
[Unit]
Description=Run auto shutdown check at 12 AM daily

[Timer]
OnCalendar=*-*-* 00:00:00
Persistent=true

[Install]
WantedBy=timers.target
EOF

systemctl enable autoshutdown.timer
systemctl start autoshutdown.timer
