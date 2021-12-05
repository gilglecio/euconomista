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

RUN curl -sL https://deb.nodesource.com/setup_14.x -o nodesource_setup.sh
RUN bash nodesource_setup.sh
RUN apt-get install nodejs -y --force-yes
RUN rm nodesource_setup.sh

RUN npm install -g bower

COPY apache_default /etc/apache2/sites-available/000-default.conf
COPY run /usr/local/bin/run
RUN chmod +x /usr/local/bin/run
RUN a2enmod rewrite

EXPOSE 80
CMD ["/usr/local/bin/run"]