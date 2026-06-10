FROM python:3.12-slim

# Install Chromium dependencies
RUN apt-get update && apt-get install -y \
    curl \
    ca-certificates \
    fonts-liberation \
    libnss3 \
    libatk-bridge2.0-0 \
    libgtk-3-0 \
    libx11-xcb1 \
    libxcomposite1 \
    libxdamage1 \
    libxrandr2 \
    libgbm1 \
    libasound2 \
    libpangocairo-1.0-0 \
    libatk1.0-0 \
    libcups2 \
    libdrm2 \
    --no-install-recommends \
    && rm -rf /var/lib/apt/lists/*

# Install Python deps
RUN pip install --no-cache-dir \
    playwright \
    pytest \
    pytest-xdist \
    pytest-playwright

# Install Chromium + deps
RUN playwright install --with-deps chromium

WORKDIR /app

# Copy entire project
COPY . .

# Default command = run tests in parallel
CMD ["pytest", "-n", "0", "-s", "-v"]
