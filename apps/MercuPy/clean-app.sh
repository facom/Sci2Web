#!/bin/bash
#CLEAN APP FOR COMMIT PURPOSES
for version in 2B-dev 3B-dev 2B-1.0 3B-1.0
do
    echo "Cleaning util..."
    make -C $version clean
    echo "Removing big files..."
    rm -rf $version/util/cspice
    echo "Removing tarballs..."
    rm -rf $version/sci2web/*.tar.gz
done
rm -rf .installed