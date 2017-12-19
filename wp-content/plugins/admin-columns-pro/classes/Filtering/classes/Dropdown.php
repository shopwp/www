<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Dropdown {

	/**
	 * @var AC_Column
	 */
	private $column;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var array
	 */
	private $options = array();

	/**
	 * @var string
	 */
	private $label;

	/**
	 * @var string
	 */
	private $empty;

	/**
	 * @var string
	 */
	private $nonempty;

	/**
	 * @var string
	 */
	private $current_value;

	/**
	 * @var string ASC, DESC or false
	 */
	private $order = 'ASC';

	/**
	 * @var array
	 */
	private $selection;

	/**
	 * @var bool
	 */
	private $loading;

	/**
	 * @param AC_Column $column
	 * @param array     $args [ $options, $current_item, $empty_option ]
	 *
	 * @return ACP_Filtering_Dropdown
	 */
	public static function create( AC_Column $column, $args ) {
		$self = new self( $column );

		/**
		 * @since 4.0
		 */
		$args = apply_filters( 'acp/filtering/dropdown_args', $args, $column );

		if ( isset( $args['options'] ) ) {
			$self->set_options( $args['options'] );
		}
		if ( isset( $args['current_value'] ) ) {
			$self->set_current_value( $args['current_value'] );
		}
		if ( isset( $args['order'] ) ) {
			$self->set_order( $args['order'] );
		}
		if ( isset( $args['empty_option'] ) ) {
			$self->set_empty_option( $args['empty_option'] );
		}

		return $self;
	}

	public function __construct( AC_Column $column ) {
		$this->column = $column;
		$this->init();
	}

	public function init() {
		$this->set_name( $this->column->get_name() );

		if ( $setting = $this->column->get_setting( 'filter' ) ) {
			$label = $setting->get_value( 'filter_label' );
			if ( ! $label ) {
				$label = sprintf( __( 'All %s', 'codepress-admin-columns' ), strtolower( trim( strip_tags( $this->column->get_setting( 'label' )->get_value() ) ) ) );
			}
			$this->set_label( $label );
		}
	}

	/**
	 * @param string|bool $option
	 */
	public function set_empty_option( $option ) {
		if ( true === $option ) {
			$this->set_empty( true );
			$this->set_nonempty( true );
		}
		if ( is_array( $option ) ) {
			$this->set_empty( $option[0] );
			$this->set_nonempty( $option[1] );
		}
	}

	public function set_name( $name ) {
		$this->name = (string) $name;
	}

	public function get_name() {
		return $this->name;
	}

	public function set_loading( $loading ) {
		$this->loading = (bool) $loading;
	}

	public function is_loading() {
		return $this->loading;
	}

	public function set_options( $options ) {
		$this->options = (array) $options;
	}

	public function get_options() {
        return $this->options;
	}

	public function set_empty( $label ) {
		$this->empty = $label;
	}

	public function get_empty() {
		if ( true === $this->empty ) {
			$this->set_empty( __( 'Empty', 'codepress-admin-columns' ) );
		}

		return $this->empty;
	}

	public function set_nonempty( $label ) {
		$this->nonempty = $label;
	}

	public function get_nonempty() {
		if ( true === $this->nonempty ) {
			$this->set_nonempty( __( 'Not empty', 'codepress-admin-columns' ) );
		}

		return $this->nonempty;
	}

	public function set_label( $label ) {
		$this->label = (string) $label;
	}

	public function get_label() {
		return $this->label;
	}

	/**
	 * @param string|false $order
	 */
	public function set_order( $order ) {
		if ( in_array( $order, array( true, false, 'ASC', 'DESC' ), true ) ) {
			$this->order = $order;
		}
	}

	public function get_order() {
		return $this->order;
	}

	public function set_current_value( $value ) {
		$this->current_value = $value;
	}

	private function get_current_value() {
		return $this->current_value;
	}

	private function has_empty_option() {
		return $this->get_empty() || $this->get_nonempty();
	}

	/**
	 * @param array $options
	 */
	private function santize_options( $options ) {
		$sanitized = array();

		foreach ( $options as $value => $label ) {

		    if ( ! is_scalar( $label ) ) {
		        continue;
            }

			// Prevent slowing down the DOM with too large strings
			if ( strlen( $value ) > 6000 ) {
				continue;
			}

			// No HTML
			$label = strip_tags( $label );
			if ( ! $label ) {
				$label = $value;
			}

			// Crop label to 100 characters
			if ( strlen( str_replace( '&nbsp;', '', $label ) ) > 100 ) {
				$label = substr( $label, 0, 98 ) . '..';
			}

			$sanitized[ $value ] = $label;
		}

		if ( $this->get_order() ) {
			natcasesort( $sanitized );

			if ( 'DESC' === $this->get_order() ) {
				$sanitized = array_reverse( $sanitized );
			}
		}

		return $sanitized;
	}

	/**
	 * Limit number of items to 5k
	 *
	 * @return int
	 */
	private function get_max_items() {
		return 5000;
	}

	/**
	 * @return string
	 */
	private function get_disabled_key( $suffix = '' ) {
		return '__ac_disabled__' . $suffix;
	}

	/**
	 * Dropdown selection option
	 */
	private function set_selection() {
		$options = $this->get_options();

		if ( empty( $options ) && ! $this->has_empty_option() ) {
			$options = array();
		}

		// Max number of items
		$limit_reached = count( $options ) > $this->get_max_items();

		if ( $limit_reached ) {
			$options = array_slice( $options, 0, $this->get_max_items() );
		}

		$options = $this->santize_options( $options );

		if ( $limit_reached ) {
			$options[ $this->get_disabled_key( 'limit' ) ] = '---- ' . sprintf( __( 'Limited to %s items' ), $this->get_max_items() ) . ' ----';
		}

		if ( $this->has_empty_option() ) {
			if ( count( $options ) > 0 ) {
				$options[ $this->get_disabled_key( 'divider' ) ] = '───────────';
			}
			if ( $this->get_empty() ) {
				$options['cpac_empty'] = $this->get_empty();
			}
			if ( $this->get_nonempty() ) {
				$options['cpac_nonempty'] = $this->get_nonempty();
			}
		}

		if ( $this->get_label() ) {
			$options = array( '' => $this->get_label() ) + $options;
		}

		$options = $this->encode_keys( $options );

		if ( $this->is_loading() ) {
			$options = $options + array( '__loading__' => __( 'Loading values ..', 'codepress-admin-columns' ) );
		}

		$this->selection = $options;
	}

	/**
	 * Base64 encoded keys. e.g.: allow the usage <img> tags as a value.
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	private function encode_keys( $options ) {
		$encoded = array();

		foreach ( $options as $k => $v ) {

		    // Exclude "disabled" keys from encoding.
		    if ( 0 === strpos( $k, $this->get_disabled_key() ) ) {
			    $encoded[ $k ] = $v;
		        continue;
            }

			$encoded[ base64_encode( $k ) ] = $v;
		}

		return $encoded;
	}

	/**
	 * @return array
	 */
	public function get_selection() {
		if ( null === $this->selection ) {
			$this->set_selection();
		}

		return $this->selection;
	}

	private function attr_class() {
		$classes = array( 'postform', 'acp-filter' );

		if ( $this->get_current_value() ) {
		    $classes[] = 'active';
        }

		echo implode( ' ', $classes );
	}

	private function attr_id() {
		echo esc_attr( "acp-filter-" . $this->get_name() );
	}

	/**
	 * Display HTML dropdown
	 */
	public function display() {
		if ( 1 >= count( $this->get_selection() ) ) {
			return;
		}

		?>
        <label for="<?php $this->attr_id(); ?>" class="screen-reader-text"><?php printf( __( 'Filter by %s', 'codepress-admin-columns' ), $this->get_label() ); ?></label>
        <select class="<?php $this->attr_class(); ?>" id="<?php $this->attr_id(); ?>" name="acp_filter[<?php echo esc_attr( $this->get_name() ); ?>]" data-current="<?php echo esc_attr( $this->get_current_value() ); ?>">
			<?php foreach ( $this->get_selection() as $value => $label ) : ?>
                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $this->get_current_value() ); ?><?php echo 0 === strpos( $value, $this->get_disabled_key() ) ? ' disabled' : ''; ?>>
					<?php echo esc_html( $label ); ?>
                </option>
			<?php endforeach; ?>
        </select>
		<?php
	}

}
