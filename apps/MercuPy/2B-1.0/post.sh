#!/bin/bash
echo "Plotting results..."
export MPLCONFIGDIR="."
make plot
echo "Saving results variables..."
bash results.sh
