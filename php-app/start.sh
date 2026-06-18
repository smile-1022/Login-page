#!/bin/bash
set -e

PHP_PORT=${PORT:-5001}
MYSQL_DATA_DIR="/tmp/guvi_mysql"
MONGO_DATA_DIR="/tmp/guvi_mongo"
REDIS_DATA_DIR="/tmp/guvi_redis"

echo "==> Setting up data directories..."
mkdir -p "$MYSQL_DATA_DIR" "$MONGO_DATA_DIR" "$REDIS_DATA_DIR"

echo "==> Starting Redis..."
if ! pgrep -x redis-server > /dev/null; then
    redis-server --port 6379 --daemonize yes --logfile /tmp/redis.log --dir "$REDIS_DATA_DIR"
    sleep 1
fi
echo "    Redis OK"

echo "==> Starting MongoDB..."
if ! pgrep -x mongod > /dev/null; then
    mongod --dbpath "$MONGO_DATA_DIR" --port 27017 --fork --logpath /tmp/mongod.log --bind_ip 127.0.0.1
    sleep 2
fi
echo "    MongoDB OK"

echo "==> Starting MariaDB..."
if ! pgrep -x mysqld > /dev/null && ! pgrep -x mariadbd > /dev/null; then
    if [ ! -d "$MYSQL_DATA_DIR/mysql" ]; then
        echo "    Initializing MariaDB data directory..."
        mysql_install_db --datadir="$MYSQL_DATA_DIR" --auth-root-authentication-method=normal > /tmp/mysql_init.log 2>&1
        echo "    MariaDB initialized."
    fi
    mysqld_safe --datadir="$MYSQL_DATA_DIR" --port=3306 --socket=/tmp/mysql.sock --log-error=/tmp/mysql.log --bind-address=127.0.0.1 &
    echo "    Waiting for MariaDB to start..."
    for i in $(seq 1 20); do
        if mysqladmin ping --host=127.0.0.1 --port=3306 --user=root --silent 2>/dev/null; then
            echo "    MariaDB is up!"
            break
        fi
        sleep 1
    done
fi
echo "    MariaDB OK"

echo "==> Creating database and tables..."
mysql --host=127.0.0.1 --port=3306 --user=root 2>/dev/null <<'ENDSQL'
CREATE DATABASE IF NOT EXISTS guvi_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE guvi_app;
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name  VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ENDSQL
echo "    Database & tables ready."

echo ""
echo "==> All services started. Launching PHP server on port $PHP_PORT..."
echo "    Visit: http://localhost:$PHP_PORT"
echo ""

cd /home/runner/workspace/php-app
php -S 0.0.0.0:$PHP_PORT -t /home/runner/workspace/php-app
