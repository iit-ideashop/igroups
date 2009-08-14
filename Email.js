function openSpellChecker()
{
	var speller = new spellChecker();
	speller.checkTextAreas();
}

function toggleToDisplay()
{
	tobox = document.getElementById('to-table');
	switch(tobox.style.display)
	{
		case 'none':
			tobox.style.display='block';
			break;
		default:
			tobox.style.display='none';
			break;
	}
}

function toggleSGDisplay()
{
	box = document.getElementById('subgroups-table');
	switch(box.style.display)
	{
		case 'none':
			box.style.display='block';
			break;
		default:
			box.style.display='none';
			break;
	}
}

function toggleGuestDisplay()
{
	guestbox = document.getElementById('guest-table');
	switch(guestbox.style.display)
	{
		case 'none':
			guestbox.style.display='block';
			break;
		default:
			guestbox.style.display='none';
			break;
	}
}


function checkedAll(id, checked)
{
	var el = document.getElementById(id);
	var guest = new RegExp("guest.", "i"), subg = new RegExp("subgroup.", "i");
	for(var i = 0; i < el.elements.length; i++)
	{
		if(el.elements[i].name != 'confidential' && !guest.test(el.elements[i].id + '.') && !subg.test(el.elements[i].id + '.'))
			el.elements[i].checked = checked;
	}
}

function checkedAllGuest(id, checked)
{
	var el = document.getElementById(id);
	var guest = new RegExp("guest.", "i");
	for(var i = 0; i < el.elements.length; i++)
	{
		if(guest.test(el.elements[i].id))
			el.elements[i].checked = checked;
	}
}

function sendinit()
{
	guestbox = document.getElementById('guest-table');
	guestbox.style.display='none';
}

function fileAdd(num)
{
	if(document.getElementById('files').childNodes.length == num)
	{
		var div = document.createElement('div');
		div.className = "stdBoldText";
		div.id = "file"+(num*1+1)+"div";
		div.innerHTML = "&nbsp;&nbsp;&nbsp;<label for=\"attachment"+(num*1+1)+"\">File "+(num*1+1)+":</label> <input type=\"file\" name=\"attachment"+(num*1+1)+"\" id=\"attachment"+(num*1+1)+"\" onchange=\"fileAdd("+(num*1+1)+");\" />";
		document.getElementById('files').appendChild(div);
	}
}

function copyCheckBoxes()
{
	var emails = new Array();
	var inputs = document.getElementsByTagName('input');
	for(var i = 0; i < inputs.length; i++)
	{
		if(inputs[i].type == "checkbox" && inputs[i].checked)
		{
			values = inputs[i].name.split( /\x5b|\x5d/ );
			emails.push( values[1] );
		}
	}
	var emailInputs = document.getElementsByName("emailMove");
	for(var i=0; i < emailInputs.length; i++)
		emailInputs[i].value=emails;
}
