function announcementObj()
{
	this.cycleFlag = -1;
	this.selected = 0;
	this.cycleSecond = 10;
	this.obj = function(){};
	
	this.doCycle = function() {
		delay = this.cycleSecond * 1000;
		if(delay)
			this.rotation = setTimeout("announcements.doCycle();", delay);
		if(this.cycleFlag > 0)
			this.navigation('cycle');
		if(this.cycleFlag == -1) 
			this.cycleFlag = 1;
	}
	
	this.add = function(id, heading, body) {
		if(!this.story)
			this.story = new Array();
			
		i = this.story.length;

		this.story[i] = new this.obj;
		
		this.story[i].id = id;
		this.story[i].heading = heading;
		this.story[i].body = body;
	}
	
	this.view = function(id, cycle) {
		if(id < this.story.length && id >= 0)
		{
			this.selected = id;
			
			document.getElementById('announcehead').innerHTML = this.story[id].heading;
			document.getElementById('announcebody').innerHTML = this.story[id].body;
			
			if(!cycle)
			{
				clearTimeout(this.rotation);
				this.cycleSecond = 0;
				this.cycleFlag = 0;
			}
		}
	}

	this.navigation = function(command) {
		if(command == 'prev')
		{
			change = (this.selected == 0) ? this.story.length-1 : this.selected - 1;
			this.view(change);
		}
		else if(command == 'pause')
		{
			this.cycleSecond = 0;
			this.cycleFlag = 0;
		}
		else if(command == 'next')
		{
			change = (this.selected == this.story.length-1) ? 0 : this.selected + 1;
			this.view(change);
		}
		else if (command == 'cycle')
		{
			change = (this.selected == this.story.length-1) ? 0 : this.selected + 1;
			this.view(change, true);
		}
		else
			this.view(0);
	}
}
