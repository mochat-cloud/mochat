# @mochat
FROM mochat/mochat:v1

ARG APP_ENV=production
ARG APP_NAME=mochat
ENV APP_ENV=${APP_ENV} \
    APP_NAME=${APP_NAME}

# 项目配置
WORKDIR /opt/www
COPY ./docker-entrypoint.sh /tmp/docker-entrypoint.sh
COPY . /opt/www
RUN chmod +x /tmp/docker-entrypoint.sh && \
    mkdir -p -m 777 /var/www-log && \
    cd /opt/www && \
    composer install --no-dev -o && \
    php /opt/www/bin/hyperf.php

ENTRYPOINT ["/tmp/docker-entrypoint.sh"]
