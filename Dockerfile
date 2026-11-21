FROM dunglas/frankenphp:1-php8.4-alpine
WORKDIR /app
RUN install-php-extensions pdo_mysql mbstring \
 && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
 && printf '%s\n' 'expose_php=Off' 'display_errors=Off' 'log_errors=On' 'session.cookie_httponly=1' > "$PHP_INI_DIR/conf.d/zz-sim-imam.ini"
COPY Caddyfile /etc/caddy/Caddyfile
COPY . /app
ENV PORT=80
ENV SESSION_STORE=database
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
