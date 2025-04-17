<?php

echo "
    <link type=”image/x-icon” href=”../images/logo_mini.ico” rel=”shortcut icon” />
    <link rel=\"stylesheet\" type=\"text/css\" href=\"../css/main.css\">
    <link rel=\"stylesheet\" type=\"text/css\" href=\"../css/footable.core.min.css\">
    <style>
        #wrap {
            background: #F1F1F1;
        }

        .row-fluid {
            margin-bottom: 20px;
        }

        .tabs_container {
            min-height: 300px;
        }

        .tabs_container div {
            margin-left: 20px;
            margin-bottom: 40px;
        }

        #banner-popup {
            background: #fff;
            position: fixed;
            top: 50%;
            left: 50%;
            z-index: 5999;
            padding: 5px;
            box-shadow: 0px 0px 3px 0px #4D4D4D;
            border: 1px solid;
            width: auto;
            height: auto;
            margin-left: -320px;
            margin-top: -180px;
        }

        #banner-popup object {
            float: left;
            vertical-align: top;
            max-height: 100%;
        }

        #banner-popup .close-but {
            height: 24px;
            width: 24px;
            position: absolute;
            top: 0px;
            right: 0px;
            cursor: pointer;
            /*background: url(/static/interface/img/close-but.png) no-repeat;*/
        }

        #fade {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #000;
            opacity: 0.4;
            z-index: 5000;
        }

        #banner-popup.hidden, #fade.hidden {
            display: none;
        }

        .video-container {
            display: none;
        }

        .webinar-video {
            display: inline-block;
        }
    </style>
    <script src=\"../js/jquery-1.11.2.min.js\"></script>
  
    <style type=\"text/css\">@keyframes fadeInOpacity {
                               0% {
                                   opacity: 0
                               }
                               to {
                                   opacity: 1
                               }
                           }

    :hover > * > .fbvd--wrapper {
        animation-name: fadeInOpacity;
        animation-duration: .3s;
        opacity: 1
    }

    .fbvd--wrapper {
        position: absolute;
        top: 10px;
        left: 10px;
        opacity: 0;
        text-align: center;
        margin: 0;
        z-index: 5
    }

    .fbvd--wrapper a {

        background-color: #fff;
        display: inline-block;
        font: 700 14px Helvetica, Arial, sans-serif;
        color: #4b4f56;
        text-decoration: none;
        vertical-align: middle;
        padding: 0px 8px 0px;
        margin-right: 8px;
        border-radius: 2px;
        line-height: 22px;
        padding-left: 19px;
        border: 1px solid #e7e7e7;
        background-size: 13px
    }

    .fbvd--wrapper a:last-child {
        margin-right: 0
    }

    .fbvd--wrapper a:hover {
        text-decoration: none
    }

    .fbvd--wrapper a:focus {
        box-shadow: 0 0 1px 2px rgba(88, 144, 255, .75), 0 1px 1px rgba(0, 0, 0, .15);
        outline: none
    }

    .fbvd--wrapper b {
        font-size: 13px;
        position: relative;
        top: 1px;
        color: #3b5998;
        font-weight: 400
    }</style>
    <!-- <script>  jQuery(document).ready(function($){ 	
	if($(btn-top).length > 0){
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
    &lt;!&ndash; end JS&ndash;&gt;
    <style>.btn-top {
    background-image: url(../images/btntop.png);
    background-repeat: no-repeat;
    border: medium none;
    bottom: 20px;
    cursor: pointer;
    display: none;
    height: 50px;
    outline: medium none;
    padding: 0;
    position: fixed;
    right: 20px;
    width: 50px;
    z-index: 9999;
}

</style>-->
";