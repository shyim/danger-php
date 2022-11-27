FROM ghcr.io/shyim/danger-php-base:latest

COPY . /app
RUN composer install --no-dev -d /app && \
    ln -s /app/bin/danger /usr/local/bin/danger

ENTRYPOINT [ "/app/bin/danger" ]
