FROM node:25.2.0-alpine3.22@sha256:3f162243a45e30f6b20f80255779785789e142bec7b1d5d3b431f31d5b3610f6 AS node-base
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
