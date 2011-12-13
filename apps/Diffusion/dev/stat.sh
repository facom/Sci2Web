#!/bin/bash

num=$(tail -n 1 status.dat | awk '{print $1}')
if [ -z $num ];then perc=0
else
    tot=$(tail -n 1 status.dat | awk '{print $2}')
    perc=$(perl -e "print ((1.0*$num)/$tot)")
fi
echo $perc
