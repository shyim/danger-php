#!/usr/bin/env bash

set -eo pipefail

version=$1
arch=$2
params=$3

if [ -z "$version" ]; then
  echo "Usage: $0 <version> <arch>"
  exit 1
fi

if [ -z "$arch" ]; then
  echo "Usage: $0 <version> <arch>"
  exit 1
fi

echo "Building version $version"

rm -rf rootfs
mkdir rootfs
mkdir rootfs/tmp

if [[ ! -d db ]]; then
  git clone -b ubuntu-23.10-php https://github.com/shyim/chisel-releases.git db
fi

chisel cut --arch=$ARCH --release ./db --root rootfs/ php8.2-cli_base php8.2-common_phar php8.2-common_ctype php8.2-mbstring_all php8.2-intl_all php8.2-xml_xml dash_bins

if [[ -e ".cache/danger-${version}.phar" ]]; then
    echo "Using cached version"
else
    mkdir -p .cache || true
    curl -q -L -o ".cache/danger-${version}.phar" "https://github.com/shyim/danger-php/releases/download/${version}/danger.phar"
fi

mkdir -p rootfs/bin
echo "#/bin/sh" > rootfs/bin/danger
echo "php /danger.phar \$@" >> rootfs/bin/danger
chmod +x rootfs/bin/danger

mkdir -p rootfs/app/bin
echo "#/bin/sh" > rootfs/app/bin/danger
echo "php /danger.phar \$@" >> rootfs/app/bin/danger
chmod +x rootfs/app/bin/danger

cp ".cache/danger-${version}.phar" rootfs/danger.phar
chmod +x rootfs/danger.phar

docker build --platform "linux/${arch}" -t "ghcr.io/shyim/danger-php:${version}-${arch}" .

if [[ "$params" == "--push" ]]; then
  docker push "ghcr.io/shyim/danger-php:${version}-${arch}"
fi