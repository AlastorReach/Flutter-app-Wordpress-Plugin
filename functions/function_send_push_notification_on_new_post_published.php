<?php
add_action( 'transition_post_status', 'post_published_notification', 10, 3 );

function post_published_notification($new_status, $old_status, $post){
			
	if( 'publish' == $new_status && 'publish' != $old_status && $post->post_type == 'post') {
		writeInLog("Entró al primer if: ", 'Sí');
		//Se dispara después de que las categorías o etiquetas de un post se hayan establecido
		add_action('set_object_terms', 'handle_firebase_notification', 10, 6);
	}
		
}

function handle_firebase_notification($object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids){
	// If this is just a revision, don't send the email.
		if ( wp_is_post_revision( $object_id ) ) {
			writeInLog("Es un revición: ", 'Sí');
		return;
       }
	   
	   if(empty($terms)){
			writeInLog("Categorías está vacío: ", 'Sí');
		   return;
	   }
	   writeInLog("Term count: ", count($terms));
	   $this_post = get_post($object_id);
	   
			foreach($terms as $term){
				writeInLog("Term is: ", $term);
			}
			$meta = get_term_meta($terms[0], 'show_in_custom_rest', true);
			writeInLog("Meta es: ", $meta);
			if( $meta == 'yes'){
				$title = $this_post->post_title;
				$permalink = get_permalink( $this_post->ID );
				$notification = array(
					'body' => 'Nueva noticia desde TV Sur Canal 14',
					'title' => $title,
					'content_available' => true,
					'priority' => 'high'
				);
				
				$data = array(
					'post_id' => $this_post->ID,
					'type' => 'NEW_POST_PUBLISHED',
					'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
				);
				
				
				//$to = 
				$to = '/topics/generalNews';
				
				
				sendPushNotification($to, $notification, $data);
			}
		
}



function sendPushNotification($to = "", $notification = array(), $data = array()) {
	$apiKey = "FIREBASE_API_KEY";
	$fields = array('to' => $to, 'notification' => $notification, 'data' => $data);
	
	$headers = array('Authorization: key='.$apiKey, 'Content-Type: application/json');
	
	$url = 'https://fcm.googleapis.com/fcm/send';
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
	$response = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
				
	if ( is_wp_error( $response ) ) {
	   $error_message = $response->get_error_message();
                $filename =  ABSPATH . "/temp/php-errors.log";
                $fh = fopen($filename, "a");
                fwrite($fh, "Mensaje de error: " . $error_message . "\n");
                fclose($fh);
	}
	

}

function writeInLog($message, $value){
	$filename =  ABSPATH . "/temp/php-errors.log";
	$fh = fopen($filename, "a");
	fwrite($fh, $message . $value . "\n");
	fclose($fh);
}
?>