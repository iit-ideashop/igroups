function showEvent(id, x, y)
{
	document.getElementById(id).style.top=(y+20)+"px";
	document.getElementById(id).style.left = (x > window.innerWidth/2) ? ((x-200)+"px") : (x+"px");
	document.getElementById(id).style.visibility='visible';
}

function hideEvent(id)
{
	document.getElementById(id).style.visibility='hidden';
}		

function checkedAll(id, checked)
{
	var el = document.getElementById(id);
	for(var i = 0; i < el.elements.length; i++)
		el.elements[i].checked = checked;
}

function showMessage(msg)
{
	msgDiv = document.createElement("div");
	msgDiv.id="messageBox";
	msgDiv.innerHTML=msg;
	document.body.insertBefore(msgDiv, null);
	window.setTimeout( function() { msgDiv.style.display='none'; }, 3000 );
}
