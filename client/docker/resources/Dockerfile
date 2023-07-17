FROM node:18.14.2-alpine
WORKDIR /app
COPY client/resources/package.json .
COPY client/resources/package-lock.json .
COPY client/resources/webpack.config.js .
COPY client/app/templates templates
COPY client/resources/assets assets
COPY client/app/translations translations
# Install NPM dependencies
RUN npm install
# Check linting
RUN npm run lint
RUN npm audit --production
#Build assets
RUN NODE_ENV=production npm run build
