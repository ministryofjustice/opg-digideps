FROM node:18.16.1-alpine as node-base
WORKDIR /app
COPY client/resources/package.json .
COPY client/resources/package-lock.json .
COPY client/resources/webpack.config.js .
COPY client/resources/assets assets
# Install NPM dependencies
RUN npm install
# Check linting
RUN npm run lint
RUN npm audit --production
#Build assets
RUN NODE_ENV=production npm run build

FROM node-base as testing
# hadolint ignore=DL3016
RUN npm install -g jest jest-environment-jsdom
RUN npm run build

FROM node-base as build
