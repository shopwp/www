<?php

/**
 * The Gravity Forms Query class.
 *
 * @since 2.3
 */
class GF_Query {

	/**
	 * Query vars set by the user
	 *
	 * @since 2.3
	 *
	 * @access public
	 * @var array
	 */
	public $query;

	/**
	 * Query vars, after parsing
	 *
	 * @since 2.3
	 *
	 * @access public
	 * @var array
	 */
	public $query_vars = array();

	/**
	 * Metadata query container
	 *
	 * @since 2.3
	 *
	 * @access public
	 * @var object GF_Meta_Query
	 */
	public $meta_query = false;

	/**
	 * Date query container
	 *
	 * @since 2.3
	 *
	 * @access public
	 * @var object WP_Date_Query
	 */
	public $date_query = false;

	/**
	 * The entry database query.
	 *
	 * @since 2.3
	 *
	 * @access public
	 * @var string
	 */
	public $request;

	/**
	 * List of entries.
	 *
	 * @since 2.3
	 *
	 * @access public
	 * @var array
	 */
	public $entries = null;

	/**
	 * The number of entries for the current query.
	 *
	 * @since 2.3
	 *
	 * @access public
	 * @var int
	 */
	public $entry_count = 0;

	/**
	 * Index of the current item in the loop.
	 *
	 * @since 2.3
	 *
	 * @access public
	 * @var int
	 */
	public $current_entry = - 1;

	/**
	 * Whether the loop has started and the caller is in the loop.
	 *
	 * @since 2.3
	 *
	 * @access public
	 * @var bool
	 */
	public $in_the_loop = false;

	/**
	 * The current entry.
	 *
	 * @since 2.3
	 *
	 * @access public
	 * @var array
	 */
	public $entry;

	/**
	 * The number of found entries for the current query.
	 *
	 * If limit clause was not used, equals $entry_count.
	 *
	 * @since 2.3
	 *
	 * @access public
	 * @var int
	 */
	public $found_entries = 0;

	/**
	 * The number of pages.
	 *
	 * @since 2.3
	 *
	 * @access public
	 * @var int
	 */
	public $max_num_pages = 0;

	/**
	 * Set if query is single entry.
	 *
	 * @since 2.3
	 *
	 * @access public
	 * @var bool
	 */
	public $is_single = false;

	/**
	 * Set if query returns a page.
	 *
	 * @since 2.3
	 *
	 * @access public
	 * @var bool
	 */
	public $is_page = false;

	/**
	 * Set if query is part of a date.
	 *
	 * @since 2.3
	 *
	 * @access public
	 * @var bool
	 */
	public $is_date = false;

	/**
	 * Set if query contains a year.
	 *
	 * @since 2.3
	 *
	 * @access public
	 * @var bool
	 */
	public $is_year = false;

	/**
	 * Set if query contains a month.
	 *
	 * @since 2.3
	 *
	 * @access public
	 * @var bool
	 */
	public $is_month = false;

	/**
	 * Set if query contains a day.
	 *
	 * @since 2.3
	 *
	 * @access public
	 * @var bool
	 */
	public $is_day = false;

	/**
	 * Set if query contains time.
	 *
	 * @since 2.3
	 *
	 * @access public
	 * @var bool
	 */
	public $is_time = false;

	/**
	 * Set if query contains an a created by user.
	 *
	 * @since 2.3
	 *
	 * @access public
	 * @var bool
	 */
	public $is_created_by = false;

	/**
	 * Set if query was part of a search result.
	 *
	 * @since 2.3
	 *
	 * @access public
	 * @var bool
	 */
	public $is_search = false;

	/**
	 * Set if query couldn't find anything.
	 *
	 * @since 2.3
	 *
	 * @access public
	 * @var bool
	 */
	public $is_404 = false;

	/**
	 * Set if query is paged
	 *
	 * @since 2.3
	 *
	 * @access public
	 * @var bool
	 */
	public $is_paged = false;

	/**
	 * Set if query is part of administration page.
	 *
	 * @since 2.3
	 *
	 * @access public
	 * @var bool
	 */
	public $is_admin = false;

	/**
	 * Set if is single, or is a page.
	 *
	 * @since 2.3
	 *
	 * @access public
	 * @var bool
	 */
	public $is_singular = false;

	/**
	 * Stores the ->query_vars state like md5(serialize( $this->query_vars ) ) so we know
	 * whether we have to re-parse because something has changed
	 *
	 * @since 2.3
	 *
	 * @access private
	 * @var bool|string
	 */
	private $query_vars_hash = false;

	/**
	 * Whether query vars have changed since the initial parse_query() call. Used to catch modifications to query vars made
	 * via pre_get_entries hooks.
	 *
	 * @since 2.3
	 *
	 * @access private
	 */
	private $query_vars_changed = true;

	/**
	 * Cached list of search stopwords.
	 *
	 * @since 2.3
	 *
	 * @var array
	 */
	private $stopwords;

	/**
	 * Resets query flags to false.
	 *
	 * The query flags are what page info WordPress was able to figure out.
	 *
	 * @since 2.3
	 *
	 * @access private
	 */
	private function init_query_flags() {
		$this->is_single     = false;
		$this->is_page       = false;
		$this->is_date       = false;
		$this->is_year       = false;
		$this->is_month      = false;
		$this->is_day        = false;
		$this->is_time       = false;
		$this->is_created_by = false;
		$this->is_search     = false;
		$this->is_404        = false;
		$this->is_paged      = false;
		$this->is_admin      = false;
		$this->is_singular   = false;
	}

	/**
	 * Initiates object properties and sets default values.
	 *
	 * @since 2.3
	 *
	 * @access public
	 */
	public function init() {
		unset( $this->entries );
		unset( $this->query );
		$this->query_vars = array();
		$this->entry_count   = 0;
		$this->current_entry = - 1;
		$this->in_the_loop   = false;
		unset( $this->request );
		unset( $this->entry );
		$this->found_entries      = 0;
		$this->max_num_pages      = 0;

		$this->init_query_flags();
	}

	/**
	 * Reparse the query vars.
	 *
	 * @since 2.3
	 *
	 * @access public
	 */
	public function parse_query_vars() {
		$this->parse_query();
	}

	/**
	 * Fills in the query variables, which do not exist within the parameter.
	 *
	 * @since 2.3
	 *
	 * @access public
	 *
	 * @param array $array Defined query variables.
	 *
	 * @return array Complete query variables with undefined ones filled in empty.
	 */
	public function fill_query_vars( $array ) {
		$keys = array(
			'error',
			'm', // YearMonth (For e.g.: 201307
			'e', // entry ID
			'post_id',
			'second',
			'minute',
			'hour',
			'day',
			'monthnum',
			'year',
			'w',
			'created_by_name',
			'paged',
			'meta_key',
			'meta_value',
			's',
			'sentence',
			'fields',
			'created_by',
			'form_id',
		);

		foreach ( $keys as $key ) {
			if ( ! isset( $array[ $key ] ) ) {
				$array[ $key ] = '';
			}
		}

		$array_keys = array(
			'entry__in',
			'entry__not_in',
			'created_by__in',
			'created_by__not_in',
			'form_id__in',
			'form_id__not_in',
		);

		foreach ( $array_keys as $key ) {
			if ( ! isset( $array[ $key ] ) ) {
				$array[ $key ] = array();
			}
		}

		return $array;
	}

