#!/bin/sh
set -e

echo 'Building docker image...'
docker-buildx build -f docker/app.staging.Dockerfile --tag "wa-aja/stg-pyramid-app:latest" --tag "sjc.vultrcr.com/waregistry/wa-aja/stg-pyramid-app:latest" --platform=linux/amd64 .
docker-buildx build -f docker/webserver.staging.Dockerfile --tag "wa-aja/stg-pyramid-webserver:latest" --tag "sjc.vultrcr.com/waregistry/wa-aja/stg-pyramid-webserver:latest" --platform=linux/amd64 .
echo 'Build complete'

echo 'Pushing docker image...'
docker push sjc.vultrcr.com/waregistry/wa-aja/stg-pyramid-app:latest
docker push sjc.vultrcr.com/waregistry/wa-aja/stg-pyramid-webserver:latest
echo 'Image pushed'

exit 0
