#!/bin/bash

PGPASSWORD="$1" pg_dump --no-owner --format=plain --schema=public -h "$2" -U "$3" -d "$4" > temp/database-pgsql.sql && echo "-- Dump completed" >> temp/database-pgsql.sql