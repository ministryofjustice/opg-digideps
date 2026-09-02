FROM node:25.6.0-alpine3.22@sha256:8c582bc73db57610b25f2ba4f71bd2d6f17f0b47367310c949acad09ea4dfcb3 AS node-base
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
