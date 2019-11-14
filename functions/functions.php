<?php
function get_upcoming_events($type){
	
	$limit = $type === "ALL_EVENTS_BY_THIS_DAY" ? "-1" : "3";
	$params = shortcode_atts(array(
		'title' => '',
		'limit' => $limit,
		'view_settings' => 'today',
		'next_days' => '1',
		'time_settings' => '',
		'mp_categories' => '',
		'custom_url' => '',
		'disable_url' => '',
		'background_color' => '',
		'hover_background_color' => '',
		'text_color' => '',
		'hover_text_color' => '',
		'item_border_color' => '',
		'hover_item_border_color' => '',
	), $params);
	
	$data['instance'] = $params;
	$data[ 'events' ]  = get_widget_events_2($data['instance']);
	$upcoming_events = array();
	foreach($data['events'] as $key => $event){
		$evento = [
			'event_start' => $event->event_start,
			'event_end'   => $event->event_end,
			'event_title' => $event->post->post_title
		];
		array_push($upcoming_events, $evento);
	}
	
	/*echo '<pre>';
	print_r($upcoming_events);
	echo '</pre>';*/
	return $upcoming_events;
}

function get_widget_events_2( $instance ) {
		$events       = array();
		$current_local_time = current_time( 'timestamp' );

		$weekday      = strtolower( date( 'l', $current_local_time ) );
		$current_date = date( 'd/m/Y', $current_local_time );
		
		// 24.09.2019 seconds added
		$curent_time  = date( 'H:i:s', $current_local_time );
		
		if ( ! empty( $instance[ 'mp_categories' ] ) ) {
			//$category_columns_ids = $this->get( 'column' )->get_columns_by_event_category( $instance[ 'mp_categories' ] );
		}
		
		$args = array(
			'post_type'   => 'mp-column',
			'post_status' => 'publish',
			'fields'      => 'ids',
			'post__in'    => '',
			'orderby'     => 'menu_order',
			'meta_query'  => array(
				'relation' => 'OR',
				array(
					'key'   => 'weekday',
					'value' => $weekday
				),
				array(
					'key'   => 'option_day',
					'value' => $current_date
				)
			)
		);
		
		switch ( $instance[ 'view_settings' ] ) {
			case'today':
			case 'current':
				$column_post_ids = get_posts( $args );
				if ( ! empty( $column_post_ids ) ) {
					$events = get_events_data_2( array( 'column' => 'column_id', 'list' => $column_post_ids ) );
				}
				$events = filter_events_2( array( 'events' => $events, 'view_settings' => $instance[ 'view_settings' ], 'time' => $curent_time, 'mp_categories' => $instance[ 'mp_categories' ] ) );
				break;
			case 'all':
				
				if ( ! empty( $instance[ 'next_days' ] ) && $instance[ 'next_days' ] > 0 ) {
					$events_array = array();
					for ( $i = 0; $i <= $instance[ 'next_days' ]; $i ++ ) {

						// set new day week
						$time = strtotime( "+$i days", $current_local_time );
						$day  = strtolower( date( 'l', $time ) );
						$date = date( 'd/m/Y', $time );

						//set week day
						$args[ 'meta_query' ][ 0 ][ 'value' ] = $day;
						//set new date
						$args[ 'meta_query' ][ 1 ][ 'value' ] = $date;
						
						$column_post_ids = get_posts( $args );
						
						if ( ! empty( $column_post_ids ) ) {
							$events_array[ $i ] = get_events_data_2( array( 'column' => 'column_id', 'list' => $column_post_ids ) );
						} else {
							$events_array[ $i ] = array();
						}
						
						// Filter by time and categories for current day
						if ( $i === 0 && ! empty( $instance[ 'mp_categories' ] ) && ! empty( $events_array[ $i ] ) ) {
							$events_array[ $i ] = $events_array[ $i ] = filter_events_2( array( 'events' => $events_array[ $i ], 'view_settings' => 'today', 'time' => $curent_time, 'mp_categories' => $instance[ 'mp_categories' ] ) );
						} elseif ( ! empty( $instance[ 'mp_categories' ] ) && ! empty( $events_array[ $i ] ) ) {
							//Filter by  categories for next days
							$events_array[ $i ] = filter_events_by_categories_2( $events_array[ $i ], $instance[ 'mp_categories' ] );
						}
						
					}
					
					foreach ( $events_array as $day_events ) {
						$events = array_merge( $events, $day_events );
					}
					
				}
				
				break;
			
			default:
				$column_post_ids = get_posts( $args );
				if ( ! empty( $column_post_ids ) ) {
					$events = get_events_data_2( array( 'column' => 'column_id', 'list' => $column_post_ids ) );
				}
				$events = filter_events_2( array( 'events' => $events, 'view_settings' => 'today', 'time' => $curent_time, 'mp_categories' => $instance[ 'mp_categories' ] ) );
				
				break;
		}
		if ( $instance[ 'limit' ] > 0 ) {
			$events = array_slice( $events, 0, $instance[ 'limit' ] );
		}
		return $events;
	}
	


