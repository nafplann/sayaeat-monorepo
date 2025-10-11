FROM dunglas/frankenphp

RUN install-php-extensions \
    pcntl
    # Add other PHP extensions here...

COPY . /app

# Set working directory
WORKDIR /app

ENTRYPOINT ["php", "artisan", "octane:frankenphp", "--port 8001"]
