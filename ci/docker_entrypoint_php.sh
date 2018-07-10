#!/bin/bash

# We need to install dependencies only for Docker
[[ ! -e /.dockerenv ]] && exit 0

set -xe

# Install extension
docker-php-ext-install -j$(nproc) bcmath

exec "$@"
