/*
  ////////////////////////////////////////////////////////////////////////////////
  BASIC
  ////////////////////////////////////////////////////////////////////////////////
 */

/*
  OPEN WINDOWS
 */
function Open(url,name,options)
{
    window.open(url,name,options);
}

/*
  CLOSE WINDOWS
 */
function Close()
{
    window.close();
}

/*
  ////////////////////////////////////////////////////////////////////////////////
  FORMS
  ////////////////////////////////////////////////////////////////////////////////
 */
function clickRow(row,color)
{
    if(!row.checked){
	row.parentNode.parentNode.style.backgroundColor='white';
    }else{
	row.parentNode.parentNode.style.backgroundColor=color;
    }
}

function enableElement(check)
{
    elname=$(check).attr("name").split("_")[0];
    element_enabled=$('[name="'+elname+'"]');
    if(!check.checked){
	element_enabled.attr("disabled",true);
    }else{
	element_enabled.attr("disabled",false);
    }
}

function popOutHidden(field)
{
    var sname=$(field).attr("name")+'_Submit';
    var hfield=$('input[name="'+sname+'"]');
    if(hfield.length==0){
	var newfield=$(field).clone();
	$(newfield).
	    attr("name",sname).
	    attr("type","hidden");
	$(field).after(newfield);
    }else{
	//alert("Element "+$(hfield).attr("name")+" Changed to "+$(field).attr("value"));
	hfield.attr("value",$(field).attr("value"));
    }
}

/*
  ////////////////////////////////////////////////////////////////////////////////
  DEBUGGING
  ////////////////////////////////////////////////////////////////////////////////
 */

/*
  CHANGE DEBUGGING LINK
 */
function fileDebug(select,fileopts,winprops)
{
  option=select.options[select.selectedIndex];
  value=option.value;
  document.getElementById("server").href="JavaScript:Open('"+fileopts+"&File=phpdb-"+value+"','File','"+winprops+"')";
  document.getElementById("stdout").href="JavaScript:Open('"+fileopts+"&File=phpout-"+value+"','File','"+winprops+"')";
  document.getElementById("stderr").href="JavaScript:Open('"+fileopts+"&File=phperr-"+value+"','File','"+winprops+"')";
  document.getElementById("mysql").href="JavaScript:Open('"+fileopts+"&File=phpsql-"+value+"','File','"+winprops+"')";
}

/*
  CHANGE NEW LINK
 */
function configureNew(select,fileopts,winprops)
{
  option=select.options[select.selectedIndex];
  value=option.value;
  document.getElementById("new").href="JavaScript:Open('"+fileopts+"&Template="+value+"','Configure','"+winprops+"')";
}

/*
  ////////////////////////////////////////////////////////////////////////////////
  AJAX
  ////////////////////////////////////////////////////////////////////////////////
 */

/*
  GENERAL LOAD CONTENT
 */
function loadContent(script,elementid,
		     onsuccessfunc,onwaitfunc,onerrorfunc,
		     timeout,async)
{
  var x=new XMLHttpRequest();
  var element=document.getElementById(elementid);
  x.onreadystatechange=function(){
    rtext=x.responseText;
    if(x.readyState==4){
      if(x.status==200){
	onsuccessfunc(element,rtext);
	x.abort();
      }else{
	onerrorfunc(element,rtext);
	x.abort();
      }
    }else{
      onwaitfunc(element,rtext);
    }
  }
  x.open("POST",script,async);
  x.send();
  if(timeout>0){
      callback="loadContent('"+script+"','"+elementid+"',"+onsuccessfunc.toString()+","+onwaitfunc.toString()+","+onerrorfunc.toString()+","+timeout.toString()+","+async.toString()+")";
      setTimeout(callback,timeout);
  }
}

/*
  WRITE FILE
 */
function writeFile(file,msg,writescr)
{
    script=writescr+"?File="+file+"&Content="+msg;
    var x=new XMLHttpRequest();
    x.onreadystatechange=function(){
	if(x.readyState==4){
	    if(x.status==200){
		//alert(x.responseText);
	    }
	}
    }
    x.open("GET",script,true);
    x.send();
}

/*
  TOGGLE ELEMENT
 */
function toggleElement(elementid)
{
  $("#"+elementid).toggle('fast',null);
}

function showElement(elementid)
{
  $("#"+elementid).show('fast',null);
}

/*
  CHANGE TO EDITOR
 */
function toggleToEdition(view,edit,link,color,ckfinder)
{
    $('#'+view).css("display","none");
    CKEDITOR.replace(edit,
    {
    uiColor:color,
    filebrowserBrowseUrl : ckfinder+'/ckfinder.html',
    filebrowserImageBrowseUrl:ckfinder+'/ckfinder.html?Type=Images',
    filebrowserFlashBrowseUrl:ckfinder+'/ckfinder.html?Type=Flash',
    filebrowserUploadUrl:ckfinder+'/core/connector/php/connector.php?command=QuickUpload&type=Files',
    filebrowserImageUploadUrl:ckfinder+'/core/connector/php/connector.php?command=QuickUpload&type=Images',
    filebrowserFlashUploadUrl:ckfinder+'/core/connector/php/connector.php?command=QuickUpload&type=Flash',
    height:'400'
    });
    $('#'+link).html("<input type='submit' name='SaveContent' value='Save'><input type='submit' name='Cancel' value='Cancel'>");
}

