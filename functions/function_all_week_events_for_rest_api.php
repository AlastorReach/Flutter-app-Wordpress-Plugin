<?php

function get_all_week_events(){
	
		global $wpdb;
		$table_name     = $wpdb->prefix . "mp_timetable_data";
		$table_posts        = $wpdb->prefix . 'posts';
		$post_type_event      = 'mp-event';
		$post_type_column = 'mp-column';
		$publish = true;
		$publish_query_part = $publish ? " AND `post_status` = 'publish'" : '';
		$day_columns = array();
		$events_by_day_column;
		$events_ids = array();
		$row;
		$dias;
		$cont = 0;
		if(post_type_exists('mp-event') && post_type_exists('mp-column')){
		$day_columns_ids = $wpdb->get_results(
				"SELECT distinct t.column_id FROM $table_name t "
				. " INNER JOIN $table_posts p ON p.ID = t.column_id  order by FIELD(p.post_title, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo')",
			'OBJECT');
			
			for($i = 0; $i < count($day_columns_ids); $i++){
				$dias[$i] = $wpdb->get_row(
					"SELECT post_title FROM {$table_posts} WHERE ID = {$day_columns_ids[$i]->column_id}", 'OBJECT'
				);
			}
			
			foreach($day_columns_ids as $key => $value){
				//Selecciona IDS los días de la semana (las columnas que representan los días de la semana)
				$row[$cont] = $wpdb->get_results(
					"SELECT event_id, event_start, event_end FROM $table_name WHERE column_id = $value->column_id", 'OBJECT'
				);
				$cont++;
				
			}
			
			for($i = 0; $i < count($row); $i++){
					$post = array($dias[$i]->post_title => $wpdb->get_results(
						"SELECT t.column_id, p.ID, p.post_title, t.event_start, t.event_end from wp_posts p INNER JOIN wp_mp_timetable_data t on t.event_id = p.ID 
						WHERE t.event_id = p.ID and t.column_id = {$day_columns_ids[$i]->column_id} and p.post_status = 'publish' ORDER BY t.column_id, t.event_start asc", 'OBJECT'
					));
					//array_push($events_ids, $dias[$i]->post_title);
					array_push($events_ids, $post);
			}
			
		echo json_encode($events_ids);
		}
	
}
?>