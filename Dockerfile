FROM debian:jessie

RUN apt-get update && \
    apt-get install -y \
      locales \
      apache2 \
      php5 \
      curl \
      git \
      php5-cli \
      libapache2-mod-php5 \
      php5-gd \
      php5-curl \
      php5-json \
      php5-ldap \
      php5-mysql \
      php5-pgsql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -fsSL https://deb.nodesource.com/setup_lts.x | bash -
RUN apt-get install -y nodejs --force-yes
RUN npm install -g bower

COPY apache_default /etc/apache2/sites-available/000-default.conf
COPY run /usr/local/bin/run
RUN chmod +x /usr/local/bin/run
RUN a2enmod rewrite

WORKDIR /var/www

RUN mkdir log && chmod -r 777 log
RUN mkdir cache && chmod -r 777 cache

EXPOSE 80
CMD ["/usr/local/bin/run"]