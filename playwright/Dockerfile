FROM node:25-bookworm-slim@sha256:81db02c4b671288a03915da9534dbd54f96d0e7c24d80ccc54f5b36b2e684370

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
