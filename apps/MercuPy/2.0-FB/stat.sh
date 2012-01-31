#!/bin/bash
. sci2web/bin/sci2web.sm

#GET THE TOTAL SIMULATION TIME 
time=$(cat stdout.oxt | grep Time: | tail -n 1 | awk -F':' '{print $2}' | awk '{print $1}')
phase=$(cut -f 1 -d ' ' output/phase)

#COMPUTE THE FRACTION OF THE TOTAL TIME COMPLETED
calc "($time*$phase)/(100.0*1)"
