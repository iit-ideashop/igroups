// The following function is placed in the HTML head
function gotoSemesterUrl()
   {
   //get sleected index
   var selectedSemester = document.getElementById('semesterlist').selectedIndex;

   //get value at selected index
   var url_address = location.href + document.getElementById('semesterlist').options[selectedSemester].value;
    
   //got to location at index
   window.location.href = url_address;

   }