	/**
	 * Parse a query string and set query type booleans.
	 *
	 * @since 2.3
	 *
	 * @access public
	 *
	 * @param string|array $query {
	 *     Optional. Array or string of Query parameters.
	 *
	 * @type int|string $created_by Author ID, or comma-separated list of IDs.
	 * @type string $created_by_name User 'user_nicename'.
	 * @type array $created_by__in An array of user IDs to query from.
	 * @type array $created_by__not_in An array of user IDs not to query from.
	 * @type bool $cache_results Whether to cache entry information. Default true.
	 * @type array $date_query An associative array of WP_Date_Query arguments.
	 *                                                 See WP_Date_Query::__construct().
	 * @type int $day Day of the month. Default empty. Accepts numbers 1-31.
	 * @type bool $exact Whether to search by exact keyword. Default false.
	 * @type string|array $fields Which fields to return. Single field or all fields (string),
	 *                                                 or array of fields.
	 *                                                 Default all fields. Accepts 'ids'.
	 * @type int $hour Hour of the day. Default empty. Accepts numbers 0-23.
	 * @type int $m Combination YearMonth. Accepts any four-digit year and month
	 *                                                 numbers 1-12. Default empty.
	 * @type string $meta_compare Comparison operator to test the 'meta_value'.
	 * @type string $meta_key Custom field key.
	 * @type array $meta_query An associative array of GF_Meta_Query arguments. See GF_Meta_Query.
	 * @type string $meta_value Custom field value.
	 * @type int $meta_value_num Custom field value number.
	 * @type int $menu_order The menu order of the entries.
	 * @type int $monthnum The two-digit month. Default empty. Accepts numbers 1-12.
	 * @type bool $nopaging Show all entries (true) or paginate (false). Default false.
	 * @type bool $no_found_rows Whether to skip counting the total rows found. Enabling can improve
	 *                                                 performance. Default false.
	 * @type int $offset The number of entries to offset before retrieval.
	 * @type string $order Designates ascending or descending order of entries. Default 'DESC'.
	 *                                                 Accepts 'ASC', 'DESC'.
	 * @type string|array $orderby Sort retrieved entries by parameter. One or more options may be
	 *                                                 passed. To use 'meta_value', or 'meta_value_num',
	 *                                                 'meta_key=keyname' must be also be defined. To sort by a
	 *                                                 specific `$meta_query` clause, use that clause's array key.
	 *                                                 Default 'date'. Accepts 'none', 'created_by', 'date',
	 *                                                 'title', 'modified', 'menu_order', 'ID', 'rand',
	 *                                                 'RAND(x)' (where 'x' is an integer seed value),
	 *                                                 'comment_count', 'meta_value', 'meta_value_num', 'entry__in',
	 *                                                 and the array keys of `$meta_query`.
	 * @type int $p Entry ID.
	 * @type int $page Show the number of entries that would show up on page X of a
	 *                                                 page.
	 * @type int $paged The number of the current page.
	 * @type int $page_id Page ID.
	 * @type string $pagename Page slug.
	 * @type string $perm Show entries if user has the appropriate capability.
	 * @type array $entry__in An array of entry IDs to retrieve, sticky entries will be included
	 * @type array $entry__not_in An array of entry IDs not to retrieve. Note: a string of comma-
	 *                                                 separated IDs will NOT work.
	 * @type string|array $entry_status An entry status (string) or array of entry statuses.
	 * @type int $entries_per_page The number of entries to query for. Use -1 to request all entries.
	 * @type string $s Search keyword(s). Prepending a term with a hyphen will
	 *                                                 exclude entries matching that term. Eg, 'pillow -sofa' will
	 *                                                 return entries containing 'pillow' but not 'sofa'.
	 * @type int $second Second of the minute. Default empty. Accepts numbers 0-60.
	 * @type bool $sentence Whether to search by phrase. Default false.
	 * @type bool $suppress_filters Whether to suppress filters. Default false.
	 *                                                 true. Note: a string of comma-separated IDs will NOT work.
	 * @type int $w The week number of the year. Default empty. Accepts numbers 0-53.
	 * @type int $year The four-digit year. Default empty. Accepts any four-digit year.
	 * }
	 */
	public function parse_query( $query = '' ) {
		if ( ! empty( $query ) ) {
			$this->init();
			$this->query = $this->query_vars = wp_parse_args( $query );
		} elseif ( ! isset( $this->query ) ) {
			$this->query = $this->query_vars;
		}

		$this->query_vars         = $this->fill_query_vars( $this->query_vars );
		$qv                       = &$this->query_vars;
		$this->query_vars_changed = true;

		$qv['e']          = absint( $qv['e'] );
		$qv['post_id']    = absint( $qv['post_id'] );
		$qv['year']       = absint( $qv['year'] );
		$qv['monthnum']   = absint( $qv['monthnum'] );
		$qv['day']        = absint( $qv['day'] );
		$qv['w']          = absint( $qv['w'] );
		$qv['m']          = is_scalar( $qv['m'] ) ? preg_replace( '|[^0-9]|', '', $qv['m'] ) : '';
		$qv['paged']      = absint( $qv['paged'] );
		$qv['created_by'] = preg_replace( '|[^0-9,-]|', '', $qv['created_by'] ); // comma separated list of positive or negative integers
		if ( '' !== $qv['hour'] ) {
			$qv['hour'] = absint( $qv['hour'] );
		}
		if ( '' !== $qv['minute'] ) {
			$qv['minute'] = absint( $qv['minute'] );
		}
		if ( '' !== $qv['second'] ) {
			$qv['second'] = absint( $qv['second'] );
		}

		// Fairly insane upper bound for search string lengths.
		if ( ! is_scalar( $qv['s'] ) || ( ! empty( $qv['s'] ) && strlen( $qv['s'] ) > 1600 ) ) {
			$qv['s'] = '';
		}


		if ( $qv['e'] ) {
			$this->is_single = true;
		} elseif ( ( '' !== $qv['hour'] ) && ( '' !== $qv['minute'] ) && ( '' !== $qv['second'] ) && ( '' != $qv['year'] ) && ( '' != $qv['monthnum'] ) && ( '' != $qv['day'] ) ) {
			// If year, month, day, hour, minute, and second are set, a single
			// entry is being queried.
			$this->is_single = true;
		} else {
			// Look for archive queries. Dates, categories, authors, search, entry type archives.

			if ( isset( $this->query['s'] ) ) {
				$this->is_search = true;
			}

			if ( '' !== $qv['second'] ) {
				$this->is_time = true;
				$this->is_date = true;
			}

			if ( '' !== $qv['minute'] ) {
				$this->is_time = true;
				$this->is_date = true;
			}

			if ( '' !== $qv['hour'] ) {
				$this->is_time = true;
				$this->is_date = true;
			}

			if ( $qv['day'] ) {
				if ( ! $this->is_date ) {
					$date = sprintf( '%04d-%02d-%02d', $qv['year'], $qv['monthnum'], $qv['day'] );
					if ( $qv['monthnum'] && $qv['year'] && ! wp_checkdate( $qv['monthnum'], $qv['day'], $qv['year'], $date ) ) {
						$qv['error'] = '404';
					} else {
						$this->is_day  = true;
						$this->is_date = true;
					}
				}
			}

			if ( $qv['monthnum'] ) {
				if ( ! $this->is_date ) {
					if ( 12 < $qv['monthnum'] ) {
						$qv['error'] = '404';
					} else {
						$this->is_month = true;
						$this->is_date  = true;
					}
				}
			}

			if ( $qv['year'] ) {
				if ( ! $this->is_date ) {
					$this->is_year = true;
					$this->is_date = true;
				}
			}

			if ( $qv['m'] ) {
				$this->is_date = true;
				if ( strlen( $qv['m'] ) > 9 ) {
					$this->is_time = true;
				} elseif ( strlen( $qv['m'] ) > 7 ) {
					$this->is_day = true;
				} elseif ( strlen( $qv['m'] ) > 5 ) {
					$this->is_month = true;
				} else {
					$this->is_year = true;
				}
			}

			if ( '' != $qv['w'] ) {
				$this->is_date = true;
			}

			$this->query_vars_hash = false;


			if ( empty( $qv['created_by'] ) || ( $qv['created_by'] == '0' ) ) {
				$this->is_created_by = false;
			} else {
				$this->is_created_by = true;
			}

			if ( '' != $qv['created_by_name'] ) {
				$this->is_created_by = true;
			}

		}


		if ( '' != $qv['paged'] && ( intval( $qv['paged'] ) > 1 ) ) {
			$this->is_paged = true;
		}


		if ( is_admin() ) {
			$this->is_admin = true;
		}

		$this->is_singular = $this->is_single || $this->is_page;

		if ( ! empty( $qv['entry_status'] ) ) {
			if ( is_array( $qv['entry_status'] ) ) {
				$qv['entry_status'] = array_map( 'sanitize_key', $qv['entry_status'] );
			} else {
				$qv['entry_status'] = preg_replace( '|[^a-z0-9_,-]|', '', $qv['entry_status'] );

			}
		}


		$this->is_singular = $this->is_single;

		if ( '404' == $qv['error'] ) {
			$this->set_404();
		}

		$this->query_vars_hash    = md5( serialize( $this->query_vars ) );
		$this->query_vars_changed = false;

		/**
		 * Fires after the main query vars have been parsed.
		 *
		 *
		 * @param GF_Query &$this The GF_Query instance (passed by reference).
		 */
		do_action_ref_array( 'gform_parse_query', array( &$this ) );
	}

