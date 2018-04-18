#!/bin/bash

# Location of fixity repository
INSTALL_PREFIX="/opt/src/fixity"

# Check if target exists before symlinking
if [ ! -d /var/www/smarty ]; then
	ln -s "$INSTALL_PREFIX/smarty/" "/var/www/smarty"
fi

if [ ! -d /var/www/html ]; then 
	ln -s "$INSTALL_PREFIX/www" "/var/www/html"
fi

# Set permissions in repo directory
chown apache:apache "$INSTALL_PREFIX/smarty/templates_c"
chown apache:apache "$INSTALL_PREFIX/smarty/cache"
chmod 775 "$INSTALL_PREFIX/smarty/templates_c"
chmod 775 "$INSTALL_PREFIX/smarty/cache"

