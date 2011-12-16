#!/bin/bash
#######################################################################
#LOAD COMMON ROUTINES AND CONFIGURATION
#######################################################################
. sci2web/bin/sci2web.sm
. sci2web/runpipeline.conf

while [ $1 ]
do
#############################################################
#GET THE INFORMATION ABOUT THE ACTION FROM THE RUN PIPELINE
#############################################################
action=$1
ACTION=$(echo $action | tr "a-z" "A-Z")
SCR="${ACTION}SCR";CMD="${ACTION}CMD"

case $action in
#############################################################
#REMOVE ALL THE SCI2WEB INFORMATION REMAINING IN THE DIR
#############################################################
"cleanall")
echo "Executing action $action:"
checkSig "clean"
bash sci2web/bin/sci2web.sh clean
rm -rf *.sig *.oxt fontList.cache
echo "Cleaned."
;;
#############################################################
#TESTING THE PIPELINE
#############################################################
"test")
echo "Executing action $action:"
echo "Testing the basic life-cycle steps..."
bash sci2web/bin/sci2web.sh clean compile pre submit
echo -e "Basic actions succeed..."
echo "Testing advanced actions..."
sleep 1
echo -e "\tPausing..."
bash sci2web/bin/sci2web.sh pause 
sleep 1
echo -e "\tResuming..."
bash sci2web/bin/sci2web.sh resume
sleep 1
echo -e "\tStopping..."
bash sci2web/bin/sci2web.sh stop
echo -e "Advanced options succeed..."
echo "All test ran.  Please test manually the 'post' action"
;;
#############################################################
#clean|compile|pre
#############################################################
"clean"|"compile"|"pre")
echo "Executing action $action:"
checkSig $action
if notBlank ${!SCR};then . ${!SCR};else ${!CMD};fi
setSig $action
;;
#############################################################
#post
#############################################################
"post")
echo "Executing action $action:"
{
    checkSig $action
    if notBlank ${!SCR};then . ${!SCR};else ${!CMD};fi
    #STORE RESULTS IN RESULTS DATABASE
    perl $BIN/sci2web.pl saveresult --rundir .
    setSig $action
} &> post.oxt
cat post.oxt
;;
#############################################################
#pause|stop
#############################################################
"pause"|"stop")
echo "Executing action $action:"
checkSig $action
if notBlank ${!SCR};then . ${!SCR}
else
    if notBlank ${!CMD};then
	${!CMD}
	setSig $action
    else
	bash sci2web/bin/sci2web.sh kill
    fi
fi
;;
#############################################################
#SUBMIT
#############################################################
"submit"):
echo "Executing action $action:"
checkSig $action
echo -n "#!/bin/bash
#&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
#THESE LINES CAN BE CHANGED ACCORDING TO YOUR QUEUE SYSTEM 
#&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
#$ Options
#&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
#&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
. sci2web/bin/sci2web.sh
. sci2web/runpipeline.conf
checkSig 'run'
setSig 'start'
#############################################################
(" > /tmp/apprun.$$
if notBlank ${!SCR}
then
    echo -n bash ${!SCR} >> /tmp/apprun.$$
else
    echo -n ${!CMD} >> /tmp/apprun.$$
fi
echo " >> stdout.oxt 2>> stderr.oxt) & 
#############################################################
PID=\$!
echo \$PID > pid.oxt
wait 

if [ -e 'end.sig' ];then setSig 'end'
else
    if [ ! -e 'pause.sig' -a ! -e 'stop.sig' -a ! -e 'kill.sig' ];then setSig 'fail';fi
fi
bash sci2web/bin/sci2web.sh post
" >> /tmp/apprun.$$
cp -rf /tmp/apprun.$$ run.sh

#&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
#THESE LINES CAN BE CHANGED ACCORDING TO YOUR QUEUE SYSTEM 
#&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
if ! at now < /tmp/apprun.$$ &> /tmp/jobinfo.$$;then
    echo "Error submitting your job:"
    cat /tmp/jobinfo.$$
    rm -rf /tmp/*.$$
    exit 1
fi
cat /tmp/jobinfo.$$
jobid=$(tail -n 1 /tmp/jobinfo.$$ | cut -f 2 -d ' ')
echo $jobid > jobid.oxt
#&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
#&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
rm -rf /tmp/*.$$
setSig $action
;;

#############################################################
#RESUME
#############################################################
"resume"):
echo "Executing action $action:"
checkSig $action
setSig $action
if notBlank ${!SCR};then . ${!SCR};else ${!CMD};fi
bash sci2web/bin/sci2web.sh submit
;;
#############################################################
#CHECK
#############################################################
"check")
checkSig $action
if [ -f pid.oxt ];then
    if ps $(cat pid.oxt) &> /dev/null
    then
	echo "--run--"
    else 
	if [ -e "end.sig" ]
	then
	    echo "--end--"
	else 
	    if [ -e "fail.sig" ]
	    then
		echo "--fail--"
	    else
		echo "No run status"
	    fi
	fi
    fi
else
    echo "Waiting"
fi
	
;;
#############################################################
#TESTING THE LIFE CYCLE
#############################################################
"kill")
echo "Executing action $action:"
checkSig $action
setSig $action
echo "Execution action $action:"
atrm $(cat jobid.oxt)
kill -9 $(cat pid.oxt)
;;
#############################################################
#STATUS
#############################################################
"status")
if notBlank ${!SCR};then . ${!SCR};else ${!CMD};fi
;;
*)
echo "Action $action not recognized"
exit 1
;;
#############################################################
#FINALIZE
#############################################################
esac
shift
done
