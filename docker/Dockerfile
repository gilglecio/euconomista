FROM php:8.2-apache

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
      git \
      unzip \
      locales \
      libzip-dev \
      libpng-dev \
      libjpeg-dev \
      libfreetype6-dev \
      nodejs \
      npm && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install pdo_mysql mysqli gd zip && \
    rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN npm install -g bower

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint && a2enmod rewrite headers

WORKDIR /var/www

EXPOSE 80
CMD ["/usr/local/bin/entrypoint"]