function get_event_data_2( $params, $order_by = 'event_start', $publish = true ) {
		global $wpdb;
		$publish_query_part = $publish ? " AND `post_status` = 'publish'" : '';
		$table_posts        = $wpdb->prefix . 'posts';
		$table_name     = $wpdb->prefix . "mp_timetable_data";
		$post_type      = 'mp-event';
		
		
		$event_data = $wpdb->get_results(
			"SELECT t.*"
			. " FROM $table_name t INNER JOIN"
			. " ("
			. "	SELECT * FROM {$table_posts}"
			. " WHERE `post_type` = 'mp-column' AND `post_status` = 'publish'"
			. " ) p ON t.`column_id` = p.`ID`"
			. " INNER JOIN ("
			. "	SELECT * FROM {$table_posts}"
			. " WHERE `post_type` = '{$post_type}'{$publish_query_part}"
			. " ) e ON t.`event_id` = e.`ID`"
			. " WHERE t.`{$params["field"]}` = {$params['id']} "
			. " ORDER BY p.`menu_order`, t.`{$order_by}`"
		);
		
		foreach ( $event_data as $key => $event ) {
			$event_data[ $key ]->event_start = date( 'H:i', strtotime( $event_data[ $key ]->event_start ) );
			$event_data[ $key ]->event_end   = date( 'H:i', strtotime( $event_data[ $key ]->event_end ) );
			$event_data[ $key ]->user        = get_user_by( 'id', $event_data[ $key ]->user_id );
			$event_data[ $key ]->post        = get_post( $event_data[ $key ]->event_id );
		}
		
		return $event_data;
	}

function filter_events_2( $params ) {
		$events = array();
		
		$events = filter_by_time_period_2( $params, $events );
		
		if ( ! empty( $params[ 'mp_categories' ] ) ) {
			$events = filter_events_by_categories_2( $events, $params[ 'mp_categories' ] );
		}
		
		return $events;
	}

function filter_by_time_period_2( $params, $events ) {
		if ( ! empty( $params[ 'events' ] ) ) {
			foreach ( $params[ 'events' ] as $key => $event ) {
				if ( $params[ 'view_settings' ] === 'today' || $params[ 'view_settings' ] === 'all' ) {
					if ( strtotime( $event->event_end ) <= strtotime( $params[ 'time' ] ) ) {
						continue;
					}
				} elseif ( $params[ 'view_settings' ] === 'current' ) {
					if ( ( strtotime( $event->event_end ) >= strtotime( $params[ 'time' ] ) && strtotime( $params[ 'time' ] ) <= strtotime( $event->event_start ) ) || strtotime( $event->event_end ) <= strtotime( $params[ 'time' ] ) ) {
						continue;
					}
				}
				$events[ $key ] = $event;
			}
		}
		
		return $events;
	}
	
function filter_events_by_categories_2( array $events, array $categories ) {
		$temp_events = array();
		$taxonomy_names = array(
			'tag' => 'mp-event_tag',
			'cat' => 'mp-event_category',
		);
		$taxonomy    = $taxonomy_names[ 'cat' ];
		
		foreach ( $events as $event ) {
			if ( @has_term( $categories, $taxonomy, $event->post->ID ) ) {
				$temp_events[] = $event;
			}
		}
		
		return $temp_events;
	}

function get_events_data_2( array $params ) {
		global $wpdb;
		$post_type      = 'mp-event';
		$table_name     = $wpdb->prefix . "mp_timetable_data";
		$events      = array();
		$sql_reguest = "SELECT * FROM " . $table_name;
		
		if ( ( ! empty( $params[ 'all' ] ) && $params[ 'all' ] ) || empty( $params[ 'list' ] ) ) {
			
		} elseif ( ! is_array( $params[ 'column' ] ) ) {
			
			if ( isset( $params[ 'list' ] ) && is_array( $params[ 'list' ] ) ) {
				$params[ 'list' ] = implode( ',', $params[ 'list' ] );
			}
			
			$sql_reguest .= " WHERE " . $params[ 'column' ] . " IN (" . $params[ 'list' ] . ")";
			
		} elseif ( is_array( $params[ 'column' ] ) && is_array( $params[ 'column' ] ) ) {
			
			$sql_reguest .= " WHERE ";
			
			$last_key = key( array_slice( $params[ 'column' ], - 1, 1, true ) );
			
			foreach ( $params[ 'column' ] as $key => $column ) {
				if ( isset( $params[ 'list' ][ $column ] ) && is_array( $params[ 'list' ][ $column ] ) ) {
					$params[ 'list' ][ $column ] = implode( ',', $params[ 'list' ][ $column ] );
				}
				$sql_reguest .= $column . " IN (" . $params[ 'list' ][ $column ] . ")";
				$sql_reguest .= ( $last_key != $key ) ? ' AND ' : '';
			}
			
		}
		
		$sql_reguest .= ' ORDER BY `event_start`';
		
		$events_data = $wpdb->get_results( $sql_reguest );
		
		if ( is_array( $events_data ) ) {
			
			foreach ( $events_data as $event ) {
				$post = get_post( $event->event_id );
				
				if ( $post && ( $post->post_type == $post_type ) && ( $post->post_status == 'publish' ) ) {
					$event->post        = $post;
					$event->event_start = date( 'H:i', strtotime( $event->event_start ) );
					$event->event_end   = date( 'H:i', strtotime( $event->event_end ) );
					$events[]           = $event;
				}
			}
		}
		
		return $events;
	}