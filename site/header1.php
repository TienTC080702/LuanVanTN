<?php


echo "<link rel=\"shortcut icon\" href=\"../images/favicon.ico\" type=\"image/x-icon\">
    <link rel=\"stylesheet\" href=\"../js/flexslider/flexslider.css\">
    <link rel=\"stylesheet\" href=\"../css/basic-style.css\">
    <link href=\"../css/bootstrap.min.css\" rel=\"stylesheet\">
    <link href=\"../css/bootstrap-theme.min.css\" rel=\"stylesheet\">
    <script type=\"text/javascript\" src=\"../js/jquery-3.1.1.min.js\"></script>
    <script type=\"text/javascript\" src=\"../js/bootstrap.min.js\"></script>

    <!-- end CSS-->

    <!-- JS-->
    <script src=\"../js/libs/modernizr-2.6.2.min.js\"></script>
    <script>  jQuery(document).ready(function($){ 	
	if($(\".btn-top\").length > 0){
		$(window).scroll(function () {
			var e = $(window).scrollTop();
			if (e > 300) {
				$(\".btn-top\").show()
			} else {
				$(\".btn-top\").hide()
			}
		});
		$(\".btn-top\").click(function () {
			$('body,html').animate({
				scrollTop: 0
			})
		})
	}		
});
       
</script>
    <!-- end JS-->
    <style>.btn-top {
    background-image: url(../images/gotop.png);
    background-repeat: no-repeat;
    border: medium none;
    bottom: 20px;
    cursor: pointer;
    display: none;
    height: 100px;
    outline: medium none;
    padding: 0;
    position: fixed;
    right: 20px;
    width: 100px;
    z-index: 9999;
}

</style>
";