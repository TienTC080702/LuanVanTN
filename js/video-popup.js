var videoPopup = function(){

		var self = this
		self.openBut = $('.webinar-video');
		self.popup = $('<div id="banner-popup" style="display:none;" />');
		self.fade = $('<div id="fade" style="display:none;" />');
		self.loader = $('<img id="loader" src="/static/interface/img/iframe_preloader_gray.gif" />');

		self.openPopup = function(x){
			flowplayer("video_player", "https://releases.flowplayer.org/swf/flowplayer-3.2.18.swf", {
			    // this will enable pseudostreaming support
			    plugins: {
			        pseudo: {
			            url: "flowplayer.pseudostreaming-3.2.13.swf"
			        }
			    },
			    // clip properties
			    clip: {
			        // our clip uses pseudostreaming
			        provider: 'pseudo',
          			autoPlay: true,
			        url: x.data('href')
			    }
			 
			}).ipad();

			
			$('.video-container').appendTo(self.popup).show();
				//self.vid_obj.play();

				self.popup.appendTo('body').fadeIn(100);
				self.fade.appendTo('body').fadeIn(100);
				//$('body').css('overflow-y', 'hidden')
		}

		self.closePopup = function(x){

			// and stop it from playing
			$f().unload();

			self.popup.fadeOut(100);
			self.fade.fadeOut(100);
			//$('body').css('overflow-y', 'auto')

		}

		self.buildPopup = function(x){

			var closeBut = $('<div class="close-but" />')

			//self.popup.append(closeBut)
			closeBut.on('click',function(){
				self.closePopup();
			})

		}

		self.buildPopup()

		self.openBut.on('click',function(){
			self.openPopup($(this));
		});

		self.fade.on('click',function(){
			self.closePopup();
		});

	}



	$(function(){

		new videoPopup();

	})