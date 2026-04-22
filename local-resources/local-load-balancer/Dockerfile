# Use the official Nginx base image
FROM nginx:1.30@sha256:0c3a604353135ac13f3a991e2a98c9958af7b8c0b7251ce1590e0c5ff5e3692c

# Copy the nginx.conf file to the container
COPY nginx.conf /etc/nginx/nginx.conf

# Copy SSL/TLS certificates to the container
COPY certificates/nginx.crt /etc/nginx/ssl/nginx.crt
COPY certificates/nginx.key /etc/nginx/ssl/nginx.key

# Expose ports
EXPOSE 443

# Start Nginx
CMD ["nginx", "-g", "daemon off;"]
