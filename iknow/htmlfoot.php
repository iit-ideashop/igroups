</div><!-- end mainContent -->
<br class="clearboth" />
</div>
		<!-- end contentWrapper -->
		
		<!--begin footer -->
		<div id="footer">
			<!-- start copyright statement -->
			<p id="copyright">iGroups &copy; 2009 &nbsp;<a href="http://www.ipro.iit.edu">Interprofessional Projects Program</a> </p> <p id="department"> <a href="http://iit.edu">Illinois Institute of Technology</a></p>
			<!-- end copyright statement -->
		</div>
		<!-- end footer -->
		
	</div> 
	<!-- end main container -->
<script type="text/javascript" >
// The following function is placed in the HTML head
function gotoSemesterUrl()
   {
   //get selected index
   var selectedSemester = document.getElementById('semesterlist').selectedIndex;

   //get value at selected index
   var url_address = document.getElementById('semesterlist').options[selectedSemester].value;
    
   //got to location at index
   window.location.href = url_address;

   }
</script>

