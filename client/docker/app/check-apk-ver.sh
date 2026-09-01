#!/usr/bin/env sh
set -eu

DOCKERFILE=${1:-Dockerfile}

apk update >/dev/null

grep -oE '"[^"]+=[^"]+"' "$DOCKERFILE" |
while read -r entry; do
    package=$(echo "$entry" | cut -d'"' -f2 | cut -d= -f1 | xargs)
    current=$(echo "$entry" | cut -d'"' -f2 | awk -F'==' '{print $2}' | xargs)
    latest=$(
        apk policy "$package" |
        awk '
            /^[[:space:]]*[0-9].*:$/ {
                gsub(":", "")
                print
                exit
            }
        ' |
        xargs
    )

	echo "$current"
	echo "$latest"

    if [ -n "$latest" ] && [ "$current" != "$latest" ]; then
        echo "$package: $current -> $latest"

        sed -i \
            "s#\"$package==$current\"#\"$package==$latest\"#g" \
            "$DOCKERFILE"
    fi
done

ls -alt "$DOCKERFILE"
pwd
cat "$DOCKERFILE" | grep unzip
