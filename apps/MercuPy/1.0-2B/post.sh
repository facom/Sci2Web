#!/bin/bash
export MPLCONFIGDIR="."

echo "Plotting results..."
make plot

echo "Saving results variables..."
bash results.sh
