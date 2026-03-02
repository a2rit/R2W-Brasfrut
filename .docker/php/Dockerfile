FROM php:7.4-fpm-buster

ENV ACCEPT_EULA=Y

RUN apt update
RUN apt install -y unixodbc-dev apt-transport-https gnupg2 libpng-dev libzip-dev unzip git zip

# Install prerequisites for the sqlsrv and pdo_sqlsrv PHP extensions.
RUN curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add - \
    && curl https://packages.microsoft.com/config/debian/10/prod.list > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && apt-get install -y msodbcsql17 mssql-tools unixodbc-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo
RUN pecl install pdo_sqlsrv-5.10.0
RUN docker-php-ext-enable pdo_sqlsrv
RUN docker-php-ext-install bcmath

# install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN usermod -u 1000 -d /home/www-data www-data && groupmod -g 1000 www-data
