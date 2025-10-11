#!/bin/sh
set -e

echo 'Checking for updates...'
docker-compose -f docker-compose.production.yml -p prd-pyramid pull
echo 'Stop and removing current containers...'
docker-compose -f docker-compose.production.yml -p prd-pyramid down
echo 'Running containers...'
docker-compose -f docker-compose.production.yml -p prd-pyramid up -d
echo 'Pruning old images...'
docker image prune --force

exit 0
