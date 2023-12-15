jQuery(document).ready(function($) {
    $(".awf-form-control").focus(function(){
      var tmpThis = $(this).val();
      if(tmpThis == '' ) {
        $(this).parent(".awf-form-group").addClass("focus-input");
      }
      else if(tmpThis !='' ){
        $(this).parent(".awf-form-group").addClass("focus-input");
      }
    });
    $(".awf-form-control").blur(function(){
      var tmpThis = $(this).val();
      if(tmpThis == '' ) {
        $(this).parent(".awf-form-group").removeClass("focus-input");
        $(this).siblings('.awf-form-error').slideDown("3000");
      }
      else if(tmpThis !='' ){
        $(this).parent(".awf-form-group").addClass("focus-input");
        $(this).siblings('.awf-form-error').slideUp("3000");
        
      }
    });
    
  }); 