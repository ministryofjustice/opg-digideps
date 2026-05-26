FROM node:25.5.0-alpine3.22@sha256:7cda5d4400613ff7a4e9b2014bded8fa153871c32fd7ee12884c397c79bdcc93 AS node-base
WORKDIR /app
COPY client/resources/package.json .
COPY client/resources/package-lock.json .
COPY client/resources/deps-builder.js .
COPY client/resources/assets assets

# Install NPM dependencies
RUN npm ci --ignore-scripts
RUN npm audit --production

FROM node-base AS testing
# hadolint ignore=DL3016
RUN npm ci --include=dev

FROM node-base AS build

RUN npm ci --ignore-scripts
RUN npm audit --production
RUN NODE_ENV=production npm run build
