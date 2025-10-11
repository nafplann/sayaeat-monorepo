FROM fholzer/nginx-brotli:v1.26.2
COPY public /var/www/public
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/app.staging.conf /etc/nginx/conf.d/default.conf
WORKDIR /var/www/
