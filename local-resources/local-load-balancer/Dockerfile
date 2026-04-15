# Use the official Nginx base image
FROM nginx:1.29@sha256:7f0adca1fc6c29c8dc49a2e90037a10ba20dc266baaed0988e9fb4d0d8b85ba0

# Copy the nginx.conf file to the container
COPY nginx.conf /etc/nginx/nginx.conf

# Copy SSL/TLS certificates to the container
COPY certificates/nginx.crt /etc/nginx/ssl/nginx.crt
COPY certificates/nginx.key /etc/nginx/ssl/nginx.key

# Expose ports
EXPOSE 443

# Start Nginx
CMD ["nginx", "-g", "daemon off;"]
