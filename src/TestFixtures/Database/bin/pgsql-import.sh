#!/bin/bash

PGPASSWORD="$1" psql -h "$2" -U "$3" -d "$4" < temp/database-pgsql.sql