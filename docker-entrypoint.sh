#!/bin/sh
set -e

# Copy .env if it doesn't exist (only if volume is mounted and host has no .env)
if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

# Generate key if APP_KEY is empty
if ! grep -q "APP_KEY=base64" .env && [ -z "$APP_KEY" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Wait for database connection and run migrate/seed if empty
echo "Waiting for database connection and checking tables..."
max_tries=30
count=0
while [ $count -lt $max_tries ]; do
    # Run inline PHP to test connection and get table count
    RESULT=$(php -r "
        try {
            require 'vendor/autoload.php';
            \$app = require 'bootstrap/app.php';
            \$kernel = \$app->make('Illuminate\Contracts\Console\Kernel');
            \$kernel->bootstrap();
            \DB::connection()->getPdo();
            echo count(\Schema::getTables());
        } catch (\Throwable \$e) {
            echo 'CONN_ERROR';
        }
    " 2>/dev/null)

    if [ "$RESULT" = "CONN_ERROR" ]; then
        echo "Database connection not ready yet (try $((count+1))/$max_tries), waiting..."
        sleep 2
        count=$((count+1))
    else
        if [ "$RESULT" = "0" ]; then
            echo "Database is connected but empty. Running migrations and seeders..."
            php artisan migrate --seed --force
        else
            echo "Database is connected and already has $RESULT tables. Skipping migrations/seeding."
        fi
        break
    fi
done

# Set full read/write permissions for storage, public, and bootstrap/cache directories
echo "Setting permissions for storage, public, bootstrap/cache, and asterisk config..."
chmod -R 777 storage public bootstrap/cache || true
chmod -R 777 /etc/asterisk || true

# Automate Mosquitto configuration permissions on host via mapped volume
if [ -d "docker/mosquitto" ]; then
    echo "Adjusting permissions for Mosquitto directory..."
    chmod -R 777 docker/mosquitto || true
fi

# Execute the main container command (e.g., apache2-foreground)
exec "$@"
