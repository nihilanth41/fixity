#!/bin/bash

# Params: 
# $1 = /path/to/fixi.db
# $2 = UUID of run 

/usr/bin/sqlite3 $1 <<!
.headers on 
.mode csv
select relpath,size,mtime,sha256,attr from audit where uuid="$2";
!
