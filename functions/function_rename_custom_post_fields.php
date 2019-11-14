<?php
add_action( 'current_screen', 'fa_this_screen' );

/**
 * Run code on the admin plugin page
 */
function fa_this_screen() {
	$currentScreen = get_current_screen();
	if( $currentScreen->id === "fapushnotification" ) {
		add_filter( 'gettext', 'fa_rename_push_btn', 10, 2 );
	}
}

function fa_rename_push_btn( $translation, $original )
{

	if ( 'Update' == $original )
	{
		return 'Actualizar & Enviar';
	}
	elseif ( 'Publish' == $original )
	{
		return 'Enviar Notificación';
	}
	elseif ( 'Excerpt' == $original )
	{
		return 'Mensaje';
	}
	else
	{
		$pos = strpos($original, 'Excerpts are optional hand-crafted summaries of your');
		if ($pos !== false) {
			return '<small><b>Note:</b> <i>(Maximum 100 characters would possibly be visible in the notification shades of the android device)</i></small>';
		}
	}
	return $translation;
}
?>