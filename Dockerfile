FROM cgr.dev/chainguard/wolfi-base:latest@sha256:88a20e2d017bd20cdc0cb3910de603e7bea9512a6c351a8dd3c9df212ab03d73

RUN <<EOF
set -eo pipefail
apk add --no-cache --upgrade \
    ca-certificates \
    php-8.5 \
    php-8.5-mbstring \
    php-8.5-ctype \
    php-8.5-curl \
    php-8.5-intl \
    php-8.5-phar \
    php-8.5-xml \
    php-8.5-xmlreader \
    php-8.5-xmlwriter \
    php-8.5-dom \
    php-8.5-simplexml

mkdir -p /bin
echo "#!/bin/sh" > /bin/danger
echo "php /danger.phar \"\$@\"" >> /bin/danger
chmod +x /bin/danger

mkdir -p /app/bin
echo "#!/bin/sh" > /app/bin/danger
echo "php /danger.phar \"\$@\"" >> /app/bin/danger
chmod +x /app/bin/danger
EOF

COPY danger.phar /danger.phar
RUN danger --version
ENTRYPOINT [ "/usr/bin/php", "/danger.phar" ]
