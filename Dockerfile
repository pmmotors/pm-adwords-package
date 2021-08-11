FROM php:7.4-apache
# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    gnupg2 \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Install php-soap
RUN apt-get update && \
    apt-get install -y libxml2-dev

RUN docker-php-ext-install soap