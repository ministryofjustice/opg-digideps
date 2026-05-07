# Use the official Nginx base image
FROM nginx:1.30@sha256:1a9ab83e2892b75773978e8d91f42a7a2a0d8bb704959a51ff17c0377481973d

# Copy the nginx.conf file to the container
COPY nginx.conf /etc/nginx/nginx.conf

# Copy SSL/TLS certificates to the container
COPY certificates/nginx.crt /etc/nginx/ssl/nginx.crt
COPY certificates/nginx.key /etc/nginx/ssl/nginx.key

# Expose ports
EXPOSE 443

# Start Nginx
CMD ["nginx", "-g", "daemon off;"]
