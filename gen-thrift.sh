#!/bin/bash
function travFolder(){
#    flist=`ls $1`
#    cd $1
#    for f in $flist
#    do
#        if test -d $f
#        then
#            #echo "dir:$f"
#            travFolder $f
#        else
#            echo "file:$f"
##            thrift --gen php:server $f
#        fi
#    done
    echo 1
}
cd rpc
dir=.
travFolder $dir

