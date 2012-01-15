#!/bin/bash
#CLEAN APP FOR COMMIT PURPOSES
for version in 1.0-2B 1.0-3B
do
    echo "Cleaning util..."
    make -C $version clean
    make -C $version/util clean
    echo "Removing big files..."
    rm -rf $version/util/cspice
    echo "Removing tarballs..."
    rm -rf $version/sci2web/*.tar.gz
done
rm -rf .installed
