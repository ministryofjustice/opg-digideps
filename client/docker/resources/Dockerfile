FROM node:25.4.0-alpine3.22@sha256:8db2f03cd982fa5bbad37fcd248202dbd28a6ec6f8312dc1ee7efe8c39c67384 AS node-base
WORKDIR /app
COPY client/resources/package.json .
COPY client/resources/package-lock.json .
COPY client/resources/deps-builder.js .
COPY client/resources/assets assets

# Install NPM dependencies
RUN npm ci
RUN npm audit --production

FROM node-base AS testing
# hadolint ignore=DL3016
RUN npm ci --include=dev

FROM node-base AS build

# Build assets
RUN NODE_ENV=production npm run build
