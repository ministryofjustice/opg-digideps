FROM node:18.16.1-alpine

# Installs latest Chromium (77) package.
RUN apk add --no-cache \
      chromium \
      nss \
      freetype \
      freetype-dev \
      harfbuzz \
      ca-certificates \
      ttf-freefont

# Tell Puppeteer to skip installing Chrome. We'll be using the installed package.
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true \
    PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium-browser

# Puppeteer v1.19.0 works with Chromium 77.
RUN npm i puppeteer@1.19.0

WORKDIR /home/node/app

RUN npm -g config set user root

ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD = false

RUN npm install -g pa11y-ci@3.1.0

COPY .pa11yci .
