FROM node:26-bookworm-slim@sha256:28147155d47dc31abe0a46e0d12b59b2fb3d0a2dcc5dc65fce76a074180b16fe

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
