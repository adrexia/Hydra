(function($) {
   $(document).ready(function(){
   		var object=null;
		var request = null;		

		$('.flexslider').flexslider({
                        animation: "slide", 
                        slideshowSpeed:10000, 
                        pausePlay: true,  
                        pauseText: " ",
                        playText: " ",         
   
                });
            
            //   $('.flex-pauseplay a.flex-pause').addClass('icon-pause');
             //  $('.flex-pauseplay a.flex-play').addClass('icon-play');
                
                
                
                                   
					
		$('li.set').append('<div class="triangle"></div>');
		$('tr th:first').addClass('first');
	   
		//Show and hide data in admin area
		$('.show').click(function(){
			var targetClass=$(this).closest('tr').next('tr').find('.gameList');
			if($(this).text()=="show"){
				$(this).removeClass('noView');
				$(this).text("hide").addClass('view');
				$(targetClass).slideDown("slow", 'linear');
			}else{
				$(this).removeClass('view');
				$(this).text("show").addClass('noView');
				$(targetClass).slideUp("slow", 'linear');
			}
			
			
			return false;
		});   	
		
		
		try{
				request = new XMLHttpRequest();
			}catch (trymicrosoft){
				try{
					request=new ActiveXObject("Msxml2.XMLHTTP");					
				} catch(othermicrosoft){
					try{
						request=new ActiveXObject("Microsoft.XMLHTTP");
					}catch(failed){
						request=null; //insures variable still set to null
					}					
				}				
			}
		if(request==null) console.log("Ajax request failed");	

		javascriptOn();
		
		function javascriptOn(){
			$('a').attr('href', function(){
				if($(this).attr('href')&&$(this).attr('href').indexOf("js=true")==-1){
					$(this).attr('href',$(this).attr('href')+"&js=true");		
				}	
			});
			$('a.accepted').click(function(){
				object =this;
				changeGameStatus();	
				return false;			
			});
			$('a.pending').click(function(){
				object =this;
				changeGameStatus();	
				return false;
			});			
			
			return false;
		}	
		
		
		function changeGameStatus(){
			var url=$(object).attr('href')+"&ran="+new Date().getTime();
			request.open("GET", url, true);
			request.onreadystatechange = updatePage;
			request.send(null);
			return false;
		}
		
		function updatePage(){
			if(request.readyState==4){
				if(request.status==200){
					var newStatus=request.responseText;
					$(object).replaceWith($(newStatus));
					javascriptOn();
				}
			}	
			return false;
		}
		
		$('.gameSub a.edit').click(function(){
			object =this;
			getGameInfo();
			//editGame();
			return false;
		});
		
		function getGameInfo(){
			console.log(object);
			var url=$(object).attr('href')+"&ran="+new Date().getTime();
			request.open("GET", url, true);
			request.onreadystatechange = handleGameInfo;
			request.send(null);
			return false;
		}		
		
		function handleGameInfo(){
			if(request.readyState==4){
				if(request.status==200){
					var form=request.responseText;
					editGame(form);
					javascriptOn();
				}
			}	
			return false;
		}
		
		//Needs game details (from id), list of registered users (to change GM)		
		function editGame(generatedHtml){
	
			function handleSubmit(button,m,content){
				if(button!= undefined){
				 
				}
				
				return true;//closes prompt
			}

			$.prompt(generatedHtml,{
				submit: handleSubmit,
				buttons:{Done:'Done'}
			});
			
			$("select[multiple]").asmSelect({ 
				addItemTarget: 'bottom',      
				animate: true,                
				highlight: true,              
				sortable: true                
			});                               
			
			return false;
		}
		
		


	

		
	});
})(jQuery);