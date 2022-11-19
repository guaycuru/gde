#!/bin/sh

git pull
php bin/doctrine orm:clear-cache:metadata
php bin/doctrine orm:clear-cache:query
php bin/doctrine orm:generate-proxies
php bin/doctrine orm:schema-tool:update --dump-sql --force
