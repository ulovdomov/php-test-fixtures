#!/bin/bash

mysql "$4" -u"$3" -p"$1" -h"$2" < temp/database-dummy.sql