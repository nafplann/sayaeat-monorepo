## Login
`docker login https://sjc.vultrcr.com/waregistry -u 21f93317-44d8-42bc-ac88-43717a147b1e -p u9VjcZBEKU6ujDR4vy8mZxorrtLx7UoVcmxw`

# Staging
## Build
1. docker-buildx build -f docker/app.staging.Dockerfile --tag "wa-aja/stg-pyramid-app:latest" --tag "sjc.vultrcr.com/waregistry/wa-aja/stg-pyramid-app:latest" --platform=linux/amd64 .
2. docker-buildx build -f docker/webserver.staging.Dockerfile --tag "wa-aja/stg-pyramid-webserver:latest" --tag "sjc.vultrcr.com/waregistry/wa-aja/stg-pyramid-webserver:latest" --platform=linux/amd64 .

## Push
1. docker push sjc.vultrcr.com/waregistry/wa-aja/stg-pyramid-app:latest
2. docker push sjc.vultrcr.com/waregistry/wa-aja/stg-pyramid-webserver:latest

# Production
## Build
1. docker-buildx build -f docker/app.production.Dockerfile --tag "wa-aja/prd-pyramid-app:latest" --tag "sjc.vultrcr.com/waregistry/wa-aja/prd-pyramid-app:latest" --platform=linux/amd64 .
2. docker-buildx build -f docker/webserver.production.Dockerfile --tag "wa-aja/prd-pyramid-webserver:latest" --tag "sjc.vultrcr.com/waregistry/wa-aja/prd-pyramid-webserver:latest" --platform=linux/amd64 .

## Push
1. docker push sjc.vultrcr.com/waregistry/wa-aja/prd-pyramid-app:latest
2. docker push sjc.vultrcr.com/waregistry/wa-aja/prd-pyramid-webserver:latest
