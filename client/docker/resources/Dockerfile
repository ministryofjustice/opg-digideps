FROM node:18.16.1-alpine AS node-base
WORKDIR /app
COPY client/resources/package.json .
COPY client/resources/package-lock.json .
COPY client/resources/webpack.config.js .
COPY client/resources/assets assets

# Install NPM dependencies
RUN npm install
RUN npm audit --production

FROM node-base AS testing
# hadolint ignore=DL3016
RUN npm install --include=dev

FROM node-base AS build

#Build assets
RUN NODE_ENV=production npm run build