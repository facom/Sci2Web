#!/bin/bash
export MPLCONFIGDIR="."

echo "Generating data files..."
bin/mercupy-plain

if [ 1 -gt 0 ];then
    echo "Changing to referenche frame..."
    bin/mercupy-ref2ref BODY2 BODY2-INERTIAL Rotating
fi

echo "Plotting results..."
bin/mercupy-plot 

echo "Saving results variables..."
bash results.sh
