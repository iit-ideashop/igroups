// The following function is placed in the HTML head
function gotoSemesterUrl()
   {
   var selectedSemester = document.semesterlist.selectedIndex;
   var url_address = document.semesterlist.options[selectedSemester].value;
   window.location.href = url_address;
   }


