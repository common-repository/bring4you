(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	 
	$(document).ready(function (e) {
		$("#b4y_estimate").click(function(){
		  $.ajax({
				method: "POST",
				headers: { "cache-control": "no-cache" },
				url: '/wp-admin/admin-ajax.php',
				data: {action: 'b4yestimation',lang:$("#bring4you_language").val(),percent:$("#bring4you_percentage").val(),arrival:$("#bring4you_city").val(),departure:$("#bring4you_citydeparture").val(),weight:$("#bring4you_weight").val(),width:$("#bring4you_width").val(),height:$("#bring4you_height").val(),depth:$("#bring4you_depth").val(),estimationtext:$("#bring4you_estimationtext").val()},
				success: function (data) {
					data = data.substring(0,data.length - 1);
					$('#bring4you_estimation').html(data);
				}
			})
		});
		
		$("#bring4you_estimation").on("click","#b4y_estimate_after_error",function(){
		  
		  $.ajax({
				method: "POST",
				headers: { "cache-control": "no-cache" },
				url: '/wp-admin/admin-ajax.php',
				data: {action: 'b4yestimation',lang:$("#bring4you_language").val(),percent:$("#bring4you_percentage").val(),arrival:$("#bring4you_errorform_arrival").val(),departure:$("#bring4you_citydeparture").val(),weight:$("#bring4you_errorform_weight").val(),width:$("#bring4you_errorform_width").val(),height:$("#bring4you_errorform_height").val(),depth:$("#bring4you_errorform_depth").val(),estimationtext:$("#bring4you_estimationtext").val()},
				success: function (data) {
					data = data.substring(0,data.length - 1);
					$('#bring4you_estimation').html(data);
				}
			})
		});
		
	});

})( jQuery );