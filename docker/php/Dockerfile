FROM php:7.4.20-cli-alpine3.13 as base

ARG DF_PATH=docker/php

RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer
RUN PATH=$PATH:/usr/src/myapp/vendor/bin:bin

WORKDIR /usr/src/myapp

CMD /usr/local/bin/composer self-update && \
    composer install --no-scripts --prefer-dist && \
    vendor/bin/simple-phpunit

FROM base as prod

COPY project /usr/src/myapp
RUN composer install --no-scripts --prefer-dist && \
    rm -rf "$(composer config cache-dir)" "$(composer config data-dir)"

