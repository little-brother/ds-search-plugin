FROM alpine:3.15.4

LABEL description = "GET-proxy server for search plugins of Synology Download Station"

ENV PROXY_IP=192.168.0.11
ENV PROXY_PORT=9050
ENV PROXY_TYPE=socks5
ENV PROXY_ATTEMPTS=3

RUN apk -U upgrade && \
    apk -v add --no-cache php8 php8-curl && \
    rm -rf /var/cache/apk/* && \
    mkdir -p /var/www 

COPY index.php /var/www/
WORKDIR "/var/www/"

CMD php8 -S 0.0.0.0:80

EXPOSE 80/tcp