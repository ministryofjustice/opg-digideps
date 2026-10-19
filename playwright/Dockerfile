FROM node:26-bookworm-slim@sha256:298992629d69bbd5b2b03ad57094d8e4fcb3a8eeb7c4e25d29de656519568913

RUN apt-get update && apt-get upgrade -y && rm -rf /var/lib/apt/lists/*

WORKDIR /playwright

# Use shared location for browsers
ENV PLAYWRIGHT_BROWSERS_PATH=/ms-playwright


COPY package.json package-lock.json ./
RUN npm ci

# Install browsers + deps (must be done after npm ci)
RUN npx playwright install --with-deps chromium


# Fix permissions for node user
RUN chown -R node:node /playwright /ms-playwright

COPY tests tests
COPY package-lock.json .
COPY package.json .
COPY tsconfig.json .
COPY playwright.config.ts .
COPY eslint.config.mjs .
COPY .prettierignore .

USER node

ENTRYPOINT ["npm", "run"]
