FROM node:16-alpine AS webpack

WORKDIR /app

COPY package.json .
COPY package-lock.json .
COPY webpack.config.js .
COPY assets assets

# Install NPM dependencies
RUN npm install

# Check linting
RUN npm run lint
RUN npm audit --production