#############################################################
#SAVING RESULTS
#############################################################
num=0
i=0
for file in path*.dat
do
    lines=$(wc -l $file | cut -f 1 -d ' ')
    ((num=num+lines))
    ((i++))
done
meandisp=$(perl -e "print $num/$i")
echo "MeanDispersions = $meandisp" > results.conf
#############################################################
#PLOTTING
#############################################################
gnuplot plot-out.pl
#############################################################
#PYTHON PLOTTING
#############################################################
export MPLCONFIGDIR="."
python sci2web/bin/sci2web-plot plot.ps2w
