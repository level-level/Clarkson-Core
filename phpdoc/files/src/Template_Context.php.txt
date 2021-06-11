<?php
/**
 * Clarkson Core Template Context.
 */

namespace Clarkson_Core;

class Template_Context {

	/**
	 * Register all hooks to add context to the template call.
	 */
	public function register_hooks():void {
		add_filter( 'clarkson_core_template_context', array( $this, 'add_author' ), 5, 2 );
		add_filter( 'clarkson_core_template_context', array( $this, 'add_term' ), 5, 2 );
		add_filter( 'clarkson_core_template_context', array( $this, 'add_search_count' ), 5, 2 );
		add_filter( 'clarkson_core_template_context', array( $this, 'add_posts' ), 5, 2 );
		add_filter( 'clarkson_core_template_context', array( $this, 'add_post_type' ), 5, 2 );
	}

	/**
	 * Adds an author object if the current request is an author archive.
	 */
	public function add_author( array $context, \WP_Query $wp_query ): array {
		if ( $wp_query->is_author ) {
			$object_loader = Objects::get_instance();
			$object        = $wp_query->queried_object;
			if ( $object instanceof \WP_User ) {
				$author            = $object_loader->get_user( $object );
				$context['user']   = $author; // Available for backward compatibility.
				$context['author'] = $author;
			}
		}
		return $context;
	}

	/**
	 * Adds a term if the current request is a term archive.
	 */
	public function add_term( array $context, \WP_Query $wp_query ):array {
		if ( $wp_query->is_tax || $wp_query->is_category || $wp_query->is_tag ) {
			$object_loader = Objects::get_instance();
			$term          = $wp_query->queried_object;
			if ( $term instanceof \WP_Term ) {
				$context['term'] = $object_loader->get_term( $term );
			}
		}
		return $context;
	}

	/**
	 * Adds the search result count if the current request is a search.
	 */
	public function add_search_count( array $context, \WP_Query $wp_query ):array {
		if ( $wp_query->is_search ) {
			$context['found_posts'] = $wp_query->get( 'filtered_found_posts' ) ? $wp_query->get( 'filtered_found_posts' ) : $wp_query->found_posts;
		}
		return $context;
	}

	/**
	 * Adds posts to the current context.
	 */
	public function add_posts( array $context, \WP_Query $wp_query ):array {
		$object_loader      = Objects::get_instance();
		$context['objects'] = $object_loader->get_objects( $wp_query->posts );
		$context['object']  = reset( $context['objects'] );
		return $context;
	}

	public function add_post_type( array $context, \WP_Query $wp_query ):array {
		if ( $wp_query->is_post_type_archive ) {
			$queried_object = get_queried_object();
			if ( $queried_object instanceof \WP_Post_Type ) {
				$context['post_type'] = Objects::get_instance()->get_post_type( $queried_object );
			}
		}
		return $context;
	}
}
