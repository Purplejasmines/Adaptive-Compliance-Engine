# ZRA System Dockerfile
FROM python:3.11-slim

# Set working directory
WORKDIR /app

# Install system dependencies and curl for healthcheck; reduce image size
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    curl \
    gcc \
    g++ \
    libpq-dev \
 && rm -rf /var/lib/apt/lists/*

# Copy requirements and install Python dependencies
COPY requirements-minimal.txt requirements.txt
RUN python -m pip install --upgrade pip setuptools wheel \
 && pip install --no-cache-dir --timeout 120 --retries 5 -r requirements.txt \
 && rm -f requirements.txt

# Copy application code (after deps to leverage layer caching)
COPY . .

# Create necessary directories
RUN mkdir -p /app/data/training /app/models /app/logs

# Set environment variables
ENV PYTHONPATH=/app
ENV PYTHONUNBUFFERED=1
ENV PIP_NO_CACHE_DIR=1 \
    PIP_DEFAULT_TIMEOUT=120 \
    PIP_RETRIES=5

# Optional PyPI mirrors (override at build time)
ARG PIP_INDEX_URL=https://pypi.org/simple
ARG PIP_EXTRA_INDEX_URL
ENV PIP_INDEX_URL=${PIP_INDEX_URL}
ENV PIP_EXTRA_INDEX_URL=${PIP_EXTRA_INDEX_URL}

# Expose port
EXPOSE 8000

# Health check
HEALTHCHECK --interval=30s --timeout=30s --start-period=5s --retries=3 \
  CMD curl -fsS http://localhost:8000/health || exit 1

# Use non-root user for runtime
RUN useradd -m appuser \
 && chown -R appuser:appuser /app
USER appuser

# Run the application with a single worker and no reload for low resource usage
CMD ["python", "-m", "uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8000", "--workers", "1"]


