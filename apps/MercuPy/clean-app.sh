#!/bin/bash
#CLEAN APP FOR COMMIT PURPOSES
for version in $(ls -d */sci2web)
do
    version=$(dirname $version)
    echo "Cleaning util..."
    make -C $version clean
    make -C $version/util clean
    echo "Removing big files..."
    rm -rf $version/util/cspice
    echo "Removing tarballs..."
    rm -rf $version/sci2web/*.tar.gz
done
rm -rf .installed
