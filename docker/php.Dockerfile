FROM php:8.2-apache AS web
RUN apt-get update && apt-get install -y --no-install-recommends libonig-dev libcurl4-openssl-dev     && docker-php-ext-install mbstring curl     && rm -rf /var/lib/apt/lists/*
COPY OdontoCare-main/ /var/www/html/
FROM web AS tools
RUN docker-php-ext-install pdo_mysql
WORKDIR /tools
COPY tools/criar_admin.php /tools/criar_admin.php
ENTRYPOINT ["php", "/tools/criar_admin.php"]
