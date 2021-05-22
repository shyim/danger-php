FROM php:8.0-cli-alpine

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
COPY . /app

RUN apk add --no-cache git zip unzip && \
    install-php-extensions bcmath gd intl sockets bz2 gmp soap zip gmp && \
    composer install --no-dev -d /app && \
    ln -s /app/bin/danger /usr/local/bin/danger

ENTRYPOINT [ "/app/bin/danger" ]