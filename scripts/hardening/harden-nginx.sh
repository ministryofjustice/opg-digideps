#!/bin/sh
echo "=== Starting Hardening Script ==="

echo "add default user"
adduser -D -s /bin/sh -u 1000 user && \
    sed -i -r 's/^user:!:/user:x:/' /etc/shadow && \
    chmod u-s /usr/sbin/login_duo

echo "/etc/duo/login_duo.conf must be readable only by user 'user'."
chown user:user /etc/duo/login_duo.conf && \
chmod 0400 /etc/duo/login_duo.conf

echo "Ensure strict ownership and perms."
chown root:root /usr/bin/github_pubkeys && \
    chmod 0555 /usr/bin/github_pubkeys && \
    echo -e "\n\nApp container image built on $(date)." > /etc/motd

echo "Remove world-writeable permissions except for /tmp/"
find / -xdev -type d -perm +0002 -exec chmod o-w {} + \
  && find / -xdev -type f -perm +0002 -exec chmod o-w {} + \
  && chmod 777 /tmp/ \
  && chown nginx:root /tmp/

echo "Remove unnecessary user accounts."
sed -i -r '/^(user|root|sshd|nginx|nobody)/!d' /etc/group
sed -i -r '/^(user|root|sshd|nginx|nobody)/!d' /etc/passwd

echo "Remove existing crontabs, if any."
rm -fr /var/spool/cron \
  && rm -fr /etc/crontabs \
  && rm -fr /etc/periodic

echo "Remove interactive login shell for everybody but user."
sed -i -r '/^user:/! s#^(.*):[^:]*$#\1:/sbin/nologin#' /etc/passwd

sysdirsforrootperms="
  /bin
  /lib
  /sbin
  /usr
"
sysdirs="
  /bin
  /etc
  /lib
  /sbin
  /usr
"
echo "Remove apk configs."
find $sysdirs -xdev -regex '.*apk.*' -exec rm -fr {} +
find $sysdirs -xdev -type f -regex '.*-$' -exec rm -f {} +

echo "Ensure system dirs are owned by root and not writable by anybody else."
find $sysdirsforrootperms -xdev -type d \
  -exec chown root:root {} \; \
  -exec chmod 0755 {} \;

echo "Remove all suid files."
find $sysdirs -xdev -type f -a -perm +4000 -delete
find $sysdirs -xdev -type f -a \( -perm +4000 -o -perm +2000 \) -delete

echo "Remove other programs that could be dangerous."
find $sysdirs -xdev \( \
  -name hexdump -o \
  -name chgrp -o \
  -name chmod -o \
  -name chown -o \
  -name ln -o \
  -name od -o \
  -name strings -o \
  -name su \
  -name sudo \
  \) -delete

echo "Remove init scripts since we do not use them."
rm -fr /etc/init.d
rm -fr /lib/rc
rm -fr /etc/conf.d
rm -fr /etc/inittab
rm -fr /etc/runlevels
rm -fr /etc/rc.conf
rm -fr /etc/logrotate.d

echo "Remove kernel tunables since we do not need them."
rm -fr /etc/sysctl*
rm -fr /etc/modprobe.d
rm -fr /etc/modules
rm -fr /etc/mdev.conf
rm -fr /etc/acpi

echo "Remove root homedir since we do not need it."
rm -fr /root

echo "Remove fstab since we do not need it."
rm -f /etc/fstab

echo "Remove all but a handful of admin commands including nginx!"
find /sbin /usr/sbin ! -type d -a ! -name apk -a ! -name ln ! -name nginx -delete

echo "Remove broken symlinks (because we removed the targets above)."
find $sysdirs -xdev -type l -exec test ! -e {} \; -delete

echo "Disable password login for everybody"
while IFS=: read -r username _; do passwd -l "$username"; done < /etc/passwd || true
