function showEvent(id, x, y)
{
	document.getElementById(id).style.top = (y + 20) + "px";
	if(x > window.innerWidth / 2)
		document.getElementById(id).style.left=(x - 200) + "px";
	else
		document.getElementById(id).style.left = x + "px";
	document.getElementById(id).style.visibility = 'visible';
}

function hideEvent(id)
{
	document.getElementById(id).style.visibility = 'hidden';
}

function editEvent(id, name, desc, date)
{
	document.getElementById('editid').value = id;
	document.getElementById('editname').value = name;
	document.getElementById('editdesc').value = desc;
	document.getElementById('editdate').value = date;
}

function viewEvent(name, desc, date)
{
	document.getElementById('viewname').innerHTML = name;
	desc = desc.replace(/&lt;a href/g, "<a onclick=\"window.open(this.href); return false;\" href");
	desc = desc.replace(/&lt;A HREF/g, "<a onclick=\"window.open(this.href); return false;\" href");
	desc = desc.replace(/<a href/g, "<a onclick=\"window.open(this.href); return false;\" href");
	desc = desc.replace(/<A HREF/g, "<a onclick=\"window.open(this.href); return false;\" href");
	desc = desc.replace(/&lt;\/a/g, "</a");
	desc = desc.replace(/&gt;/g, ">");
	desc = desc.replace(/&amp;quot;/g, "\"");
	desc = desc.replace(/&quot;/g, "\"");
	document.getElementById('viewdesc').innerHTML = desc;
	document.getElementById('viewdate').innerHTML = date;
}
