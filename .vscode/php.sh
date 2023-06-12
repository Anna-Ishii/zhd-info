#!/bin/sh
docker-compose exec PHPコンテナ名 php "$@"
return $?