<?php
class WP_REST_posts_mobile_custom_controller extends WP_REST_Posts_Controller {

    // Esta es una copia del controlador WP_REST_Posts por defecto, el que maneja los endpoints de los posts


    // Edited constructor for custom namespace and endpoint url
    /**
     * Constructor.
     *
     * @since 4.7.0
     * @access public
     *
     * @param string $post_type Post type.
     */
    public function __construct() {

        $this->post_type = 'post';
        $this->namespace = 'mobile/v1';
        $obj = get_post_type_object( $this->post_type );
        $this->rest_base = ! empty( $obj->rest_base ) ? $obj->rest_base : $obj->name;

        $this->resource_name = 'posts';

        $this->meta = new WP_REST_Post_Meta_Fields( $this->post_type );
		$this->schema = $this->prefix_get_comment_schema();
		
    }
	
	/**
 * Get our sample schema for comments.
 */
function prefix_get_comment_schema() {
    $schema = array(
        // This tells the spec of JSON Schema we are using which is draft 4.
        '$schema'              => 'http://json-schema.org/draft-04/schema#',
        // The title property marks the identity of the resource.
        'title'                => 'comment',
        'type'                 => 'object',
        // In JSON Schema you can specify object properties in the properties attribute.
        'properties'           => array(
            'id'           => array(
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
            'date'         => array(
					'description' => __( "The date the object was published, in the site's timezone." ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
			'modified'     => array(
					'description' => __( "The date the object was last modified, in the site's timezone." ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
			'link'         => array(
					'description' => __( 'URL to the object.' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
			'thumbnail' => array(
                'description'  => __( "The featured image of post, size: thumbnail" ),
                'type'         => 'string',
            ),
			'medium' => array(
                'description'  => __( "The featured image of post, size: medium" ),
                'type'         => 'string',
            ),
			'large' => array(
                'description'  => __( "The featured image of post, size: large" ),
                'type'         => 'string',
            ),
        ),
    );
	
	$schema['properties']['title'] = array(
						'description' => __( 'The title for the object.' ),
						'type'        => 'object',
						'context'     => array( 'view', 'edit', 'embed' ),
						'arg_options' => array(
							'sanitize_callback' => null, // Note: sanitization implemented in self::prepare_item_for_database()
							'validate_callback' => null, // Note: validation implemented in self::prepare_item_for_database()
						),
						'properties'  => array(
							'raw'      => array(
								'description' => __( 'Title for the object, as it exists in the database.' ),
								'type'        => 'string',
								'context'     => array( 'edit' ),
							),
							'rendered' => array(
								'description' => __( 'HTML title for the object, transformed for display.' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit', 'embed' ),
								'readonly'    => true,
							),
						),
					);
		
 
    return $schema;
}


    // this is a copy of default class WP_REST_Posts_Controller with necessary edits
	
	/**
	 * Prepares a single post output for response.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_Post         $post    Post object.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $post, $request ) {
		$GLOBALS['post'] = $post;

		setup_postdata( $post );

		$fields = $this->get_fields_for_response( $request );

		// Base fields for every post.
		$data = array();

		if ( in_array( 'id', $fields, true ) ) {
			$data['id'] = $post->ID;
		}

		if ( in_array( 'date', $fields, true ) ) {
			$data['date'] = $this->prepare_date_response( $post->post_date_gmt, $post->post_date );
		}

		if ( in_array( 'date_gmt', $fields, true ) ) {
			// For drafts, `post_date_gmt` may not be set, indicating that the
			// date of the draft should be updated each time it is saved (see
			// #38883).  In this case, shim the value based on the `post_date`
			// field with the site's timezone offset applied.
			if ( '0000-00-00 00:00:00' === $post->post_date_gmt ) {
				$post_date_gmt = get_gmt_from_date( $post->post_date );
			} else {
				$post_date_gmt = $post->post_date_gmt;
			}
			$data['date_gmt'] = $this->prepare_date_response( $post_date_gmt );
		}

		if ( in_array( 'guid', $fields, true ) ) {
			$data['guid'] = array(
				/** This filter is documented in wp-includes/post-template.php */
				'rendered' => apply_filters( 'get_the_guid', $post->guid, $post->ID ),
				'raw'      => $post->guid,
			);
		}

		if ( in_array( 'modified', $fields, true ) ) {
			$data['modified'] = $this->prepare_date_response( $post->post_modified_gmt, $post->post_modified );
		}

		if ( in_array( 'modified_gmt', $fields, true ) ) {
			// For drafts, `post_modified_gmt` may not be set (see
			// `post_date_gmt` comments above).  In this case, shim the value
			// based on the `post_modified` field with the site's timezone
			// offset applied.
			if ( '0000-00-00 00:00:00' === $post->post_modified_gmt ) {
				$post_modified_gmt = date( 'Y-m-d H:i:s', strtotime( $post->post_modified ) - ( get_option( 'gmt_offset' ) * 3600 ) );
			} else {
				$post_modified_gmt = $post->post_modified_gmt;
			}
			$data['modified_gmt'] = $this->prepare_date_response( $post_modified_gmt );
		}

		if ( in_array( 'password', $fields, true ) ) {
			$data['password'] = $post->post_password;
		}

		if ( in_array( 'slug', $fields, true ) ) {
			$data['slug'] = $post->post_name;
		}

		if ( in_array( 'status', $fields, true ) ) {
			$data['status'] = $post->post_status;
		}

		if ( in_array( 'type', $fields, true ) ) {
			$data['type'] = $post->post_type;
		}

		if ( in_array( 'link', $fields, true ) ) {
			$data['link'] = get_permalink( $post->ID );
		}

		if ( in_array( 'title', $fields, true ) ) {
			add_filter( 'protected_title_format', array( $this, 'protected_title_format' ) );

			$data['title'] = array(
				'raw'      => $post->post_title,
				'rendered' => get_the_title( $post->ID ),
			);

			remove_filter( 'protected_title_format', array( $this, 'protected_title_format' ) );
		}

		$has_password_filter = false;

		if ( $this->can_access_password_content( $post, $request ) ) {
			// Allow access to the post, permissions already checked before.
			add_filter( 'post_password_required', '__return_false' );

			$has_password_filter = true;
		}

		if ( in_array( 'content', $fields, true ) ) {
			$data['content'] = array(
				'raw'           => $post->post_content,
				/** This filter is documented in wp-includes/post-template.php */
				'rendered'      => post_password_required( $post ) ? '' : apply_filters( 'the_content', $post->post_content ),
				'protected'     => (bool) $post->post_password,
				'block_version' => block_version( $post->post_content ),
			);
		}

		if ( in_array( 'excerpt', $fields, true ) ) {
			/** This filter is documented in wp-includes/post-template.php */
			$excerpt         = apply_filters( 'the_excerpt', apply_filters( 'get_the_excerpt', $post->post_excerpt, $post ) );
			$data['excerpt'] = array(
				'raw'       => $post->post_excerpt,
				'rendered'  => post_password_required( $post ) ? '' : $excerpt,
				'protected' => (bool) $post->post_password,
			);
		}

		if ( $has_password_filter ) {
			// Reset filter.
			remove_filter( 'post_password_required', '__return_false' );
		}

		if ( in_array( 'author', $fields, true ) ) {
			$data['author'] = (int) $post->post_author;
		}

		if ( in_array( 'featured_media', $fields, true ) ) {
			$data['featured_media'] = (int) get_post_thumbnail_id( $post->ID );
		}

		if ( in_array( 'parent', $fields, true ) ) {
			$data['parent'] = (int) $post->post_parent;
		}

		if ( in_array( 'menu_order', $fields, true ) ) {
			$data['menu_order'] = (int) $post->menu_order;
		}

		if ( in_array( 'comment_status', $fields, true ) ) {
			$data['comment_status'] = $post->comment_status;
		}

		if ( in_array( 'ping_status', $fields, true ) ) {
			$data['ping_status'] = $post->ping_status;
		}

		if ( in_array( 'sticky', $fields, true ) ) {
			$data['sticky'] = is_sticky( $post->ID );
		}

		if ( in_array( 'template', $fields, true ) ) {
			if ( $template = get_page_template_slug( $post->ID ) ) {
				$data['template'] = $template;
			} else {
				$data['template'] = '';
			}
		}

		if ( in_array( 'format', $fields, true ) ) {
			$data['format'] = get_post_format( $post->ID );

			// Fill in blank post format.
			if ( empty( $data['format'] ) ) {
				$data['format'] = 'standard';
			}
		}

		if ( in_array( 'meta', $fields, true ) ) {
			$data['meta'] = $this->meta->get_value( $post->ID, $request );
		}

		$taxonomies = wp_list_filter( get_object_taxonomies( $this->post_type, 'objects' ), array( 'show_in_rest' => true ) );

		foreach ( $taxonomies as $taxonomy ) {
			$base = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;

			if ( in_array( $base, $fields, true ) ) {
				$terms         = get_the_terms( $post, $taxonomy->name );
				$data[ $base ] = $terms ? array_values( wp_list_pluck( $terms, 'term_id' ) ) : array();
			}
		}

		$post_type_obj = get_post_type_object( $post->post_type );
		if ( is_post_type_viewable( $post_type_obj ) && $post_type_obj->public ) {

			if ( ! function_exists( 'get_sample_permalink' ) ) {
				require_once ABSPATH . '/wp-admin/includes/post.php';
			}

			$sample_permalink = get_sample_permalink( $post->ID, $post->post_title, '' );

			if ( in_array( 'permalink_template', $fields, true ) ) {
				$data['permalink_template'] = $sample_permalink[0];
			}
			if ( in_array( 'generated_slug', $fields, true ) ) {
				$data['generated_slug'] = $sample_permalink[1];
			}
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		$links = $this->prepare_links( $post );
		$response->add_links( $links );

		if ( ! empty( $links['self']['href'] ) ) {
			$actions = $this->get_available_actions( $post, $request );

			$self = $links['self']['href'];

			foreach ( $actions as $rel ) {
				$response->add_link( $rel, $self );
			}
		}

		/**
		 * Filters the post data for a response.
		 *
		 * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
		 *
		 * @since 4.7.0
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WP_Post          $post     Post object.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( "rest_prepare_post_mobile", $response, $post, $request );
	}

}