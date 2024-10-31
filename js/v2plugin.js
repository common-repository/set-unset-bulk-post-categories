jQuery(
	function() {
			jQuery( "#startdate" ).datepicker();}
);
jQuery(
	function() {
			jQuery( "#enddate" ).datepicker();}
);
			 var expanded = false;
function showCheckboxes() {
	var checkboxes = document.getElementById( "checkboxes" );
	if ( ! expanded) {
		checkboxes.style.display = "block";
		expanded = true;
	} else {
		checkboxes.style.display = "none";
		expanded = false;
	}
}
			jQuery( document ).ready(
				function(){
					jQuery( "#btn" ).click(
						function(){
							jQuery( "#startdate" ).val( '' );
							jQuery( "#enddate" ).val( '' );
						}
					);
				}
			);
			function displaymessage() {
					jQuery( "select" ).each( function() { this.selectedIndex = 0 } );
			}

			function myFunction() {
				alert( 'hello' );
			}