	/**
	 * Generate SQL for the WHERE clause based on passed search terms.
	 *
	 * @since 2.3
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param array $q Query variables.
	 *
	 * @return string WHERE clause.
	 */
	protected function parse_search( &$q ) {
		global $wpdb;

		$search = '';

		// added slashes screw with quote grouping when done early, so done later
		$q['s'] = stripslashes( $q['s'] );
		if ( empty( $_GET['s'] ) && $this->is_main_query() ) {
			$q['s'] = urldecode( $q['s'] );
		}
		// there are no line breaks in <input /> fields
		$q['s']                  = str_replace( array( "\r", "\n" ), '', $q['s'] );
		$q['search_terms_count'] = 1;
		if ( ! empty( $q['sentence'] ) ) {
			$q['search_terms'] = array( $q['s'] );
		} else {
			if ( preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $q['s'], $matches ) ) {
				$q['search_terms_count'] = count( $matches[0] );
				$q['search_terms']       = $this->parse_search_terms( $matches[0] );
				// if the search string has only short terms or stopwords, or is 10+ terms long, match it as sentence
				if ( empty( $q['search_terms'] ) || count( $q['search_terms'] ) > 9 ) {
					$q['search_terms'] = array( $q['s'] );
				}
			} else {
				$q['search_terms'] = array( $q['s'] );
			}
		}

