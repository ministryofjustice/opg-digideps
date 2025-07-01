#!/bin/bash
exec > >(tee /var/log/user-data.log | logger -t user-data -s 2>/dev/console) 2>&1 #Allow us to see the log if something was to go wrong.

# This is the Database Script to make connecting to the database easier
cat << 'EOF' > /usr/local/bin/database
#!/bin/bash

command="${1:-}"
environment="${2:-}"

secret_exists() {
  local name="$1"
  aws secretsmanager describe-secret --secret-id "$name" >/dev/null 2>&1
}

get_secret_value() {
  local name="$1"
  aws secretsmanager get-secret-value --secret-id "$name" --query SecretString --output text 2>/dev/null
}

list_databases() {
  printf "%-20s %-30s %-70s\n" "Environment" "Database" "Endpoint"
  printf "%-20s %-30s %-70s\n" "-----------" "---------" "---------"

  dbs=$(aws rds describe-db-clusters --query "DBClusters[?Engine=='aurora-postgresql'].[DBClusterIdentifier,Endpoint]" --output text)

  while read -r database endpoint; do
    env=$(echo "$database" | awk -F'-' '{print $2}')
    printf "%-20s %-30s %-70s\n" "$env" "$database" "$endpoint"
  done <<< "$dbs"

  echo
  echo "To connect: database connect <environment|database> <read|edit>"
  echo "Example:    database connect production02 read"
}

connect_to_database() {
  input="$1"
  access="$2"

  if [[ -z "$input" || -z "$access" ]]; then
    echo "Usage: database connect <environment|database> <read|edit>"
    exit 1
  fi

  if [[ "$input" == api-* ]]; then
    database="$input"
    environment=$(echo "$input" | awk -F'-' '{print $2}')
  else
    database="api-$input"
    environment="$input"
  fi

  exists=$(aws rds describe-db-clusters --query "DBClusters[?DBClusterIdentifier=='${database}'].DBClusterIdentifier" --output text)

  if [[ -z "$exists" ]]; then
    echo "Error: Database '${database}' does not exist."
    exit 1
  fi

  if [[ "$access" == "edit" ]]; then
    user="digidepsmaster"
    secret_name="${environment}/database-password"

    if ! secret_exists "$secret_name"; then
      fallback="default/database-password"
      if secret_exists "$fallback"; then
        secret_name="$fallback"
      else
        echo "Access Denied"
        exit 1
      fi
    fi

    password=$(get_secret_value "$secret_name")
    if [[ -z "$password" ]]; then
      echo "Failed to retrieve password"
      exit 1
    fi

    HOST=$(aws rds describe-db-instances --region eu-west-1 --db-instance-identifier "${database}-0" --query 'DBInstances[0].Endpoint.Address' --output text)

    if [[ -z "$HOST" || "$HOST" == "None" ]]; then
      echo "Error: Could not resolve DB instance for '${database}-0'"
      exit 1
    fi

    echo "Connecting to $HOST as $user"
    PGPASSWORD="$password" psql -h "$HOST" -U "$user" -d api -p 5432

  elif [[ "$access" == "read" ]]; then
    ACCOUNT_ID=$(aws sts get-caller-identity --query "Account" --output text)
    HOST=$(aws rds describe-db-instances --region eu-west-1 --db-instance-identifier "${database}-0" --query 'DBInstances[0].Endpoint.Address' --output text)

    if [[ -z "$HOST" || "$HOST" == "None" ]]; then
      echo "Error: Could not resolve DB instance for '${database}-0'"
      exit 1
    fi

    CREDS=$(aws sts assume-role --role-arn "arn:aws:iam::$ACCOUNT_ID:role/readonly-db-iam-${environment}" --role-session-name db-readonly-session 2>/dev/null)

    if [[ -z "$CREDS" ]]; then
      echo "Error: Could not assume readonly role for environment '${environment}'"
      exit 1
    fi

    export AWS_ACCESS_KEY_ID=$(echo "$CREDS" | jq -r .Credentials.AccessKeyId)
    export AWS_SECRET_ACCESS_KEY=$(echo "$CREDS" | jq -r .Credentials.SecretAccessKey)
    export AWS_SESSION_TOKEN=$(echo "$CREDS" | jq -r .Credentials.SessionToken)

    curl -s https://truststore.pki.rds.amazonaws.com/global/global-bundle.pem -o /tmp/rds-combined-ca-bundle.pem

    TOKEN=$(aws rds generate-db-auth-token --hostname "$HOST" --port 5432 --username readonly-db-iam-${environment} --region eu-west-1)

    echo "Connecting to $HOST as readonly-db-iam-${environment}"
    PGPASSWORD="$TOKEN" psql "host=$HOST port=5432 dbname=api user=readonly-db-iam-${environment} sslmode=require sslrootcert=/tmp/rds-combined-ca-bundle.pem"

  else
    echo "Invalid access level: must be 'read' or 'edit'"
    exit 1
  fi
}

case "$command" in
  list)
    list_databases
    ;;
  connect)
    connect_to_database "$2" "$3"
    ;;
  *)
    echo "Usage:"
    echo "database list"
    echo "database connect <environment|database> <read|edit>"
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
