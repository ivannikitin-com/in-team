/**
 * Скрипт наполняет поля быстрого редактирования
 * http://wpdreamer.com/2012/03/manage-wordpress-posts-using-bulk-edit-and-quick-edit/
 */
(function($) {
	var debug = true;
	
	debug && console.log('quick-edit');
   
   
   // we create a copy of the WP inline edit post function
   var $wp_inline_edit = inlineEditPost.edit;

   // and then we overwrite the function with our own code
   inlineEditPost.edit = function( id ) {
      // "call" the original WP edit function
      // we don't want to leave WordPress hanging
      $wp_inline_edit.apply( this, arguments );

      // now we take care of our business
	  debug && console.log('inlineEditPost.edit ID: ' + id);

      // get the post ID
      var $post_id = 0;
      if ( typeof( id ) == 'object' )
         $post_id = parseInt( this.getId( id ) );

      if ( $post_id > 0 ) {

         // define the edit row
         var $edit_row = $( '#edit-' + $post_id );
		 console.log('$edit_row ', $edit_row);

         // get the release date
		 var $linkedUserId = $( '#linked_user_id-' + $post_id ).data('linked-user');
		console.log('$linkedUserId ', $linkedUserId);

		 // populate the release date
		 $edit_row.find( '#inteam_user_id' ).val( $linkedUserId );

      }

   };

})(jQuery);