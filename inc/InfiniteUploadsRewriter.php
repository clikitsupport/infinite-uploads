<?php

namespace ClikIT\InfiniteUploads;

/**
 * Infinite_Uploads_Rewriter
 *
 * @since 1.0
 */
class InfiniteUploadsRewriter {
	protected $uploads_path = null;    // uploads PATH
	protected $replacements = [];    // urls to be searched for and replaced with CDN URL
	protected $cdn_url = null;    // CDN URL
	protected $exclusions = [];
	protected $smush_url_fix_pairs = [];    // bad smush next-gen URL => correct URL

	/**
	 * constructor
	 *
	 * @param  string  $uploads_url   Original upload url
	 * @param  array   $replacements  Urls to filter
	 * @param  string  $cdn_url       Destination CDN url
	 *
	 * @since 1.0
	 */
	function __construct( $uploads_url, $replacements, $cdn_url ) {
		$this->uploads_path = trailingslashit( parse_url( $uploads_url, PHP_URL_PATH ) );

		$this->replacements = array_unique(
			array_map( [ $this, 'protocolize_url' ],
				apply_filters( 'infinite_uploads_replacement_urls', $replacements )
			)
		);

		$this->cdn_url = $this->protocolize_url( $cdn_url );

		//generate upload url paths that should be excluded from url replacement
		$filelist   = new InfiniteUploadsFilelist( '/' ); //path doesn't matter
		$exclusions = apply_filters( 'infinite_uploads_sync_exclusions', $filelist->exclusions );
		foreach ( $exclusions as $exclusion ) {
			if ( 0 === strpos( $exclusion, '/' ) ) {
				$this->exclusions[ $exclusion ] = untrailingslashit( $uploads_url ) . $exclusion;
			}
		}

		add_action( 'template_redirect', [ &$this, 'handle_rewrite_hook' ] );

		// Make sure we replace urls in REST API responses
		add_filter( 'the_content', [ &$this, 'rewrite_the_content' ], 100 );

		// Build Smush Pro next-gen URL correction pairs.
		// Smush derives its WebP/AVIF base URL via dirname(wp_upload_dir()['baseurl']), which
		// strips the last path segment (site_id) from the CDN URL. We fix the resulting bad
		// CDN URLs in HTML output and REST API responses.
		// e.g. https://cdn.example.com/user_id/smush-webp/ → https://cdn.example.com/user_id/site_id/smush-webp/
		$cdn_no_slash = rtrim( $this->cdn_url, '/' );
		$last_slash   = strrpos( $cdn_no_slash, '/' );
		if ( $last_slash !== false ) {
			$parent_base = substr( $cdn_no_slash, 0, $last_slash );
			// Guard: parent must still be a full URL (not just "https:").
			if ( strpos( $parent_base, '://' ) !== false ) {
				foreach ( [ 'smush-webp', 'smush-avif' ] as $next_gen_dir ) {
					$bad  = $parent_base . '/' . $next_gen_dir . '/';
					$good = $this->cdn_url . $next_gen_dir . '/'; // cdn_url already has trailing slash
					if ( $bad !== $good ) {
						$this->smush_url_fix_pairs[ $bad ] = $good;
					}
				}
			}
		}

		// Fix Smush next-gen URLs in REST API responses.
		// WordPress REST API requests bypass template_redirect, so the ob_start approach
		// does not capture them. Page builders (Elementor, Gutenberg, etc.) fetch attachment
		// data via /wp-json/wp/v2/media/{id} and use the returned URLs directly; without
		// this filter those URLs contain the wrong smush-webp CDN path.
		if ( ! empty( $this->smush_url_fix_pairs ) ) {
			add_filter( 'rest_prepare_attachment', [ &$this, 'rewrite_rest_attachment' ], 100, 3 );
		}
	}

	/**
	 * Fix Smush next-gen (WebP/AVIF) URLs in WordPress REST API attachment responses.
	 *
	 * The ob_start hook on template_redirect does not capture REST API responses.
	 * This filter serialises the response data to JSON, applies the same
	 * smush_url_fix_pairs substitutions used by the HTML rewriter, and restores
	 * the corrected data so consumers receive valid CDN URLs.
	 *
	 * @param  \WP_REST_Response  $response  The REST API response object.
	 * @param  \WP_Post           $post      The attachment post.
	 * @param  \WP_REST_Request   $request   The REST request.
	 *
	 * @return \WP_REST_Response
	 */
	public function rewrite_rest_attachment( $response, $post, $request ) {
		if ( empty( $this->smush_url_fix_pairs ) || ! ( $response instanceof \WP_REST_Response ) ) {
			return $response;
		}

		$data = $response->get_data();
		$json = wp_json_encode( $data );

		if ( $json === false ) {
			return $response;
		}

		$changed = false;
		foreach ( $this->smush_url_fix_pairs as $bad => $good ) {
			if ( strpos( $json, $bad ) !== false ) {
				$json    = str_replace( $bad, $good, $json );
				$changed = true;
			}
		}

		if ( $changed ) {
			$response->set_data( json_decode( $json, true ) );
		}

		return $response;
	}