/*
  CHANGE TO EDITOR
 */
function toggleHidden(object,hclass,symbols){
    hclassjq="."+hclass;
    if($(object).attr("action").search(/More/i)>=0){
	$(object).attr("action","Less");
	$(object).html(symbols["Less"]);
	$(hclassjq).slideDown('fast',null);
    }else{
	$(object).attr("action","More");
	$(object).html(symbols["More"]);
	$(hclassjq).slideUp('fast',null);
    }
}

/*
  SUBMIT FORM 
 */
function submitForm(formid,script,
		    elementid,onsuccessfunc,onwaitfunc,onerrorfunc)
{
  var x=new XMLHttpRequest();
  var element=document.getElementById(elementid);
  var form=document.getElementById(formid);
  
  //GET SUBMIT FORM ELEMENTS
  i=0;
  qstring="";
  while(formel=form.elements[i]){
      esname=$(formel).attr("name");
      if(esname.search("_Submit")>=0){
	  ename=esname.split("_")[0];
	  qstring+=ename+"="+$(formel).attr("value")+"&";
      }
      i++;
  }
  script=script+"&"+qstring;

  x.onreadystatechange=function(){
    rtext=x.responseText;
    if(x.readyState==4){
      if(x.status==200){
	onsuccessfunc(element,rtext);
      }else{
	onerrorfunc(element,rtext);
      }
    }else{
      onwaitfunc(element,rtext);
    }
  }

  x.open("GET",script,true);
  x.send();
}

function notDiv(notid,text)
{
    $('#'+notid).html(text);
    $('#'+notid).fadeIn(1000,null);
    setTimeout("$('#"+notid+"').fadeOut(3000,null)",2000);
}

function explainThis(object)
{
  name=$(object).attr("name");
  explanation=$(object).attr("explanation");
  boxclass="explanation";
  boxref='.'+boxclass;

  boxwidth=strLength(explanation);
  boxheight=15;
  elheight=$(object).height();
  elwidth=$(object).width();
  if(elheight>15) elheight=15;
  boxtop=-elheight;
  boxleft=elwidth+5;
  
  unheight="px";
  unwidth="px";
  $.openDOMWindow({
        height:boxheight+unheight,
	width:boxwidth+unwidth,
	positionType:'anchored', 
	anchoredClassName:boxclass, 
	anchoredSelector:object,
	positionTop:boxtop,
	positionLeft:boxleft,
	borderSize:1,
	loader:0,
	windowBGColor:"#F3B06C",
	windowPadding:2
	}
    );

  $(boxref).css("font-size","12px");
  $(boxref).html(explanation);

  $(this).
    mouseout(function(){$.closeDOMWindow({anchoredClassName:boxclass});});
}

function check()
{
  alert('Hola');
}

function strLength(string)
{
  var ruler=$("#RULER");
  ruler.html(string);
  return ruler.width();
}

function updateSlider(varid)
{
    input=$("#"+varid);
    value=input.attr("value");
    valueSlider(varid,0,0,value);
}

function valueSlider(varid,newpos,delsgn,value)
{
    ////////////////////////////////////////////////////
    //COMPONENTS
    ////////////////////////////////////////////////////
    cont=$("#"+varid+"_container");
    button=$("#"+varid+"_button");
    input=$("#"+varid);
    bar=$("#"+varid+"_bar");

    ////////////////////////////////////////////////////
    //PROPERTIES OF VARIABLE
    ////////////////////////////////////////////////////
    val=parseFloat(input.attr("value"));
    pos=parseFloat(bar.css("left"));
    max=parseFloat(input.attr("max"));
    min=parseFloat(input.attr("min"));
    delta=parseFloat(input.attr("delta"));

    ////////////////////////////////////////////////////
    //GEOMETRICAL PROPERTIES OF SLIDER
    ////////////////////////////////////////////////////
    width=parseFloat(cont.css("width"));
    wbut=parseFloat(button.css("width"));
    wbar=parseFloat(bar.css("width"));
    minpos=wbut;
    maxpos=width-wbut-wbar;
    pos2val=(max-min)/(maxpos-minpos);

    ////////////////////////////////////////////////////
    //NEW VALUE
    ////////////////////////////////////////////////////
    if(newpos>0){
	newval=min+pos2val*(newpos-wbut);
    }else{
	if(Math.abs(delsgn)>0){
	    delval=delsgn*delta
	}else{
	    delval=parseFloat(value)-min;
	    val=min;
	    pos=minpos;
	}
	newval=val+delval;
	newpos=pos+delval/pos2val;
	if(newpos>maxpos) newpos=maxpos;
	if(newpos<minpos) newpos=minpos;
	bar.css("left",newpos);
    }
    if(newval<min) newval=min;
    if(newval>max) newval=max;
    newval=Math.round(newval*100)/100;
    //$("#report").html("val:"+val+",pos:"+pos+",delta:"+delta+",deltaval:"+delval+",newval:"+newval+",newpos:"+newpos);
    input.attr("value",newval);
}

