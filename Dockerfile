# Donut Shop Management System - Application Container
FROM php:8.2-apache

# Install mysqli extension and curl for health checks
RUN apt-get update && apt-get install -y --no-install-recommends curl \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install mysqli \
    && docker-php-ext-enable mysqli

# Enable Apache mod_rewrite (optional, for future routing)
RUN a2enmod rewrite

# Copy application source into web root
WORKDIR /var/www/html
COPY index.php db.php style.css ./

# Apache listens on port 80
EXPOSE 80

# Health check - verify Apache is responding
HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
    CMD curl -f http://localhost/ || exit 1
