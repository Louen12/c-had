#!/bin/sh
set -e

if [ ! -f "node_modules/.package-lock.json" ]; then
    npm install
fi

exec "$@"
