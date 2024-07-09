( function ( $ ) {
	$( document ).on(
		'click',
		'#last-modified-posts.notice.is-dismissible .notice-dismiss',
		function () {
			$.ajax(
				{
					url: ajaxurl,
					method: 'POST',
					data: {
						action: 'dismiss_last_modified_posts_notice'
					},
					success: function ( response ) {
						if ( response.success ) {
							console.log( 'Last modified and published posts notice dismissed.' );
						}
					}
				}
			);
		}
	);
} )( jQuery );
