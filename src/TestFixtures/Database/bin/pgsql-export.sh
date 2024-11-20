#!/bin/bash

PGPASSWORD="$1" pg_dump --no-owner -h "$2" -U "$3" -d "$4" > ./../temp/database-dummy.sql && echo "-- Dump completed" >> ./../temp/database-dummy.sql