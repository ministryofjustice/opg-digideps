#!/bin/sh
# Exit immediately if a command exits with a non-zero status.
set -e

# /var/www/var/cache is the tmpfs mount point owned by root initially.
# Change ownership to www-data so the application user can write to it.
echo "Fixing ownership of /var/www/var/cache..."
chown www-data:www-data /var/www/var/cache

# Now, copy the pre-warmed cache from the backup into the tmpfs mount.
# Running this as root is often easiest, using -p to preserve permissions as much as possible
# (though ownership will be relative to the container's users).
# Using -a (archive) implies -Rp and more, good for caches.
echo "Restoring pre-warmed cache..."
cp -a /var/www/cache_backup/. /var/www/var/cache/

# Alternatively, if you want the final files owned by www-data explicitly during copy:
# echo "Restoring pre-warmed cache as www-data..."
# su-exec www-data cp -a /var/www/cache_backup/. /var/www/var/cache/

echo "Cache restored. Starting php-fpm as www-data..."
# Use su-exec (or gosu) to drop privileges and run the main command (passed via CMD)
# "$@" passes any arguments from the Docker CMD instruction
exec su-exec www-data "$@"
