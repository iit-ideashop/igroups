

      <p id="truncateMe">Lorem ipsum dolor sit amet, consectetuer adipiscing

      elit. Aenean consectetuer. Etiam venenatis. Sed ultricies, pede sit

      amet aliquet lobortis, nisi ante sagittis sapien, in rhoncus lectus

      mauris quis massa. Integer porttitor, mi sit amet viverra faucibus,
 
      urna libero viverra nibh, sed dictum nisi mi et diam. Nulla nunc eros,

      convallis sed, varius ac, commodo et, magna. Proin vel

      risus. Vestibulum eu urna. Maecenas lobortis, pede ac dictum pulvinar,
  
      nibh ante vestibulum tortor, eget fermentum urna ipsum ac neque. Nam

      urna nulla, mollis blandit, pretium id, tristique vitae, neque. Etiam

      id tellus. Sed pharetra enim non nisl.</p>

       

      <script type="text/javascript">

       
 
      var len = 100;
 
      var p = document.getElementById('truncateMe');

      if (p) {

       

      var trunc = p.innerHTML;

      if (trunc.length > len) {

       

      /* Truncate the content of the P, then go back to the end of the

      previous word to ensure that we don't truncate in the middle of

      a word */

      trunc = trunc.substring(0, len);
 
      trunc = trunc.replace(/\w+$/, '');

       

      /* Add an ellipses to the end and make it a link that expands
 
      the paragraph back to its original size */

      trunc += '<a href="#" ' +

      'onclick="this.parentNode.innerHTML=' +

      'unescape(\''+escape(p.innerHTML)+'\');return false;">' +

      '...<\/a>';

      p.innerHTML = trunc;

      }

      }

       
 
      </script>

