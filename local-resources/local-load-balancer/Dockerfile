# Use the official Nginx base image
FROM nginx:1.31@sha256:b34848eff6db786b6b1282d3a9c3fd0b5563dfb6d261df4923378b419e0d24f0

# Copy the nginx.conf file to the container
COPY nginx.conf /etc/nginx/nginx.conf

# Copy SSL/TLS certificates to the container
COPY certificates/nginx.crt /etc/nginx/ssl/nginx.crt
COPY certificates/nginx.key /etc/nginx/ssl/nginx.key

# Expose ports
EXPOSE 443

# Start Nginx
CMD ["nginx", "-g", "daemon off;"]
