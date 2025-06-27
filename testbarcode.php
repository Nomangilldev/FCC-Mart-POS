 <input id="userInput" type="text"  autofocus/>
<br>
<div class="test">

   <input id="scannerInput" type="text" value="barcodescan" autofocus/>
</div>

<script type="text/javascript">
	$(document).scannerDetection({
    
  //https://github.com/kabachello/jQuery-Scanner-Detection

    timeBeforeScanTest: 200, // wait for the next character for upto 200ms
    avgTimeByChar: 40, // it's not a barcode if a character takes longer than 100ms
  endChar: [13],
  //preventDefault: true, //this would prevent text appearing in the current input field as typed 
        onComplete: function(barcode, qty){
   
    alert(barcode);
    } // main callback function 
});

</script>