FROM node:25.3.0-alpine3.22@sha256:e7b37edfbf338dad4a48ac77b072a78afd06728dffe8c1803fd9f5cd2d4c1dc8 AS node-base
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
