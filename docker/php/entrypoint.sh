#!/bin/sh

# Clone FluxBB if input directory is empty or missing
if [ ! -f /var/www/html/input/index.php ]; then
    echo "Cloning FluxBB from GitHub..."
    rm -rf /var/www/html/input/*
    git clone --depth 1 https://github.com/fluxbb/fluxbb.git /tmp/fluxbb
    mv /tmp/fluxbb/* /var/www/html/input/
    rm -rf /tmp/fluxbb

    echo "FluxBB cloned successfully. Visit http://localhost:8080/input/ to install."
fi

# Set permissions for writable directories
chmod 777 /var/www/html/input/cache
chmod 777 /var/www/html/input/img/avatars

# Start PHP-FPM
exec php-fpm
