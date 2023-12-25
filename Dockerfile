FROM scratch
COPY rootfs/ /
ENTRYPOINT [ "/usr/bin/php", "/danger.phar" ]
