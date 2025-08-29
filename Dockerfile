FROM php:8.2-cli
WORKDIR /app
COPY . .
# Usa el puerto que Render pone en $PORT (fallback 10000 en local)
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-10000} -t ."]
