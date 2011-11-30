#!/bin/bash
#######################################################################
#LOAD COMMON ROUTINES AND CONFIGURATION
#######################################################################
. sci2web/bin/s2w-common
. sci2web/s2w-config

while [ $1 ]
do
#############################################################
#GET THE INFORMATION ABOUT THE ACTION
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
bash sci2web/bin/s2w-action.sh clean
rm -rf *.sig *.oxt fontList.cache
echo "Cleaned."
;;
#############################################################
#TESTING THE LIFE CYCLE
#############################################################
"test")
echo "Executing action $action:"
echo "Testing the basic life-cycle steps..."
bash sci2web/bin/s2w-action.sh clean compile pre submit
echo -e "Basic actions succeed..."
echo "Testing advanced actions..."
sleep 1
echo -e "\tPausing..."
bash sci2web/bin/s2w-action.sh pause 
sleep 1
echo -e "\tResuming..."
bash sci2web/bin/s2w-action.sh resume
sleep 1
echo -e "\tStopping..."
bash sci2web/bin/s2w-action.sh stop
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
    perl $BIN/sci2web-dbresult .
    setSig $action
} &> post.oxt
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
    else
	bash sci2web/bin/s2w-action.sh kill
    fi
fi
;;
#############################################################
#SUBMIT
#############################################################
"submit"):
echo "Executing action $action:"
checkSig $action
setSig $action
echo -n "
. sci2web/bin/s2w-common
. sci2web/s2w-config
checkSig 'run'
setSig 'start'
#############################################################
(" > /tmp/s2w-run.$$
if notBlank ${!SCR}
then
    echo -n bash ${!SCR} >> /tmp/s2w-run.$$
else
    echo -n ${!CMD} >> /tmp/s2w-run.$$
fi
echo " >> stdout.oxt 2>> stderr.oxt) & 
#############################################################
PID=\$!
echo \$PID > pid.oxt
wait 

if [ -e 'end.sig' ];then setSig 'end'
else
    if [ ! -e 'pause.sig' -a ! -e 'stop.sig' ];then setSig 'fail';fi
fi
bash sci2web/bin/s2w-action.sh post
" >> /tmp/s2w-run.$$
subtxt=$(at now < /tmp/s2w-run.$$ &> /tmp/jobinfo.$$)
jobid=$(tail -n 1 /tmp/jobinfo.$$ | cut -f 2 -d ' ')
cat /tmp/jobinfo.$$
echo $jobid > jobid.oxt
rm -rf /tmp/*.$$
;;

#############################################################
#RESUME
#############################################################
"resume"):
echo "Executing action $action:"
checkSig $action
setSig $action
if notBlank ${!SCR};then . ${!SCR};else ${!CMD};fi
bash sci2web/bin/s2w-action.sh submit
;;
#############################################################
#CHECK
#############################################################
"check")
checkSig $action
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
	fi
    fi
fi
;;
#############################################################
#TESTING THE LIFE CYCLE
#############################################################
"kill")
echo "Executing action $action:"
checkSig $action
echo "Execution action $action:"
atrm $(cat jobid.oxt)
setSig $action
;;
#############################################################
#STATUS
#############################################################
"status")
if notBlank ${!SCR};then . ${!SCR};else ${!CMD};fi
;;

#############################################################
#FINALIZE
#############################################################
esac
shift
done
