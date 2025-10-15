FROM php:8.2-cli

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    git \
    curl \
    wget \
    unzip \
    libzip-dev \
    chromium \
    chromium-driver \
    && rm -rf /var/lib/apt/lists/*

# Установка PHP расширений
RUN docker-php-ext-install zip pcntl

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader

CMD ["tail", "-f", "/dev/null"]
