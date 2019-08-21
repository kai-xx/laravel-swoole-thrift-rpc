#!/bin/sh
travel() {
   flist=`ls $1`
   for f in $flist
   do
   if test -d $f;then
         travel $f
   else
       if [ "${f##*.}"x="thrift"x ];then
        thrift --gen php:server $1/$f
       fi
   fi
   done
}
cd rpc
dir=.
travel $dir