	/**
	 * Add https protocol to url when needed
	 *
	 * @since   1.0
	 */
	public function protocolize_url( $url ) {
		if ( strpos( $url, ':' ) === false && ! in_array( $url[0], [ '/', '#', '?' ], true ) &&
		     ! preg_match( '/^[a-z0-9-]+?\.php/i', $url ) ) {
			$url = 'https://' . $url;
		}

		return trailingslashit( $url );
	}

	/**
	 * run rewrite hook
	 *
	 * @since   1.0
	 */
	public function handle_rewrite_hook() {
		ob_start( [ &$this, 'rewrite' ] );
	}


	/**
	 * rewrite html content
	 *
	 * @since   1.0
	 */
	public function rewrite_the_content( $html ) {
		return $this->rewrite( $html );
	}

	/**
	 * rewrite url
	 *
	 * @param  string  $html  current raw HTML doc
	 *
	 * @return  string  updated HTML doc with CDN links
	 * @since 1.0
	 *
	 */
	public function rewrite( $html ) {
		// start regex
		$regex_rule = '#((?:https?:)?(?:';

		//add all the domains to replace
		$regex_rule .= implode( '|',
			array_map( [ $this, 'relative_url' ],
				array_map( 'quotemeta', $this->replacements )
			)
		);

		// check for relative paths
		$regex_rule .= ')|(?<=[(\"\'=\s])' . quotemeta( $this->uploads_path ) . ')([^\#\"\'\s]*)#';

		// call the cdn rewriter callback
		$cdn_html = preg_replace_callback( $regex_rule, [ $this, 'rewrite_url' ], $html );

		// Fix Smush next-gen (WebP/AVIF) URLs where dirname() stripped the site_id from the CDN path.
		foreach ( $this->smush_url_fix_pairs as $bad_url => $good_url ) {
			if ( strpos( $cdn_html, $bad_url ) !== false ) {
				$cdn_html = str_replace( $bad_url, $good_url, $cdn_html );
			}
		}

		return $cdn_html;
	}

	/**
	 * Get relative url
	 *
	 * @param  string  $url  a full url
	 *
	 * @return  string  protocol relative url
	 * @since   1.0
	 *
	 */
	protected function relative_url( $url ) {
		return substr( $url, strpos( $url, '//' ) );
	}

	/**
	 * rewrite url
	 *
	 * @param  string  $matches  the matches from regex
	 *
	 * @return  string  updated url if not excluded
	 * @since   1.0
	 *
	 */
	protected function rewrite_url( $matches ) {
		//don't filter excluded dirs
		foreach ( $this->exclusions as $exclusion ) {
			if ( 0 === strpos( $matches[0], $exclusion ) ) {
				return $matches[0];
			}
		}

		// Check if file exclusion is enabled and if the path is excluded.
		if ( InfiniteUploadsHelper::is_file_exclusion_enabled() ) {
			// If the path is in the exclusion list, return the original match.
			$path = isset( $matches[2] ) ? $matches[2] : '';

			$original_upload = InfiniteUploadsHelper::get_original_upload_dir_root();

			$original_base_dir = $original_upload['basedir'];

			$path = $original_base_dir . '/' . $path;
			if ( InfiniteUploadsHelper::is_path_excluded( $path ) ) {
				return $matches[0];
			}
		}

		$replace = str_replace( $matches[1], $this->cdn_url, $matches[0] );

		/**
		 * Filters the find/replace url rewriter that replaces matches in HTML output with CDN url.
		 *
		 * @param  {string}  $replace  The url to replace the match with, like `https://xxxxx.infiniteuploads.cloud/somefile.png`.
		 * @param  {array}   $matches  The the matches found in HTML, like `[0 => 'https://mysite.com/wp-content/uploads/somefile.png', 1 => 'https://mysite.com/wp-content/uploads/', 2 => 'somefile.png']`.
		 *
		 * @return {string} The base url to replace the match with.
		 * @since  1.0
		 * @hook   infinite_uploads_rewrite_url
		 *
		 */
		return apply_filters( 'infinite_uploads_rewrite_url', $replace, $matches );
	}
}
