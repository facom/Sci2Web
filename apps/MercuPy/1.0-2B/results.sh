#!/bin/bash
fracen=$(grep "Fractional energy" info.out | grep "integrator" | tail -n 1 | cut -f 2 -d ':')
fracmom=$(grep "Fractional angular" info.out | tail -n 1 | cut -f 2 -d ':')
{
    echo "ChangeEnergy=" $fracen
    echo "ChangeMomentum=" $fracmom
} > sci2web/results.info
