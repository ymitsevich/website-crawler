FROM php:8.2-cli-alpine as base

RUN apk add -U --no-cache procps git openssh-client autoconf gcc make libc-dev libzip-dev bzip2-dev libxml2-dev libpng-dev g++ icu-dev rabbitmq-c-dev
RUN docker-php-ext-install intl
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN pecl install amqp
RUN docker-php-ext-enable amqp

COPY --chown=www-data ./app /app
COPY .infra/php/php.prod.ini /usr/local/etc/php/conf.d/php.local.ini

WORKDIR /app

RUN composer install --no-interaction --no-progress --optimize-autoloader

FROM base as dev

COPY .infra/php/php.dev.ini /usr/local/etc/php/conf.d/php.local.ini

RUN apk add -U --no-cache linux-headers
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

FROM dev as worker

RUN apk add -U --no-cache supervisor
COPY .infra/worker/supervisor-messenger.ini /etc/supervisor.d/supervisor-messenger.ini
ENV WORKER_NUMPROCS=5
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]
#ENTRYPOINT ["/usr/bin/supervisord", "-n"]
