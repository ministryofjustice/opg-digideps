#!/bin/bash
exec > >(tee /var/log/user-data.log | logger -t user-data -s 2>/dev/console) 2>&1 #Allow us to see the log if something was to go wrong.

# This is the Database Script to make connecting to the database easier
cat << 'EOF' > /usr/local/bin/database
#!/bin/bash

command="\${1:-}"

secret_exists() {
  local name="\$1"
  aws secretsmanager describe-secret --secret-id "\$name" >/dev/null 2>&1
}

get_secret_value() {
  local name="\$1"
  aws secretsmanager get-secret-value --secret-id "\$name" --query SecretString --output text 2>/dev/null
}

list_databases() {
  printf "%-30s %-70s %-35s %-35s\n" "Database" "Endpoint" "digidepsmaster password" "readonly password"
  printf "%-30s %-70s %-35s %-35s\n" "---------" "---------" "------------------------" "------------------"

  dbs=\$(aws rds describe-db-clusters --query "DBClusters[?Engine=='aurora-postgresql'].[DBClusterIdentifier,Endpoint]" --output text)

  while read -r identifier endpoint; do
    if [[ "\$identifier" == *ddls* ]]; then
      env="default"
    else
      env="\${identifier#api-}"
    fi

    digideps_secret="\${env}/database-password"
    readonly_secret="\${env}/readonly-sql-db-password"

    if [[ "\$env" == "default" ]]; then
      if ! secret_exists "\$digideps_secret"; then
        digideps_secret="Access Denied"
        readonly_secret="Access Denied"
      fi
    else
      if ! secret_exists "\$digideps_secret"; then
        if secret_exists "default/database-password"; then
          digideps_secret="default/database-password"
        else
          digideps_secret="Access Denied"
        fi
      fi

      if ! secret_exists "\$readonly_secret"; then
        if secret_exists "default/readonly-sql-db-password"; then
          readonly_secret="default/readonly-sql-db-password"
        else
          readonly_secret="Access Denied"
        fi
      fi
    fi

    printf "%-30s %-70s %-35s %-35s\n" "\$identifier" "\$endpoint" "\$digideps_secret" "\$readonly_secret"
  done <<< "\$dbs"

  echo
  echo "To connect: database connect <database> <view|admin>"
  echo "Example:    database connect api-ddls5451990 admin"
}

connect_to_database() {
  identifier="\${1:-}"
  access="\${2:-}"

  if [[ -z "\$identifier" || -z "\$access" ]]; then
    echo "Usage: database connect <database> <view|admin>"
    exit 1
  fi

  dbs=\$(aws rds describe-db-clusters --query "DBClusters[?DBClusterIdentifier=='\$identifier'].[DBClusterIdentifier,Endpoint]" --output text)
  read -r cluster_id endpoint <<< "\$dbs"

  if [[ -z "\$endpoint" || "\$endpoint" == "None" ]]; then
    echo "Could not find endpoint for cluster '\$identifier'"
    exit 1
  fi

  if [[ "\$identifier" == *ddls* ]]; then
    env="default"
  else
    env="\${identifier#api-}"
  fi

  if [[ "\$access" == "admin" ]]; then
    user="digidepsmaster"
    secret_name="\${env}/database-password"
  elif [[ "\$access" == "view" ]]; then
    user="readonly_sql_user"
    secret_name="\${env}/readonly-sql-db-password"
  else
    echo "Invalid access level: must be 'view' or 'admin'"
    exit 1
  fi

  if ! secret_exists "\$secret_name"; then
    if [[ "\$env" != "default" ]]; then
      fallback="default/\${secret_name#*/}"
      if secret_exists "\$fallback"; then
        secret_name="\$fallback"
      else
        echo "Access Denied"
        exit 1
      fi
    else
      echo "Access Denied"
      exit 1
    fi
  fi

  password=\$(get_secret_value "\$secret_name")
  if [[ -z "\$password" ]]; then
    echo "Failed to retrieve password"
    exit 1
  fi

  echo "Connecting to \$endpoint as \$user"
  PGPASSWORD="\$password" psql -h "\$endpoint" -U "\$user" -d postgres -p 5432
}

case "\$command" in
  list)
    list_databases
    ;;
  connect)
    connect_to_database "\$2" "\$3"
    ;;
  *)
    echo "Usage:"
    echo "  \$0 list"
    echo "  \$0 connect <cluster-identifier> <view|admin>"
    exit 1
    ;;
esac
EOF

chmod +x /usr/local/bin/database

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

cat << 'EOF' > /etc/motd
Welcome to the DigiDeps SSM Server

Available commands:

  database list
    → Shows all connectable Aurora PostgreSQL databases with access info

  database connect <database> <view|admin>
    → Connects you to the given database using psql
    → Example: database connect production02 view
EOF


systemctl enable autoshutdown.timer
systemctl start autoshutdown.timer
