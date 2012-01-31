#!/bin/bash
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
#          _ ___               _     
#         (_)__ \             | |    
# ___  ___ _   ) |_      _____| |__  
#/ __|/ __| | / /\ \ /\ / / _ \ '_ \ 
#\__ \ (__| |/ /_ \ V  V /  __/ |_) |
#|___/\___|_|____| \_/\_/ \___|_.__/ 
#
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
# UTILITY SCRIPT
#%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
#######################################################################
#LOAD COMMON ROUTINES AND CONFIGURATION
#######################################################################
. sci2web/bin/sci2web.sm
. sci2web/runpipeline.conf
. sci2web/bin/queues.sm

while [ $1 ]
do
#############################################################
#GET THE INFORMATION ABOUT THE ACTION FROM THE RUN PIPELINE
#############################################################
#GET ACTION
action=$1

#TRANSLATE ACTION TO UPPERCASE
ACTION=$(echo $action | tr "a-z" "A-Z")

#SCRIPT AND COMMAND
SCR="${ACTION}SCR";CMD="${ACTION}CMD"

case $action in
#############################################################
#REMOVE ALL THE SCI2WEB INFORMATION REMAINING IN THE DIR
#############################################################
    "cleanall")
	echo "Executing action $action:"
	checkSig "clean"
	rm -rf *.sig *.oxt fontList.cache
	bash sci2web/bin/sci2web.sh clean
	echo "Cleaned."
	;;
#############################################################
#clean|compile|pre
#############################################################
    "clean"|"compile"|"pre")
	echo "Executing action $action:"
	checkSig $action
	if notBlank ${!SCR}
	then 
	    . ${!SCR}
	    ecode=$?
	    if [ $ecode -gt 0 ];then 
		setSig "error"
		exit $ecode
	    fi
	else 
	    ${!CMD}
	    ecode=$?
	    if [ $ecode -gt 0 ];then 
		setSig "error"
		exit $ecode
	    fi
	fi
	setSig $action
	;;
#############################################################
#post
#############################################################
    "post")
	echo $(NOW) > posting.sig
	echo "Executing action $action:"
	{
	    checkSig $action
	    if notBlank ${!SCR}
	    then 
		. ${!SCR}
		ecode=$?
		if [ $ecode -gt 0 ];then 
		    setSig "error"
		    exit $ecode
		fi
	    else 
		${!CMD}
		ecode=$?
		if [ $ecode -gt 0 ];then 
		    setSig "error"
		    exit $ecode
		fi
	    fi
	    #========================================
            #STORE RESULTS IN RESULTS DATABASE
	    #========================================
	    perl $BIN/sci2web.pl saveresult --rundir .
	    #========================================
            #SAVE INFORMATION ABOUT JOB
	    #========================================
	    bash sci2web/bin/sci2web.sh fullinfo > jobinfo.oxt
	    setSig $action
	} &> post.oxt
	cat post.oxt
	;;
#############################################################
#pause|stop
#############################################################
    "pause"|"stop")
	echo "Executing action $action:"
	if notBlank ${!SCR};then 
	    if [ ${!SCR} = "kill" ];then
		killJob
		setSig $action
	    else
		. ${!SCR}
		ecode=$?
		if [ $ecode -gt 0 ];then 
		    setSig "error"
		    exit $ecode
		fi
	    fi
	else
	    if notBlank ${!CMD};then
		checkSig $action
		${!CMD}
		ecode=$?
		if [ $ecode -gt 0 ];then 
		    setSig "error"
		    exit $ecode
		fi
		setSig $action
	    else
		#IF PAUSE OR STOP CMD IS BLANK EXECUTE KILL
		echo "$action not implemented.  Switching to kill..."
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
	if notBlank ${!SCR}
	then
	    cmd=$(echo -n "bash ";echo -n ${!SCR})
	else
	    cmd=${!CMD}
	fi
	
	#########################################
	#SCHEDULER INDEPENDENT SUBMISSION SCRIPT
	#########################################
	echo -n "#BASH SCRIPT IS ASSUMED
#############################################################
#PREPARE SCRIPT
#############################################################
. sci2web/bin/sci2web.sh
. sci2web/runpipeline.conf
checkSig 'run'
setSig 'start'

#############################################################
#RUN COMMAND
#############################################################
rm -rf stdout.oxt
($cmd >> stdout.oxt 2>> stderr.oxt) & 

#############################################################
#GET PROCESS ID
#############################################################
PID=\$!
echo \$PID > pid.oxt
wait 

#############################################################
#FINISH PROCESS
#############################################################
if [ -e 'end.sig' ];then setSig 'end'
else
    if [ ! -e 'pause.sig' -a ! -e 'stop.sig' -a ! -e 'kill.sig' ]
    then setSig 'fail'
    fi
fi
#############################################################
#POST EXECUTION ACTIONS
#############################################################
bash sci2web/bin/sci2web.sh post
" > run.sh
	
	setSig $action
	sleep 1
	submitJob
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
#KILLING JOB
#############################################################
    "kill")
	echo "Executing action $action:"
	checkSig $action
	setSig $action
	echo "Execution action $action:"
	killJob
	;;
#############################################################
#STATUS
#############################################################
    "status")
	if notBlank ${!SCR};then . ${!SCR};else ${!CMD};fi
	;;
#############################################################
#CHECK
#############################################################
    "check")
	checkSig $action
	if [ -f pid.oxt ];then
	    if checkJob &> /dev/null
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
#QSTATUS
#############################################################
    "qstatus")
	checkSig $action
	statusJob
	;;
#############################################################
#FULL INFORMATION ABOUT JOB
#############################################################
    "fullinfo")
	checkSig $action
	fullinfoJob
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
#IN THE CASE OF AN UNRECOGNIZED ACTION
#############################################################
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

chmod -R g+rw * &> /dev/null
echo "Done."
