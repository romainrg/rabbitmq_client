#!/bin/bash

# We need to install dependencies only for Docker
[[ ! -e /.dockerenv ]] && exit 0

# HTTP basic authentication
echo -e '{\n\t"http-basic": {\n\t\t"git.santiane.io": {\n\t\t\t"username": "'$COMPOSER_USERNAME'",\n\t\t\t"password": "'$COMPOSER_PASSWORD'"\n\t\t}\n\t}\n}\n' > auth.json

exec "$@"