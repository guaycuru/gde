FROM php:8.5-apache

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libonig-dev \
    libzip-dev \
    unzip \
    zip \
    default-mysql-client \
    s3cmd \
    msmtp-mta \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring gd zip bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && echo "session.save_handler = redis" >> /usr/local/etc/php/conf.d/sessions.ini \
    && echo "session.save_path = \"tcp://redis:6379\"" >> /usr/local/etc/php/conf.d/sessions.ini \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Envio de email: mail() do PHP (classes/GDE/Util.php) shella pro binário de
# sendmail_path. msmtp-mta já se registra como /usr/sbin/sendmail. O
# /etc/msmtprc em si é gerado em runtime pelo entrypoint (docker/entrypoint.sh)
# a partir de SMTP_HOST/SMTP_PORT (env, via configmap do repo gde-k8s) —
# não hardcoded aqui porque este repo é espelhado publicamente no GitHub.
RUN echo "sendmail_path = /usr/bin/msmtp -t -i" > /usr/local/etc/php/conf.d/mail.ini \
    && touch /var/log/msmtp.log && chown www-data:www-data /var/log/msmtp.log

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

# config-k8s.inc.php becomes the active config inside the image
COPY common/config-k8s.inc.php common/config.inc.php

COPY docker/apache-gde.conf /etc/apache2/sites-available/000-default.conf

COPY docker/entrypoint.sh /usr/local/bin/gde-entrypoint.sh
RUN chmod +x /usr/local/bin/gde-entrypoint.sh

# Update dependencies for PHP 8.5 compatibility and install without dev packages.
# Commit the resulting composer.lock back to the repo so subsequent builds
# can use `composer install` instead of `update`.
RUN composer install --no-interaction --no-dev --optimize-autoloader

RUN vendor/bin/doctrine orm:generate-proxies \
    && bin/doctrine_dac orm:generate-proxies \
    && mkdir -p cache/arvores cache/dac_results \
    && chown -R www-data:www-data cache proxies \
    && chmod -R 775 cache proxies

ENTRYPOINT ["gde-entrypoint.sh"]
CMD ["apache2-foreground"]
