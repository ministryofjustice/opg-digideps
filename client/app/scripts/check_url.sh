
#!/bin/sh

# Define URLs (or read from a file)
urls="
https://example.com
https://google.com
https://nonexistentsecure.example
http://example.com
"

# Loop through each URL
for url in $urls; do
    echo "Trying to curl: $url"

    # Perform curl with timeout and silent mode
    if curl --silent --head --fail --max-time 5 "$url" >/dev/null 2>&1; then
        echo "✅ Success: Received a response from $url"
    else
        echo "❌ Failed: No response from $url"
    fi

    echo "-----------------------------------"
done
