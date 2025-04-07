#!/bin/bash

mysqldump --column-statistics=0 "$4" -u"$3" -p"$1" -h"$2" --skip-add-drop-table --routines --triggers --events > temp/database-mysql.sql