function posSlider(varid,newpos)
{
    ////////////////////////////////////////////////////
    //COMPONENTS
    ////////////////////////////////////////////////////
    cont=$("#"+varid+"_container");
    button=$("#"+varid+"_button");
    bar=$("#"+varid+"_bar");

    ////////////////////////////////////////////////////
    //GEOMETRICAL PROPERTIES OF SLIDER
    ////////////////////////////////////////////////////
    width=parseFloat(cont.css("width"));
    wbut=parseFloat(button.css("width"));
    wbar=parseFloat(bar.css("width"));
    minpos=wbut;
    maxpos=width-wbut-wbar;
   
    pos=newpos;
    if(pos<minpos){
	pos=minpos;
	cont.die("mousemove");
    }
    if(pos>maxpos){
	pos=maxpos;
	cont.die("mousemove");
    }

    return pos;
}

function moveSlider(varid)
{
    ////////////////////////////////////////////////////
    //COMPONENTS
    ////////////////////////////////////////////////////
    cont=$("#"+varid+"_container");
    input=$("#"+varid);
    button=$("#"+varid+"_button");
    slider=$("#"+varid+"_slider");
    bar=$("#"+varid+"_bar");

    ////////////////////////////////////////////////////
    //GEOMETRICAL PROPERTIES OF SLIDER
    ////////////////////////////////////////////////////
    width=parseFloat(cont.css("width"));
    wbut=parseFloat(button.css("width"));
    wbar=parseFloat(bar.css("width"));
    wsli=parseFloat(slider.css("width"));
    minpos=wbut;
    maxpos=width-wbut-wbar;
    ////////////////////////////////////////////////////
    //ACTIONS
    ////////////////////////////////////////////////////
    if(slider.attr("dragged").search(/true/)>=0){
	cont.live("mousemove",function(e){
		posx=e.pageX-this.offsetLeft-wsli/2;
		valueSlider(varid,posx,0,0);
		posl=posSlider(varid,posx);
		bar.css("left",posl+"px");
	    });
    }else{
	cont.die("mousemove");
    }
    cont.click(function(e){
	    posx=e.pageX-this.offsetLeft-wsli/2;
	    posl=posSlider(varid,posx);
	    if(posl>minpos && posl<maxpos){
		valueSlider(varid,posx,0,0);
		bar.css("left",posl+"px");
	    }
	});
}

function checkInputValue(id,type,reference)
{
    inpel=$("#"+id);
    value=inpel.attr("value");
    qerror=false;
    if(type.search(/minmax/)>=0){
	min=reference['min'];
	max=reference['max'];
	if(value<min){
	    inpel.attr("value",min);
	    qerror=true;
	}
	if(value>max){
	    inpel.attr("value",max);
	    qerror=true;
	}
	if(qerror)
	    alert("Input value should be in the interval ("+
		  min+","+max+")");
    }
}

function queryResultsDatabase(form,resultsid,script)
{
    results=document.getElementById(resultsid);
    querytxt=form.elements[0].value;
    scripturl=script+"Query="+querytxt;

    loadContent(scripturl,resultsid,
		function(element,rtext){
		    element.innerHTML=rtext;
		    $('#DIVBLANKET').css('display','none');
		    $('#DIVOVER').css('display','none');
		},
		function(element,rtext){
		    $('#DIVBLANKET').css('display','block');
		    $('#DIVOVER').css('display','block');
		},
		function(element,rtext){
		    $('#DIVBLANKET').css('display','none');
		    $('#DIVOVER').css('display','none');
		    element.innerHTML="ERROR";
		},
		-1,
		true
		);
}

function selectAll(formid,object)
{
    form=document.getElementById(formid);
    value=object.checked;
    i=0;
    //alert(form.elements.length);
    while(formel=form.elements[i]){
	if(formel.type.search("checkbox")>=0){
	    namel=formel.name;
	    valel=formel.value;
	    subel=$("input[name="+namel+"_Submit]");
	    subel.attr("value",valel);
	    formel.checked=value;
	}
	i++;
    }
}

function toggleBug(elementid,referer)
{
  delement=document.getElementById(elementid);
  element=$("#"+elementid);
  wbox=element.width();
  poslft=referer.offsetLeft;
  postop=referer.offsetTop;
  element.css("top",postop);
  element.css("left",poslft);
  element.toggle('fast',null);
}

function setsValue(elementid,value)
{
  element=document.getElementById(elementid);
  $(element).attr("value",value);
}
