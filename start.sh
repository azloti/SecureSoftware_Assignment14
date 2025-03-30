#!/bin/bash

# Start PHP server in the background
/Users/nichita/homebrew/bin/php -S localhost:8000 -t . server.php &
PHP_PID=$!

# Start stunnel in the background
/Users/nichita/homebrew/opt/stunnel/bin/stunnel stunnel.conf &
STUNNEL_PID=$!

# Wait for user input
trap "kill $PHP_PID $STUNNEL_PID" EXIT
wait 