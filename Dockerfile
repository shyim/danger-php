FROM ghcr.io/shyim/wolfi-php/base:latest

RUN <<EOF
set -eo pipefail
apk add --no-cache \
    ca-certificates \
    php-8.2 \
    php-8.2-mbstring \
    php-8.2-ctype \
    php-8.2-curl \
    php-8.2-intl \
    php-8.2-phar \
    php-8.2-xml \
    php-8.2-xmlreader \
    php-8.2-xmlwriter \
    php-8.2-dom \
    php-8.2-simplexml

mkdir -p /bin
echo "#/bin/sh" > /bin/danger
echo "php /danger.phar \"\$@\"" >> /bin/danger
chmod +x /bin/danger

mkdir -p /app/bin
echo "#/bin/sh" > /app/bin/danger
echo "php /danger.phar \"\$@\"" >> /app/bin/danger
chmod +x /app/bin/danger
EOF

COPY danger.phar /danger.phar
ENTRYPOINT [ "/usr/bin/php", "/danger.phar" ]
