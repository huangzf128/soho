$(function() {
	// hide all the sub-menus
	$("span.toggle").next().hide();
	
	// set the cursor of the toggling span elements
	$("span.toggle").css("cursor", "pointer");
	
	$("span.toggle").prepend("+ ");

	$("span.toggle").click(function() {
		//$(this).next().toggle(1000);
		$(this).next().toggle(6);
		
		// switch the plus to a minus sign or vice-versa
		var v = $(this).html().substring( 0, 1 );
		if ( v == "+" )
			$(this).html( "-" + $(this).html().substring( 1 ) );
		else if ( v == "-" )
			$(this).html( "+" + $(this).html().substring( 1 ) );
	});

});
