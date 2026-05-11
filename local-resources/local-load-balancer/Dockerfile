# Use the official Nginx base image
FROM nginx:1.30@sha256:55d1fb0944aafb8534b253e486c6cf57d3e0c785c4f6a7b3e9f02c5a080fb2ce

# Copy the nginx.conf file to the container
COPY nginx.conf /etc/nginx/nginx.conf

# Copy SSL/TLS certificates to the container
COPY certificates/nginx.crt /etc/nginx/ssl/nginx.crt
COPY certificates/nginx.key /etc/nginx/ssl/nginx.key

# Expose ports
EXPOSE 443

# Start Nginx
CMD ["nginx", "-g", "daemon off;"]
