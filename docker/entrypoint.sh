#!/bin/sh
# Arranque del contenedor de la app:
#   1. Espera a que MySQL acepte conexiones.
#   2. Aplica las migraciones pendientes (scripts/migrate.php).
#   3. Levanta Apache.
set -e

if [ "${RUN_MIGRATIONS:-1}" = "1" ]; then
  echo "Esperando a la base de datos en ${DB_HOST}:${DB_PORT}..."
  intentos=0
  until php -r 'new PDO("mysql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT").";dbname=".getenv("DB_NAME"), getenv("DB_USER"), getenv("DB_PASS"));' 2>/dev/null; do
    intentos=$((intentos + 1))
    if [ "$intentos" -ge 30 ]; then
      echo "La base no respondió después de 60 segundos." >&2
      exit 1
    fi
    sleep 2
  done
  php scripts/migrate.php
fi

exec "$@"
