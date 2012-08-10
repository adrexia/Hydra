(function($) {
   $(document).ready(function(){
           
           if($('#pageContent').length > 0){

                   		
		var editorContent = new wysihtml5.Editor("pageContent", { // id of textarea element
		  toolbar:      "wysihtml5-toolbar", // id of toolbar element
		  parserRules:  wysihtml5ParserRules, // defined in parser rules set 
		  stylesheets: "css/editor.css"
		});

	    }
	    

           

		
	});
})(jQuery);		
