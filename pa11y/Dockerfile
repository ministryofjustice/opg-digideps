FROM node:14.5.0-alpine3.10

# Installs latest Chromium (77) package.
RUN apk add --no-cache \
      chromium \
      nss \
      freetype \
      freetype-dev \
      harfbuzz \
      ca-certificates \
      ttf-freefont \
      nodejs \
      yarn


# Tell Puppeteer to skip installing Chrome. We'll be using the installed package.
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true \
    PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium-browser

# Puppeteer v1.19.0 works with Chromium 77.
RUN yarn add puppeteer@1.19.0

# Add user so we don't need --no-sandbox.
#RUN addgroup -S pptruser && adduser -S -g pptruser pptruser \
#    && mkdir -p /home/pptruser/Downloads /app \
#    && chown -R pptruser:pptruser /home/pptruser \
#    && chown -R pptruser:pptruser /app

# Run everything after as non-privileged user.
#USER pptruser

#FROM node:14.5.0 AS node
WORKDIR /home/node/app


RUN npm -g config set user root

#RUN mkdir global
#RUN chmod 777 /home/node/app/global
#RUN npm install puppeteer --unsafe-perm=true

ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD = false
#RUN npm config set prefix /home/node/app/global

RUN npm install -g pa11y-ci

#RUN ldd /usr/local/lib/node_modules/pa11y-ci/node_modules/puppeteer/.local-chromium/linux-686378/chrome-linux/chrome | grep not

COPY .pa11yci .

#RUN pa11y-ci > output.txt

#RUN cat output.txt