		return $search;
	}

	/**
	 * Check if the terms are suitable for searching.
	 *
	 * Uses an array of stopwords (terms) that are excluded from the separate
	 * term matching when searching for entries. The list of English stopwords is
	 * the approximate search engines list, and is translatable.
	 *
	 * @since 2.3
	 *
	 * @param array $terms Terms to check.
	 *
	 * @return array Terms that are not stopwords.
	 */
	protected function parse_search_terms( $terms ) {
		$strtolower = function_exists( 'mb_strtolower' ) ? 'mb_strtolower' : 'strtolower';
		$checked    = array();

		$stopwords = $this->get_search_stopwords();

		foreach ( $terms as $term ) {
			// keep before/after spaces when term is for exact match
			if ( preg_match( '/^".+"$/', $term ) ) {
				$term = trim( $term, "\"'" );
			} else {
				$term = trim( $term, "\"' " );
			}

			// Avoid single A-Z and single dashes.
			if ( ! $term || ( 1 === strlen( $term ) && preg_match( '/^[a-z\-]$/i', $term ) ) ) {
				continue;
			}

			if ( in_array( call_user_func( $strtolower, $term ), $stopwords, true ) ) {
				continue;
			}

			$checked[] = $term;
		}

		return $checked;
	}

	/**
	 * Retrieve stopwords used when parsing search terms.
	 *
	 * @since 2.3
	 *
	 * @return array Stopwords.
	 */
	protected function get_search_stopwords() {
		if ( isset( $this->stopwords ) ) {
			return $this->stopwords;
		}

		/* translators: This is a comma-separated list of very common words that should be excluded from a search,
		 * like a, an, and the. These are usually called "stopwords". You should not simply translate these individual
		 * words into your language. Instead, look for and provide commonly accepted stopwords in your language.
		 */
		$words = explode( ',', _x( 'about,an,are,as,at,be,by,com,for,from,how,in,is,it,of,on,or,that,the,this,to,was,what,when,where,who,will,with,www',
			'Comma-separated list of search stopwords in your language' ) );

		$stopwords = array();
		foreach ( $words as $word ) {
			$word = trim( $word, "\r\n\t " );
			if ( $word ) {
				$stopwords[] = $word;
			}
		}

		/**
		 * Filters stopwords used when parsing search terms.
		 *
		 * @since 2.3
		 *
		 * @param array $stopwords Stopwords.
		 */
		$this->stopwords = apply_filters( 'wp_search_stopwords', $stopwords );

		return $this->stopwords;
	}

	/**
	 * If the passed orderby value is allowed, convert the alias to a
	 * properly-prefixed orderby value.
	 *
	 * @since 2.3
	 *
	 * @access protected
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $orderby Alias for the field to order by.
	 *
	 * @return string|false Table-prefixed value to used in the ORDER clause. False otherwise.
	 */
	protected function parse_orderby( $orderby ) {
		global $wpdb;

		// Used to filter values.
		$allowed_keys = array(
			'id',
			'form_id',
			'post_id',
			'date_created',
			'is_starred',
			'is_read',
			'ip',
			'source_url',
			'user_agent',
			'currency',
			'payment_status',
			'payment_date',
			'payment_amount',
			'transaction_id',
			'is_fulfilled',
			'created_by',
			'transaction_type',
			'status',
			'rand',
		);

		$primary_meta_key   = '';
		$primary_meta_query = false;
		$meta_clauses       = $this->meta_query->get_clauses();
		if ( ! empty( $meta_clauses ) ) {
			$primary_meta_query = reset( $meta_clauses );

			if ( ! empty( $primary_meta_query['key'] ) ) {
				$primary_meta_key = $primary_meta_query['key'];
				$allowed_keys[]   = $primary_meta_key;
			}

			$allowed_keys[] = 'meta_value';
			$allowed_keys[] = 'meta_value_num';
			$allowed_keys   = array_merge( $allowed_keys, array_keys( $meta_clauses ) );
		}

		// If RAND() contains a seed value, sanitize and add to allowed keys.
		$rand_with_seed = false;
		if ( preg_match( '/RAND\(([0-9]+)\)/i', $orderby, $matches ) ) {
			$orderby        = sprintf( 'RAND(%s)', intval( $matches[1] ) );
			$allowed_keys[] = $orderby;
			$rand_with_seed = true;
		}

		if ( ! in_array( $orderby, $allowed_keys, true ) ) {
			return false;
		}

		switch ( $orderby ) {
			case 'id':
			case 'form_id':
			case 'post_id':
			case 'date_created':
			case 'is_starred':
			case 'is_read':
			case 'ip':
			case 'source_url':
			case 'user_agent':
			case 'currency':
			case 'payment_status':
			case 'payment_date':
			case 'payment_amount':
			case 'transaction_id':
			case 'is_fulfilled':
			case 'created_by':
			case 'transaction_type':
			case 'status':
				$orderby_clause = "{$wpdb->prefix}gf_entry.{$orderby}";
				break;
			case 'rand':
				$orderby_clause = 'RAND()';
				break;
			case $primary_meta_key:
			case 'meta_value':
				if ( ! empty( $primary_meta_query['type'] ) ) {
					$orderby_clause = "CAST({$primary_meta_query['alias']}.meta_value AS {$primary_meta_query['cast']})";
				} else {
					$orderby_clause = "{$primary_meta_query['alias']}.meta_value";
				}
				break;
			case 'meta_value_num':
				$orderby_clause = "{$primary_meta_query['alias']}.meta_value+0";
				break;
			default:
				if ( array_key_exists( $orderby, $meta_clauses ) ) {
					// $orderby corresponds to a meta_query clause.
					$meta_clause    = $meta_clauses[ $orderby ];
					$orderby_clause = "CAST({$meta_clause['alias']}.meta_value AS {$meta_clause['cast']})";
				} elseif ( $rand_with_seed ) {
					$orderby_clause = $orderby;
				} else {
					// Default: order by entry field.
					$orderby_clause = "{$wpdb->prefix}gf_entry." . sanitize_key( $orderby );
				}

				break;
		}

		return $orderby_clause;
	}

	/**
	 * Parse an 'order' query variable and cast it to ASC or DESC as necessary.
	 *
	 * @since 2.3
	 *
	 * @access protected
	 *
	 * @param string $order The 'order' query variable.
	 *
	 * @return string The sanitized 'order' query variable.
	 */
	protected function parse_order( $order ) {
		if ( ! is_string( $order ) || empty( $order ) ) {
			return 'DESC';
		}

		if ( 'ASC' === strtoupper( $order ) ) {
			return 'ASC';
		} else {
			return 'DESC';
		}
	}

	/**
	 * Sets the 404 property and saves whether query is feed.
	 *
	 * @access public
	 */
	public function set_404() {

		$this->init_query_flags();
		$this->is_404 = true;

	}

	/**
	 * Retrieve query variable.
	 *
	 * @since 2.3
	 *
	 * @access public
	 *
	 * @param string $query_var Query variable key.
	 * @param mixed $default Optional. Value to return if the query variable is not set. Default empty.
	 *
	 * @return mixed Contents of the query variable.
	 */
	public function get( $query_var, $default = '' ) {
		if ( isset( $this->query_vars[ $query_var ] ) ) {
			return $this->query_vars[ $query_var ];
		}

		return $default;
	}

	/**
	 * Set query variable.
	 *
	 * @since 2.3
	 *
	 * @access public
	 *
	 * @param string $query_var Query variable key.
	 * @param mixed $value Query variable value.
	 */
	public function set( $query_var, $value ) {
		$this->query_vars[ $query_var ] = $value;
	}

	/**
	 * Retrieve the entries based on query variables.
	 *
	 * There are a few filters and actions that can be used to modify the entry
	 * database query.
	 *
	 * @since 2.3
	 *
	 * @access public
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return array List of entries.
	 */
	public function get_entries() {
		global $wpdb;

		$this->parse_query();

		/**
		 * Fires after the query variable object is created, but before the actual query is run.
		 *
		 * @since 2.3
		 *
		 * @param GF_Query &$this The GF_Query instance (passed by reference).
		 */
		do_action_ref_array( 'gform_pre_get_entries', array( &$this ) );

		// Shorthand.
		$q = &$this->query_vars;

		// Fill again in case gform_pre_get_entries unset some vars.
		$q = $this->fill_query_vars( $q );

		$q = $this->parse_global_criteria( $q );

		// Parse meta query
		$this->meta_query = new GF_Meta_Query();
		$this->meta_query->parse_query_vars( $q );

		// Set a flag if a pre_get_entries hook changed the query vars.
		$hash = md5( serialize( $this->query_vars ) );
		if ( $hash != $this->query_vars_hash ) {
			$this->query_vars_changed = true;
			$this->query_vars_hash    = $hash;
		}
		unset( $hash );

		// First let's clear some variables
		$distinct          = 'DISTINCT'; // The WP_Query default is empty returning duplicates. The GF_Query default is no duplicates.
		$whichcreated_by   = '';
		$where             = '';
		$limits            = '';
		$join              = '';
		$search            = '';
		$groupby           = '';
		$entry_status_join = false;
		$page              = 1;

		if ( ! isset( $q['suppress_filters'] ) ) {
			$q['suppress_filters'] = false;
		}

		if ( ! isset( $q['cache_results'] ) ) {
			if ( wp_using_ext_object_cache() ) {
				$q['cache_results'] = false;
			} else {
				$q['cache_results'] = true;
			}
		}

		if ( empty( $q['entries_per_page'] ) ) {
			$q['entries_per_page'] = 20;
		}
		$q['entries_per_page'] = (int) $q['entries_per_page'];
		if ( $q['entries_per_page'] < - 1 ) {
			$q['entries_per_page'] = abs( $q['entries_per_page'] );
		} elseif ( $q['entries_per_page'] == 0 ) {
			$q['entries_per_page'] = 1;
		}

		if ( isset( $q['page'] ) ) {
			$q['page'] = trim( $q['page'], '/' );
			$q['page'] = absint( $q['page'] );
		}

		// If true, forcibly turns off SQL_CALC_FOUND_ROWS even when limits are present.
		if ( isset( $q['no_found_rows'] ) ) {
			$q['no_found_rows'] = (bool) $q['no_found_rows'];
		} else {
			$q['no_found_rows'] = false;
		}

		switch ( $q['fields'] ) {
			case 'ids':
				$fields = "{$wpdb->prefix}gf_entry.id";
				break;
			default:
				$fields = "{$wpdb->prefix}gf_entry.*";
		}

		// The "m" parameter is meant for months but accepts datetimes of varying specificity
		if ( $q['m'] ) {
			$where .= " AND YEAR({$wpdb->prefix}gf_entry.date_created)=" . substr( $q['m'], 0, 4 );
			if ( strlen( $q['m'] ) > 5 ) {
				$where .= " AND MONTH({$wpdb->prefix}gf_entry.date_created)=" . substr( $q['m'], 4, 2 );
			}
			if ( strlen( $q['m'] ) > 7 ) {
				$where .= " AND DAYOFMONTH({$wpdb->prefix}gf_entry.date_created)=" . substr( $q['m'], 6, 2 );
			}
			if ( strlen( $q['m'] ) > 9 ) {
				$where .= " AND HOUR({$wpdb->prefix}gf_entry.date_created)=" . substr( $q['m'], 8, 2 );
			}
			if ( strlen( $q['m'] ) > 11 ) {
				$where .= " AND MINUTE({$wpdb->prefix}gf_entry.date_created)=" . substr( $q['m'], 10, 2 );
			}
			if ( strlen( $q['m'] ) > 13 ) {
				$where .= " AND SECOND({$wpdb->prefix}gf_entry.date_created)=" . substr( $q['m'], 12, 2 );
			}
		}

		// Handle the other individual date parameters
		$date_parameters = array();

		if ( '' !== $q['hour'] ) {
			$date_parameters['hour'] = $q['hour'];
		}

		if ( '' !== $q['minute'] ) {
			$date_parameters['minute'] = $q['minute'];
		}

		if ( '' !== $q['second'] ) {
			$date_parameters['second'] = $q['second'];
		}

		if ( $q['year'] ) {
			$date_parameters['year'] = $q['year'];
		}

		if ( $q['monthnum'] ) {
			$date_parameters['monthnum'] = $q['monthnum'];
		}

		if ( $q['w'] ) {
			$date_parameters['week'] = $q['w'];
		}

		if ( $q['day'] ) {
			$date_parameters['day'] = $q['day'];
		}

		if ( $date_parameters ) {
			add_filter( 'date_query_valid_columns', array( $this, 'filter_date_query_valid_columns' ) ); // Required for <=  WP 4.0
			$date_query = new WP_Date_Query( array( $date_parameters ), GFFormsModel::get_entry_table_name() . '.date_created' );
			$where .= $date_query->get_sql();

		}
		unset( $date_parameters, $date_query );

		// Handle complex date queries
		if ( ! empty( $q['date_query'] ) ) {
			add_filter( 'date_query_valid_columns', array( $this, 'filter_date_query_valid_columns' ) ); // Required for <= WP 4.0
			$this->date_query = new WP_Date_Query( $q['date_query'], GFFormsModel::get_entry_table_name() . '.date_created' );
			$where .= $this->date_query->get_sql();
			remove_filter( 'date_query_valid_columns', array( $this, 'filter_date_query_valid_columns' ) );
		}

		// If an entry number is specified, load that entry
		if ( $q['e'] ) {
			$where .= " AND {$wpdb->prefix}gf_entry.id = " . $q['e'];
		} elseif ( $q['entry__in'] ) {
			$entry__in = implode( ',', array_map( 'absint', $q['entry__in'] ) );
			$where .= " AND {$wpdb->prefix}gf_entry.id IN ($entry__in)";
		} elseif ( $q['entry__not_in'] ) {
			$entry__not_in = implode( ',', array_map( 'absint', $q['entry__not_in'] ) );
			$where .= " AND {$wpdb->prefix}gf_entry.id NOT IN ($entry__not_in)";
		}

		// Use the form_id column of the meta table if there are meta queries.
		$form_id_where_table = empty( $this->meta_query->queries ) ? "{$wpdb->prefix}gf_entry" : "{$wpdb->prefix}gf_entry_meta";
		if ( is_numeric( $q['form_id'] ) ) {
			$where .= $wpdb->prepare( " AND {$form_id_where_table}.form_id = %d ", $q['form_id'] );
		} elseif ( $q['form_id__in'] ) {
			$form_id__in = implode( ',', array_map( 'absint', $q['form_id__in'] ) );
			$where .= " AND {$form_id_where_table}.form_id IN ($form_id__in)";
		} elseif ( $q['form_id__not_in'] ) {
			$form_id__not_in = implode( ',', array_map( 'absint', $q['form_id__not_in'] ) );
			$where .= " AND {$form_id_where_table}.form_id NOT IN ($form_id__not_in)";
		}

		// If a search pattern is specified, load the entries that match.
		if ( strlen( $q['s'] ) ) {
			$search = $this->parse_search( $q );
		}

		if ( ! $q['suppress_filters'] ) {
			/**
			 * Filters the search SQL that is used in the WHERE clause of GF_Query.
			 *
			 * @since 2.3
			 *
			 * @param string $search Search SQL for WHERE clause.
			 * @param GF_Query $this The current GF_Query object.
			 */
			$search = apply_filters_ref_array( 'gform_entry_search', array( $search, &$this ) );
		}


		if ( empty( $this->meta_query->queries ) ) {
			$groupby = "{$wpdb->prefix}gf_entry.id";
		}

		// User stuff

		if ( ! empty( $q['created_by'] ) && $q['created_by'] != '0' ) {
			$q['created_by'] = addslashes_gpc( '' . urldecode( $q['created_by'] ) );
			$users           = array_unique( array_map( 'intval', preg_split( '/[,\s]+/', $q['created_by'] ) ) );
			foreach ( $users as $user ) {
				$key         = $user > 0 ? 'created_by__in' : 'created_by__not_in';
				$q[ $key ][] = abs( $user );
			}
			$q['created_by'] = implode( ',', $users );
		}

		if ( ! empty( $q['created_by__not_in'] ) ) {
			$user__not_in = implode( ',', array_map( 'absint', array_unique( (array) $q['created_by__not_in'] ) ) );
			$where .= " AND {$wpdb->prefix}gf_entry.created_by NOT IN ($user__not_in) ";
		} elseif ( ! empty( $q['created_by__in'] ) ) {
			$user__in = implode( ',', array_map( 'absint', array_unique( (array) $q['user__in'] ) ) );
			$where .= " AND {$wpdb->prefix}gf_entry.created_by IN ($user__in) ";
		}

		// Author stuff for nice URLs

		if ( '' != $q['created_by_name'] ) {
			if ( strpos( $q['created_by_name'], '/' ) !== false ) {
				$q['created_by_name'] = explode( '/', $q['created_by_name'] );
				if ( $q['created_by_name'][ count( $q['created_by_name'] ) - 1 ] ) {
					$q['created_by_name'] = $q['created_by_name'][ count( $q['created_by_name'] ) - 1 ]; // no trailing slash
				} else {
					$q['created_by_name'] = $q['created_by_name'][ count( $q['created_by_name'] ) - 2 ]; // there was a trailing slash
				}
			}
			$q['created_by_name'] = sanitize_title_for_query( $q['created_by_name'] );
			$q['created_by']      = get_user_by( 'slug', $q['created_by_name'] );
			if ( $q['created_by_name'] ) {
				$q['created_by_name'] = $q['created_by_name']->ID;
			}
			$whichcreated_by .= " AND ({$wpdb->prefix}gf_entry.created_by = " . absint( $q['created_by'] ) . ')';
		}

		$where .= $search . $whichcreated_by;

		if ( ! empty( $this->meta_query->queries ) ) {
			$clauses = $this->meta_query->get_sql( 'entry', "{$wpdb->prefix}gf_entry", 'id', $this );
			$join .= $clauses['join'];
			$where .= $clauses['where'];
		}

		$rand = ( isset( $q['orderby'] ) && 'rand' === $q['orderby'] );
		if ( ! isset( $q['order'] ) ) {
			$q['order'] = $rand ? '' : 'DESC';
		} else {
			$q['order'] = $rand ? '' : $this->parse_order( $q['order'] );
		}

		// Order by.
		if ( empty( $q['orderby'] ) ) {
			/*
			 * Boolean false or empty array blanks out ORDER BY,
			 * while leaving the value unset or otherwise empty sets the default.
			 */
			if ( isset( $q['orderby'] ) && ( is_array( $q['orderby'] ) || false === $q['orderby'] ) ) {
				$orderby = '';
			} else {
				$orderby = "{$wpdb->prefix}gf_entry.date_created " . $q['order'];
			}
		} elseif ( 'none' == $q['orderby'] ) {
			$orderby = '';
		} elseif ( $q['orderby'] == 'entry__in' && ! empty( $entry__in ) ) {
			$orderby = "FIELD( {$wpdb->prefix}gf_entry.id, $entry__in )";
		} else {
			$orderby_array = array();
			if ( is_array( $q['orderby'] ) ) {
				foreach ( $q['orderby'] as $_orderby => $order ) {
					$orderby = addslashes_gpc( urldecode( $_orderby ) );
					$parsed  = $this->parse_orderby( $orderby );

					if ( ! $parsed ) {
						continue;
					}

					$orderby_array[] = $parsed . ' ' . $this->parse_order( $order );
				}
				$orderby = implode( ', ', $orderby_array );

			} else {
				$q['orderby'] = urldecode( $q['orderby'] );
				$q['orderby'] = addslashes_gpc( $q['orderby'] );

				foreach ( explode( ' ', $q['orderby'] ) as $i => $orderby ) {
					$parsed = $this->parse_orderby( $orderby );
					// Only allow certain values for safety.
					if ( ! $parsed ) {
						continue;
					}

					$orderby_array[] = $parsed;
				}
				$orderby = implode( ' ' . $q['order'] . ', ', $orderby_array );

				if ( empty( $orderby ) ) {
					$orderby = "{$wpdb->prefix}gf_entry.date_created " . $q['order'];
				} elseif ( ! empty( $q['order'] ) ) {
					$orderby .= " {$q['order']}";
				}
			}
		}

		// Order search results by relevance only when another "orderby" is not specified in the query.
		if ( ! empty( $q['s'] ) ) {
			$search_orderby = '';

			if ( ! $q['suppress_filters'] ) {
				/**
				 * Filters the ORDER BY used when ordering search results.
				 *
				 * @since 2.3
				 *
				 * @param string $search_orderby The ORDER BY clause.
				 * @param GF_Query $this The current GF_Query instance.
				 */
				$search_orderby = apply_filters( 'gform_entries_search_orderby', $search_orderby, $this );
			}

			if ( $search_orderby ) {
				$orderby = $orderby ? $search_orderby . ', ' . $orderby : $search_orderby;
			}
		}

		$edit_cap = 'edit_entry';
		$read_cap = 'read_entry';

		$user_id = get_current_user_id();

		$q_status = array();
		if ( ! empty( $q['entry_status'] ) ) {
			$statuswheres = array();
			$q_status     = $q['entry_status'];
			if ( ! is_array( $q_status ) ) {
				$q_status = explode( ',', $q_status );
			}
			$r_status = array();
			$p_status = array();
			$e_status = array();
			if ( in_array( 'any', $q_status ) ) {
				foreach ( array( 'exclude_from_search' => true ) as $status ) {
					if ( ! in_array( $status, $q_status ) ) {
						$e_status[] = "{$wpdb->prefix}gf_entry.status <> '$status'";
					}
				}
			} else {
				$entry_stati = array( 'active', 'trash' );
				foreach ( $entry_stati as $status ) {
					if ( in_array( $status, $q_status ) ) {
						if ( 'private' == $status ) {
							$p_status[] = "{$wpdb->prefix}gf_entry.status = '$status'";
						} else {
							$r_status[] = "{$wpdb->prefix}gf_entry.status = '$status'";
						}
					}
				}
			}

			if ( ! empty( $e_status ) ) {
				$statuswheres[] = "(" . join( ' AND ', $e_status ) . ")";
			}
			if ( ! empty( $r_status ) ) {
				if ( ! empty( $q['perm'] ) && 'editable' == $q['perm'] && ! current_user_can( 'gravityforms_edit_others' ) ) {
					$statuswheres[] = "({$wpdb->prefix}gf_entry.created_by = $user_id " . "AND (" . join( ' OR ', $r_status ) . "))";
				} else {
					$statuswheres[] = "(" . join( ' OR ', $r_status ) . ")";
				}
			}

			if ( ! empty( $p_status ) ) {
				if ( ! empty( $q['perm'] ) && 'readable' == $q['perm'] && ! current_user_can( 'gravityforms_read_others' ) ) {
					$statuswheres[] = "({$wpdb->prefix}gf_entry.created_by = $user_id " . "AND (" . join( ' OR ', $p_status ) . "))";
				} else {
					$statuswheres[] = "(" . join( ' OR ', $p_status ) . ")";
				}
			}
			if ( $entry_status_join ) {
				foreach ( $statuswheres as $index => $statuswhere ) {
					$statuswheres[ $index ] = "($statuswhere OR ({$wpdb->prefix}gf_entry.status = 'inherit' AND " . str_replace( "{$wpdb->prefix}gf_entry", 'p2', $statuswhere ) . "))";
				}
			}
			$where_status = implode( ' OR ', $statuswheres );
			if ( ! empty( $where_status ) ) {
				$where .= " AND ($where_status)";
			}
		} elseif ( ! $this->is_singular ) {
			$where .= " AND ({$wpdb->prefix}gf_entry.status = 'active'";

			// Add public states.
			$public_states = array( 'active', 'trash' );
			foreach ( (array) $public_states as $state ) {
				if ( 'active' == $state ) // Active is hard-coded above.
				{
					continue;
				}
				$where .= " OR {$wpdb->prefix}gf_entry.status = '$state'";
			}

			$where .= ')';
		}

		if ( isset( $q['properties_query'] ) ) {
			$where .= $this->get_properties_query_sql();
		}

		/*
		 * Apply filters on where and join prior to paging so that any
		 * manipulations to them are reflected in the paging by day queries.
		 */
		if ( ! $q['suppress_filters'] ) {
			/**
			 * Filters the WHERE clause of the query.
			 *
			 * @param string $where The WHERE clause of the query.
			 * @param GF_Query &$this The GF_Query instance (passed by reference).
			 */
			$where = apply_filters_ref_array( 'gform_entries_where', array( $where, &$this ) );

			/**
			 * Filters the JOIN clause of the query.
			 *
			 * @param string $where The JOIN clause of the query.
			 * @param GF_Query &$this The GF_Query instance (passed by reference).
			 */
			$join = apply_filters_ref_array( 'gform_entries_join', array( $join, &$this ) );
		}

		// Paging
		if ( empty( $q['nopaging'] ) && ! $this->is_singular ) {
			$page = absint( $q['paged'] );
			if ( ! $page ) {
				$page = 1;
			}

			// If 'offset' is provided, it takes precedence over 'paged'.
			if ( isset( $q['offset'] ) && is_numeric( $q['offset'] ) ) {
				$q['offset'] = absint( $q['offset'] );
				$pgstrt      = $q['offset'] . ', ';
			} else {
				$pgstrt = absint( ( $page - 1 ) * $q['entries_per_page'] ) . ', ';
			}
			$limits = 'LIMIT ' . $pgstrt . $q['entries_per_page'];
		}

		$pieces = array( 'where', 'groupby', 'join', 'orderby', 'distinct', 'fields', 'limits' );

		/*
		 * Apply entry-paging filters on where and join. Only plugins that
		 * manipulate paging queries should use these hooks.
		 */
		if ( ! $q['suppress_filters'] ) {
			/**
			 * Filters the WHERE clause of the query.
			 *
			 * Specifically for manipulating paging queries.
			 *
			 *
			 * @param string $where The WHERE clause of the query.
			 * @param GF_Query &$this The GF_Query instance (passed by reference).
			 */
			$where = apply_filters_ref_array( 'gform_entries_where_paged', array( $where, &$this ) );

			/**
			 * Filters the GROUP BY clause of the query.
			 *
			 *
			 * @param string $groupby The GROUP BY clause of the query.
			 * @param GF_Query &$this The GF_Query instance (passed by reference).
			 */
			$groupby = apply_filters_ref_array( 'gform_entries_groupby', array( $groupby, &$this ) );

			/**
			 * Filters the JOIN clause of the query.
			 *
			 * Specifically for manipulating paging queries.
			 *
			 *
			 * @param string $join The JOIN clause of the query.
			 * @param GF_Query &$this The GF_Query instance (passed by reference).
			 */
			$join = apply_filters_ref_array( 'gform_entries_join_paged', array( $join, &$this ) );

			/**
			 * Filters the ORDER BY clause of the query.
			 *
			 *
			 * @param string $orderby The ORDER BY clause of the query.
			 * @param GF_Query &$this The GF_Query instance (passed by reference).
			 */
			$orderby = apply_filters_ref_array( 'gform_entries_orderby', array( $orderby, &$this ) );

			/**
			 * Filters the DISTINCT clause of the query.
			 *
			 *
			 * @param string $distinct The DISTINCT clause of the query.
			 * @param GF_Query &$this The GF_Query instance (passed by reference).
			 */
			$distinct = apply_filters_ref_array( 'gform_entries_distinct', array( $distinct, &$this ) );

			/**
			 * Filters the LIMIT clause of the query.
			 *
			 * @param string $limits The LIMIT clause of the query.
			 * @param GF_Query &$this The GF_Query instance (passed by reference).
			 */
			$limits = apply_filters_ref_array( 'gform_entry_limits', array( $limits, &$this ) );

			/**
			 * Filters the SELECT clause of the query.
			 *
			 *
			 * @param string $fields The SELECT clause of the query.
			 * @param GF_Query &$this The GF_Query instance (passed by reference).
			 */
			$fields = apply_filters_ref_array( 'gform_entries_fields', array( $fields, &$this ) );

			/**
			 * Filters all query clauses at once, for convenience.
			 *
			 * Covers the WHERE, GROUP BY, JOIN, ORDER BY, DISTINCT,
			 * fields (SELECT), and LIMITS clauses.
			 *
			 *
			 * @param array $clauses The list of clauses for the query.
			 * @param GF_Query &$this The GF_Query instance (passed by reference).
			 */
			$clauses = (array) apply_filters_ref_array( 'gform_entries_clauses', array( compact( $pieces ), &$this ) );

			$where    = isset( $clauses['where'] ) ? $clauses['where'] : '';
			$groupby  = isset( $clauses['groupby'] ) ? $clauses['groupby'] : '';
			$join     = isset( $clauses['join'] ) ? $clauses['join'] : '';
			$orderby  = isset( $clauses['orderby'] ) ? $clauses['orderby'] : '';
			$distinct = isset( $clauses['distinct'] ) ? $clauses['distinct'] : '';
			$fields   = isset( $clauses['fields'] ) ? $clauses['fields'] : '';
			$limits   = isset( $clauses['limits'] ) ? $clauses['limits'] : '';
		}

		/**
		 * Fires to announce the query's current selection parameters.
		 *
		 * For use by caching plugins.
		 *
		 * @since 2.3
		 *
		 * @param string $selection The assembled selection query.
		 */
		do_action( 'gform_entries_selection', $where . $groupby . $orderby . $limits . $join );

		/*
		 * Filters again for the benefit of caching plugins.
		 * Regular plugins should use the hooks above.
		 */
		if ( ! $q['suppress_filters'] ) {
			/**
			 * Filters the WHERE clause of the query.
			 *
			 * For use by caching plugins.
			 *
			 * @since 2.3
			 *
			 * @param string $where The WHERE clause of the query.
			 * @param GF_Query &$this The GF_Query instance (passed by reference).
			 */
			$where = apply_filters_ref_array( 'gform_entries_where_request', array( $where, &$this ) );

			/**
			 * Filters the GROUP BY clause of the query.
			 *
			 * For use by caching plugins.
			 *
			 * @since 2.3
			 *
			 * @param string $groupby The GROUP BY clause of the query.
			 * @param GF_Query &$this The GF_Query instance (passed by reference).
			 */
			$groupby = apply_filters_ref_array( 'gform_entries_groupby_request', array( $groupby, &$this ) );

			/**
			 * Filters the JOIN clause of the query.
			 *
			 * For use by caching plugins.
			 *
			 * @since 2.3
			 *
			 * @param string $join The JOIN clause of the query.
			 * @param GF_Query &$this The GF_Query instance (passed by reference).
			 */
			$join = apply_filters_ref_array( 'gform_entries_join_request', array( $join, &$this ) );

			/**
			 * Filters the ORDER BY clause of the query.
			 *
			 * For use by caching plugins.
			 *
			 * @since 2.3
			 *
			 * @param string $orderby The ORDER BY clause of the query.
			 * @param GF_Query &$this The GF_Query instance (passed by reference).
			 */
			$orderby = apply_filters_ref_array( 'gform_entries_orderby_request', array( $orderby, &$this ) );

			/**
			 * Filters the DISTINCT clause of the query.
			 *
			 * For use by caching plugins.
			 *
			 * @since 2.3
			 *
			 * @param string $distinct The DISTINCT clause of the query.
			 * @param GF_Query &$this The GF_Query instance (passed by reference).
			 */
			$distinct = apply_filters_ref_array( 'gform_entries_distinct_request', array( $distinct, &$this ) );

			/**
			 * Filters the SELECT clause of the query.
			 *
			 * For use by caching plugins.
			 *
			 * @since 2.3
			 *
			 * @param string $fields The SELECT clause of the query.
			 * @param GF_Query &$this The GF_Query instance (passed by reference).
			 */
			$fields = apply_filters_ref_array( 'gform_entries_fields_request', array( $fields, &$this ) );

			/**
			 * Filters the LIMIT clause of the query.
			 *
			 * For use by caching plugins.
			 *
			 * @since 2.3
			 *
			 * @param string $limits The LIMIT clause of the query.
			 * @param GF_Query &$this The GF_Query instance (passed by reference).
			 */
			$limits = apply_filters_ref_array( 'gform_entries_limits_request', array( $limits, &$this ) );

			/**
			 * Filters all query clauses at once, for convenience.
			 *
			 * For use by caching plugins.
			 *
			 * Covers the WHERE, GROUP BY, JOIN, ORDER BY, DISTINCT,
			 * fields (SELECT), and LIMITS clauses.
			 *
			 * @since 2.3
			 *
			 * @param array $pieces The pieces of the query.
			 * @param GF_Query &$this The GF_Query instance (passed by reference).
			 */
			$clauses = (array) apply_filters_ref_array( 'gform_entries_clauses_request', array(
				compact( $pieces ),
				&$this
			) );

			$where    = isset( $clauses['where'] ) ? $clauses['where'] : '';
			$groupby  = isset( $clauses['groupby'] ) ? $clauses['groupby'] : '';
			$join     = isset( $clauses['join'] ) ? $clauses['join'] : '';
			$orderby  = isset( $clauses['orderby'] ) ? $clauses['orderby'] : '';
			$distinct = isset( $clauses['distinct'] ) ? $clauses['distinct'] : '';
			$fields   = isset( $clauses['fields'] ) ? $clauses['fields'] : '';
			$limits   = isset( $clauses['limits'] ) ? $clauses['limits'] : '';
		}

		if ( ! empty( $groupby ) ) {
			$groupby = 'GROUP BY ' . $groupby;
		}
		if ( ! empty( $orderby ) ) {
			$orderby = 'ORDER BY ' . $orderby;
		}

		$found_rows = '';
		if ( ! $q['no_found_rows'] && ! empty( $limits ) ) {
			$found_rows = 'SQL_CALC_FOUND_ROWS';
		}

		$this->request = $old_request = "SELECT $found_rows $distinct $fields FROM {$wpdb->prefix}gf_entry $join WHERE 1=1 $where $groupby $orderby $limits";

		if ( ! $q['suppress_filters'] ) {
			/**
			 * Filters the completed SQL query before sending.
			 *
			 * @since 2.3
			 *
			 * @param string $request The complete SQL query.
			 * @param GF_Query &$this The GF_Query instance (passed by reference).
			 */
			$this->request = apply_filters_ref_array( 'gform_entries_request', array( $this->request, &$this ) );
		}

		/**
		 * Filters the entries array before the query takes place.
		 *
		 * Return a non-null value to bypass WordPress's default entry queries.
		 *
		 * Filtering functions that require pagination information are encouraged to set
		 * the `found_entries` and `max_num_pages` properties of the GF_Query object,
		 * passed to the filter by reference. If GF_Query does not perform a database
		 * query, it will not have enough information to generate these values itself.
		 *
		 * @since 2.3
		 *
		 * @param array|null $entries Return an array of entry data to short-circuit the query,
		 *                          or null to allow GF to run its normal queries.
		 * @param GF_Query $this The GF_Query instance, passed by reference.
		 */
		$this->entries = apply_filters_ref_array( 'gform_entries_pre_query', array( null, &$this ) );

		if ( 'ids' == $q['fields'] ) {
			if ( null === $this->entries ) {
				$this->entries = $wpdb->get_col( $this->request );
			}

			$this->entries     = array_map( 'intval', $this->entries );
			$this->entry_count = count( $this->entries );
			$this->set_found_entries( $q, $limits );

			return $this->entries;
		}

		if ( ! isset( $this->entries ) ) {
			$split_the_query = ( $old_request == $this->request && "{$wpdb->prefix}gf_entry.*" == $fields && ! empty( $limits ) && $q['entries_per_page'] < 500 );

			/**
			 * Filters whether to split the query.
			 *
			 * Splitting the query will cause it to fetch just the IDs of the found entries
			 * (and then individually fetch each entry by ID), rather than fetching every
			 * complete row at once. One massive result vs. many small results.
			 *
			 * @since 2.3
			 *
			 * @param bool $split_the_query Whether or not to split the query.
			 * @param GF_Query $this The GF_Query instance.
			 */
			$split_the_query = apply_filters( 'gform_split_the_query', $split_the_query, $this );

			if ( $split_the_query ) {
				// First get the IDs and then fill in the objects

				$this->request = "SELECT $found_rows $distinct {$wpdb->prefix}gf_entry.id FROM {$wpdb->prefix}gf_entry $join WHERE 1=1 $where $groupby $orderby $limits";

				/**
				 * Filters the Entry IDs SQL request before sending.
				 *
				 *
				 * @param string $request The entry ID request.
				 * @param GF_Query $this The GF_Query instance.
				 */
				$this->request = apply_filters( 'gform_entries_request_ids', $this->request, $this );

				$ids = $wpdb->get_col( $this->request );

				if ( $ids ) {
					$this->entries = $ids;
					$this->set_found_entries( $q, $limits );
				} else {
					$this->entries = array();
				}
			} else {
				$this->entries = $wpdb->get_results( $this->request );
				$this->set_found_entries( $q, $limits );
			}
		}

		// Convert to entries arrays.
		if ( $this->entries ) {
			$this->entries = array_map( array( $this, 'get_entry' ), $this->entries );
		}

		if ( ! $q['suppress_filters'] ) {
			/**
			 * Filters the raw entry results array, prior to status checks.
			 *
			 * @since 2.3
			 *
			 * @param array $entries The entry results array.
			 * @param GF_Query &$this The GF_Query instance (passed by reference).
			 */
			$this->entries = apply_filters_ref_array( 'gform_entry_results', array( $this->entries, &$this ) );
		}


		if ( ! $q['suppress_filters'] ) {
			/**
			 * Filters the array of retrieved entries after they've been fetched and
			 * internally processed.
			 *
			 * @since 2.3
			 *
			 * @param array $entries The array of retrieved entries.
			 * @param GF_Query &$this The GF_Query instance (passed by reference).
			 */
			$this->entries = apply_filters_ref_array( 'gform_the_entries', array( $this->entries, &$this ) );
		}

		if ( $this->entries ) {
			$this->entry_count = count( $this->entries );

			$this->entry = isset( $this->entry ) ? reset( $this->entry ) : null;
		} else {
			$this->entry_count = 0;
			$this->entries     = array();
		}


		return $this->entries;
	}

	/**
	 * Set up the amount of found entries and the number of pages (if limit clause was used)
	 * for the current query.
	 *
	 * @access private
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param array $q Query variables.
	 * @param string $limits LIMIT clauses of the query.
	 */
	private function set_found_entries( $q, $limits ) {
		global $wpdb;

		// Bail if entries is an empty array. Continue if entries is an empty string,
		// null, or false to accommodate caching plugins that fill entries later.
		if ( $q['no_found_rows'] || ( is_array( $this->entries ) && ! $this->entries ) ) {
			return;
		}

		if ( ! empty( $limits ) ) {
			/**
			 * Filters the query to run for retrieving the found entries.
			 *
			 * @since 2.3
			 *
			 * @param string $found_entries The query to run to find the found entries.
			 * @param GF_Query &$this The GF_Query instance (passed by reference).
			 */
			$this->found_entries = $wpdb->get_var( apply_filters_ref_array( 'gform_found_entries_query', array(
				'SELECT FOUND_ROWS()',
				&$this
			) ) );
		} else {
			$this->found_entries = count( $this->entries );
		}

		/**
		 * Filters the number of found entries for the query.
		 *
		 * @since 2.3
		 *
		 * @param int $found_entries The number of entries found.
		 * @param GF_Query &$this The GF_Query instance (passed by reference).
		 */
		$this->found_entries = apply_filters_ref_array( 'gform_found_entries', array( $this->found_entries, &$this ) );

		if ( ! empty( $limits ) ) {
			$this->max_num_pages = ceil( $this->found_entries / $q['entries_per_page'] );
		}
	}

	/**
	 * Sets up the current entry.
	 *
	 * Retrieves the next entry, sets up the entry, sets the 'in the loop'
	 * property to true.
	 *
	 * @access public
	 *
	 */
	public function the_entry() {
		$this->in_the_loop = true;

		if ( $this->current_entry == -1 ) {
			// loop has just started
			/**
			 * Fires once the loop is started.
			 *
			 *
			 * @param GF_Query &$this The GF_Query instance (passed by reference).
			 */
			do_action_ref_array( 'gform_loop_start', array( &$this ) );
		}

		$this->next_entry();
	}

	/**
	 * Set up the next entry and iterate current entry index.
	 *
	 * @since 2.3
	 * @access public
	 *
	 * @return array Next entry.
	 */
	public function next_entry() {

		$this->current_entry ++;

		$this->entry = $this->entries[ $this->current_entry ];

		return $this->entry;
	}

	/**
	 * Determines whether there are more entries available in the loop.
	 *
	 * Calls the {@see 'gform_loop_end'} action when the loop is complete.
	 *
	 * @since 2.3
	 *
	 * @access public
	 *
	 * @return bool True if entries are available, false if end of loop.
	 */
	public function have_entries() {
		if ( $this->current_entry + 1 < $this->entry_count ) {
			return true;
		} elseif ( $this->current_entry + 1 == $this->entry_count && $this->entry_count > 0 ) {
			/**
			 * Fires once the loop has ended.
			 *
			 *
			 * @param GF_Query &$this The GF_Query instance (passed by reference).
			 */
			do_action_ref_array( 'gform_loop_end', array( &$this ) );
			// Do some cleaning up after the loop
			$this->rewind_entries();
		}

		$this->in_the_loop = false;

		return false;
	}

	/**
	 * Rewind the entries and reset entry index.
	 *
	 * @since 2.3
	 *
	 * @access public
	 */
	public function rewind_entries() {
		$this->current_entry = - 1;
		if ( $this->current_entry > 0 ) {
			$this->entry = $this->entries[0];
		}
	}

	/**
	 * Sets up the WordPress query by parsing query string.
	 *
	 * @since 2.3
	 *
	 * @access public
	 *
	 * @param string $query URL query string.
	 *
	 * @return array List of entries.
	 */
	public function query( $query ) {
		$this->init();
		$this->query = $this->query_vars = wp_parse_args( $query );

		return $this->get_entries();
	}

	/**
	 * Constructor.
	 *
	 * Sets up the WordPress query, if parameter is not empty.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param string|array $query URL query string or array of vars.
	 */
	public function __construct( $query = '' ) {
		if ( ! empty( $query ) ) {
			$this->query( $query );
		}
	}


	/**
	 * Is the query for paged result and not for the first page?
	 *
	 * @since 2.3.0
	 *
	 * @return bool
	 */
	public function is_paged() {
		return (bool) $this->is_paged;
	}

	/**
	 * Is the query for a search?
	 *
	 * @since 2.3
	 *
	 * @return bool
	 */
	public function is_search() {
		return (bool) $this->is_search;
	}

	/**
	 * Is the query for a specific time?
	 *
	 * @since 2.3
	 *
	 * @return bool
	 */
	public function is_time() {
		return (bool) $this->is_time;
	}

	/**
	 * Is a year included in the query?
	 *
	 * @since 2.3
	 *
	 * @return bool
	 */
	public function is_year() {
		return (bool) $this->is_year;
	}

	/**
	 * Is the query a 404 (returns no results)?
	 *
	 * @since 2.3
	 *
	 * @return bool
	 */
	public function is_404() {
		return (bool) $this->is_404;
	}

	public function get_entry( $entry_id ) {
		global $wpdb;

		$entry = array(
			'id' => $entry_id
		);


		if ( is_numeric( $entry_id ) ) {
			$entry_table = GFFormsModel::get_entry_table_name();

			$sql = "SELECT * from $entry_table WHERE id = %d";

			$column_values = $wpdb->get_row( $wpdb->prepare( $sql, $entry_id ), ARRAY_A );

			foreach ( $column_values as $column_key => $column_value ) {
				$entry[ $column_key ] = $column_value;
			}
		} else {
			foreach ( $entry_id as $column_key => $column_value ) {
				$entry[ $column_key ] = $column_value;
			}
			$entry_id = $entry['id'];
		}

		$entry_meta_table = GFFormsModel::get_entry_meta_table_name();

		$sql = "SELECT meta_key, meta_value from $entry_meta_table WHERE entry_id = %d";

		$values = $wpdb->get_results( $wpdb->prepare( $sql, $entry_id ), ARRAY_A );

		$db_values = array();

		foreach ( $values as $value ) {
			$db_values[ $value['meta_key'] ] = $value['meta_value'];
		}

		$form_id = $entry['form_id'];

		$form = RGFormsModel::get_form_meta( $form_id );

		// running entry through gform_get_field_value filter

		foreach ( $form['fields'] as $field ) {
			/* @var GF_Field $field */
			$inputs = $field->get_entry_inputs();
			// skip types html, page and section?
			if ( is_array( $inputs ) ) {
				foreach ( $inputs as $input ) {
					$entry[ (string) $input['id'] ] = gf_apply_filters( array(
						'gform_get_input_value',
						$form['id'],
						$field->id,
						$input['id']
					), rgar( $db_values, (string) $input['id'] ), $entry, $field, $input['id'] );
				}
			} else {

				$value = rgar( $db_values, (string) $field->id );

				if ( GFFormsModel::is_openssl_encrypted_field( $entry['id'], $field->id ) ) {
					$value = GFCommon::openssl_decrypt( $value );
				}

				$entry[ $field->id ] = gf_apply_filters( array(
					'gform_get_input_value',
					$form['id'],
					$field->id
				), $value, $entry, $field, '' );

			}
		}

		$entry_meta = GFFormsModel::get_entry_meta( $form_id );
		$meta_keys  = array_keys( $entry_meta );

		foreach ( $meta_keys as $meta_key ) {
			$entry[ $meta_key ] = isset( $db_values[ $meta_key ] ) ? $db_values[ $meta_key ] : null;
		}

		return $entry;
	}

	private function get_properties_query_sql() {
		global $wpdb;

		$properties_query = $this->get( 'properties_query' );

		if ( empty( $properties_query ) ) {
			return;
		}

		$relation = isset( $properties_query['relation'] ) ? $properties_query['relation'] : 'AND';

		$info_column_keys = GFFormsModel::get_lead_db_columns();
		array_push( $info_column_keys, 'id' );
		$int_columns = array( 'id', 'post_id', 'is_starred', 'is_read', 'is_fulfilled', 'entry_id' );
		$where_array = array();
		foreach ( $properties_query as $query ) {
			$key = strtolower( rgar( $query, 'key' ) );

			if ( 'entry_id' === $key ) {
				$key = 'id';
			}

			if ( ! in_array( $key, $info_column_keys ) ) {
				continue;
			}

			if ( isset( $query['compare'] ) ) {
				$query['compare'] = strtoupper( $query['compare'] );
			} else {
				$query['compare'] = isset( $query['value'] ) && is_array( $query['value'] ) ? 'IN' : '=';
			}

			if ( ! in_array( $query['compare'], array(
				'=', '!=', '>', '>=', '<', '<=',
				'LIKE', 'NOT LIKE',
				'IN', 'NOT IN',
				'BETWEEN', 'NOT BETWEEN',
				'EXISTS', 'NOT EXISTS',
				'REGEXP', 'NOT REGEXP', 'RLIKE'
			) ) ) {
				$query['compare'] = '=';
			}

			$compare = $query['compare'];

			$value = rgar( $query, 'value' );

			$search_term = 'like' == $compare ? "%$value%" : $value;
			if ( 'date_created' == $key && '=' === $compare ) {
				$search_date           = new DateTime( $search_term );
				$search_date_str       = $search_date->format( 'Y-m-d' );
				$date_created_start    = $search_date_str . ' 00:00:00';
				$date_create_start_utc = get_gmt_from_date( $date_created_start );
				$date_created_end      = $search_date_str . ' 23:59:59';
				$date_created_end_utc  = get_gmt_from_date( $date_created_end );
				$where_array[] = $wpdb->prepare( "({$wpdb->prefix}gf_entry.date_created >= %s AND {$wpdb->prefix}gf_entry.date_created <= %s)", $date_create_start_utc, $date_created_end_utc );
			} else if ( in_array( $key, $int_columns ) ) {
				$where_array[] = $wpdb->prepare( "{$wpdb->prefix}gf_entry.{$key} $compare %d", $search_term );
			} else {
				$where_array[] = $wpdb->prepare( "{$wpdb->prefix}gf_entry.{$key} $compare %s", $search_term );
			}
		}


		$sql = empty( $where_array ) ? '' : ' AND (' . join( " $relation ", $where_array ) . ')';

		return $sql;
	}

	public function filter_date_query_valid_columns( $valid_columns ) {
		$valid_columns[] = GFFormsModel::get_entry_table_name() . '.date_created';
		return $valid_columns;
	}

	public function parse_global_criteria( $qv ) {
		if ( ! isset( $qv['meta_query'] ) ) {
			return $qv;
		}

		$form_id = $qv['form_id'];


		foreach ( $qv['meta_query'] as &$meta_query ) {
			$meta_query = $this->add_choice_texts( $meta_query, $form_id );
		}

		return $qv;
	}

	public function add_choice_texts( $meta_query, $form_id ) {

		if ( ! empty( $meta_query['key'] ) || ! isset( $meta_query['value'] ) ) {
			return $meta_query;
		}

		// include choice text
		$forms = array();
		if ( $form_id == 0 ) {
			$forms = GFAPI::get_forms();
		} elseif ( is_array( $form_id ) ) {
			foreach ( $form_id as $id ){
				$forms[] = GFAPI::get_form( $id );
			}
		} else {
			$forms[] = GFAPI::get_form( $form_id );
		}

		$original_operator = strtoupper( rgar( $meta_query, 'compare' ) );

		switch ( $original_operator ) {
			case 'CONTAINS':
				$operator = 'LIKE';
				break;
			case '<>':
			case 'ISNOT':
				$operator = '!=';
				break;
			default :
				$operator = empty( $original_operator ) ? '=' : $original_operator;
		}

		$val = $meta_query['value'];

		$new_meta_query = array();

		foreach ( $forms as $form ) {
			if ( isset( $form['fields'] ) ) {
				foreach ( $form['fields'] as $field ) {
					/* @var GF_Field $field */
					if ( is_array( $field->choices ) ) {
						foreach ( $field->choices as $choice ) {
							if ( ( $operator == '=' && strtolower( $choice['text'] ) == strtolower( $val ) ) || ( $operator == 'LIKE' && ! empty( $val ) && strpos( strtolower( $choice['text'] ), strtolower( $val ) ) !== false ) ) {
								if ( $field->gsurveyLikertEnableMultipleRows ) {
									$choice_value = $choice['value'] ;
									$choice_search_operator = 'like';
								} else {
									$choice_value = $choice['value'];
									$choice_search_operator = '=';
								}
								$new_meta_query[] = array( 'compare' => $choice_search_operator, 'key' => $field->id, 'value' => $choice_value, 'form_id' => $form['id'] );
							}
						}
					}
				}
			}
		}

		if ( ! empty( $new_meta_query ) ) {
			$new_meta_query['relation'] = 'OR';
			$new_meta_query[] = $meta_query;
			$meta_query = $new_meta_query;
		}
		return $meta_query;
	}
}
