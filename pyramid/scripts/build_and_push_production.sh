#!/bin/sh
set -e

echo 'Compiling production assets...'
yarn prod

echo 'Building docker image...'
docker-buildx build -f docker/app.production.Dockerfile --tag "wa-aja/prd-pyramid-app:latest" --tag "sjc.vultrcr.com/waregistry/wa-aja/prd-pyramid-app:latest" --platform=linux/amd64 .
docker-buildx build -f docker/webserver.production.Dockerfile --tag "wa-aja/prd-pyramid-webserver:latest" --tag "sjc.vultrcr.com/waregistry/wa-aja/prd-pyramid-webserver:latest" --platform=linux/amd64 .
echo 'Build complete'

echo 'Pushing docker image...'
docker push sjc.vultrcr.com/waregistry/wa-aja/prd-pyramid-app:latest
docker push sjc.vultrcr.com/waregistry/wa-aja/prd-pyramid-webserver:latest
echo 'Image pushed'

exit 0
