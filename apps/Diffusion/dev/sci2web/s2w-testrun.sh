echo "Running..."
i=0
if [ -e "pause.oxt" ];then i=$(cat pause.oxt);fi
while [ $i -lt 100 ]
do
    echo "Step $i..."
    if [ -e "pause.sig" ];then
	echo $i > pause.oxt
	exit 0
    fi
    if [ -e "stop.sig" ];then
	exit 1
    fi
    sleep 1
    ((i++))
done
date +%s.%N > end.sig
