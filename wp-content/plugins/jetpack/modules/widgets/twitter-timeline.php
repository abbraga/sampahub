<?php

/*
 * Based on Evolution Twitter Timeline
 * (https://wordpress.org/extend/plugins/evolution-twitter-timeline/)
 * For details on Twitter Timelines see:
 *  - https://twitter.com/settings/widgets
 *  - https://dev.twitter.com/docs/embedded-timelines
 */

/**
 * Register the widget for use in Appearance -> Widgets
 */
add_action( 'widgets_init', 'jetpack_twitter_timeline_widget_init' );

function jetpack_twitter_timeline_widget_init() {
	register_widget( 'Jetpack_Twitter_Timeline_Widget' );
}

class Jetpack_Twitter_Timeline_Widget extends WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'twitter_timeline',
			/** This filter is documented in modules/widgets/facebook-likebox.php */
			apply_filters( 'jetpack_widget_name', esc_html__( 'Twitter Timeline', 'jetpack' ) ),
			array(
				'classname' => 'widget_twitter_timeline',
				'description' => __( 'Display an official Twitter Embedded Timeline widget.', 'jetpack' ),
				'customize_selective_refresh' => true,
			)
		);

		if ( is_active_widget( false, false, $this->id_base ) || is_active_widget( false, false, 'monster' ) || is_customize_preview() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'jetpack-twitter-timeline' );
	}

	/**
	 * Enqueue Twitter's widget library.
	 *
	 * @deprecated
	 */
	public function library() {
		_deprecated_function( __METHOD__, '4.0.0' );
		wp_print_scripts( array( 'jetpack-twitter-timeline' ) );
	}

	/**
	 * Enqueue script to improve admin UI
	 */
	public function admin_scripts( $hook ) {
		// This is still 'widgets.php' when managing widgets via the Customizer.
		if ( 'widgets.php' === $hook ) {
			wp_enqueue_script( 'twitter-timeline-admin', plugins_url( 'twitter-timeline-admin.js', __FILE__ ) );
		}
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$instance['lang'] = substr( strtoupper( get_locale() ), 0, 2 );

		echo $args['before_widget'];

		if ( isset( $instance['title'] ) ) {
			/** This filter is documented in core/src/wp-includes/default-widgets.php */
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		// Start tag output
		// This tag is transformed into the widget markup by Twitter's
		// widgets.js code
		echo '<a class="twitter-timeline"';

		$data_attribs = array(
			'width',
			'height',
			'theme',
			'link-color',
			'border-color',
			'tweet-limit',
			'lang'
		);
		foreach ( $data_attribs as $att ) {
			if ( ! empty( $instance[ $att ] ) && ! is_array( $instance[ $att ] ) ) {
				echo ' data-' . esc_attr( $att ) . '="' . esc_attr( $instance[ $att ] ) . '"';
			}
		}

		if ( ! empty( $instance['chrome'] ) && is_array( $instance['chrome'] ) ) {
			echo ' data-chrome="' . esc_attr( join( ' ', $instance['chrome'] ) ) . '"';
		}

		$type      = ( isset( $instance['type'] ) ? $instance['type'] : '' );
		$widget_id = ( isset( $instance['widget-id'] ) ? $instance['widget-id'] : '' );
		switch ( $type ) {
			case 'profile':
				echo ' href="https://twitter.com/' . esc_attr( $widget_id ) . '"';
				break;
			case 'widget-id':
			default:
				echo ' data-widget-id="' . esc_attr( $widget_id ) . '"';
				break;
		}

		// End tag output
		echo '>';

		$timeline_placeholder = __( 'My Tweets', 'jetpack' );

		/**
		 * Filter the Timeline placeholder text.
		 *
		 * @module widgets
		 *
		 * @since 3.4.0
		 *
		 * @param string $timeline_placeholder Timeline placeholder text.
		 */
		$timeline_placeholder = apply_filters( 'jetpack_twitter_timeline_placeholder', $timeline_placeholder );

		echo esc_html( $timeline_placeholder ) . '</a>';

		// End tag output

		echo $args['after_widget'];

		/** This action is documented in modules/widgets/social-media-icons.php */
		do_action( 'jetpack_bump_stats_extras', 'widget', 'twitter_timeline' );
	}


	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		$instance['title'] = sanitize_text_field( $new_instance['title'] );

		$width = (int) $new_instance['width'];
		if ( $width ) {
			// From publish.twitter.com: 220 <= width <= 1200
			$instance['width'] = min( max( $width, 220 ), 1200 );
		} else {
			$instance['width'] = '';
		}

		$height = (int) $new_instance['height'];
		if ( $height ) {
			// From publish.twitter.com: height >= 200
			$instance['height'] = max( $height, 200 );
		} else {
			$instance['height'] = '';
		}

		$tweet_limit = (int) $new_instance['tweet-limit'];
		$instance['tweet-limit'] = ( $tweet_limit ? $tweet_limit : null );

		// If they entered something that might be a full URL, try to parse it out
		if ( is_string( $new_instance['widget-id'] ) ) {
			if ( preg_match(
				'#https?://twitter\.com/settings/widgets/(\d+)#s',
				$new_instance['widget-id'],
				$matches
			) ) {
				$new_instance['widget-id'] = $matches[1];
			}
		}

		$instance['widget-id'] = sanitize_text_field( $new_instance['widget-id'] );

		$hex_regex = '/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/';
		foreach ( array( 'link-color', 'border-color' ) as $color ) {
			$new_color = sanitize_text_field( $new_instance[ $color ] );
			if ( preg_match( $hex_regex, $new_color ) ) {
				$instance[ $color ] = $new_color;
			}

		}

		$instance['type'] = 'widget-id';
		if ( in_array( $new_instance['type'], array( 'widget-id', 'profile' ) ) ) {
			$instance['type'] = $new_instance['type'];
		}

		$instance['theme'] = 'light';
		if ( in_array( $new_instance['theme'], array( 'light', 'dark' ) ) ) {
			$instance['theme'] = $new_instance['theme'];
		}

		$instance['chrome'] = array();
		$chrome_settings = array(
			'noheader',
			'nofooter',
			'noborders',
			'transparent'
		);
		if ( isset( $new_instance['chrome'] ) ) {
			foreach ( $new_instance['chrome'] as $chrome ) {
				if ( in_array( $chrome, $chrome_settings ) ) {
					$instance['chrome'][] = $chrome;
				}
			}
		}

		return $instance;
	}

	/**
 	 * Returns a link to the documentation for a feature of this widget on
 	 * Jetpack or WordPress.com.
 	 */
	public function get_docs_link( $hash = '' ) {
		if ( defined( 'IS_WPCOM' ) && IS_WPCOM ) {
			$base_url = 'https://support.wordpress.com/widgets/twitter-timeline-widget/';
		} else {
			$base_url = 'https://jetpack.com/support/extra-sidebar-widgets/twitter-timeline-widget/';
		}
		return '<a href="' . $base_url . $hash . '" target="_blank">( ? )</a>';
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$defaults = array(
			'title'        => esc_html__( 'Follow me on Twitter', 'jetpack' ),
			'width'        => '',
			'height'       => '400',
			'widget-id'    => '',
			'link-color'   => '#f96e5b',
			'border-color' => '#e8e8e8',
			'theme'        => 'light',
			'chrome'       => array(),
			'tweet-limit'  => null,
		);

		$instance = wp_parse_args( (array) $instance, $defaults );

		if ( empty( $instance['type'] ) ) {
			// Decide the correct widget type.  If this is a pre-existing
			// widget with a numeric widget ID, then the type should be
			// 'widget-id', otherwise it should be 'profile'.
			if ( ! empty( $instance['widget-id'] ) && is_numeric( $instance['widget-id'] ) ) {
				$instance['type'] = 'widget-id';
			} else {
				$instance['type'] = 'profile';
			}
		}
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">
				<?php esc_html_e( 'Title:', 'jetpack' ); ?>
			</label>
			<input
				class="widefat"
				id="<?php echo $this->get_field_id( 'title' ); ?>"
				name="<?php echo $this->get_field_name( 'title' ); ?>"
				type="text"
				value="<?php echo esc_attr( $instance['title'] ); ?>"
			/>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'width' ); ?>">
				<?php esc_html_e( 'Maximum Width (px; 220 to 1200):', 'jetpack' ); ?>
			</label>
			<input
				class="widefat"
				id="<?php echo $this->get_field_id( 'width' ); ?>"
				name="<?php echo $this->get_field_name( 'width' ); ?>"
				type="number" min="220" max="1200"
				value="<?php echo esc_attr( $instance['width'] ); ?>"
			/>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'height' ); ?>">
				<?php esc_html_e( 'Height (px; at least 200):', 'jetpack' ); ?>
			</label>
			<input
				class="widefat"
				id="<?php echo $this->get_field_id( 'height' ); ?>"
				name="<?php echo $this->get_field_name( 'height' ); ?>"
				type="number" min="200"
				value="<?php echo esc_attr( $instance['height'] ); ?>"
			/>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'tweet-limit' ); ?>">
				<?php esc_html_e( '# of Tweets Shown:', 'jetpack' ); ?>
			</label>
			<input
				class="widefat"
				id="<?php echo $this->get_field_id( 'tweet-limit' ); ?>"
				name="<?php echo $this->get_field_name( 'tweet-limit' ); ?>"
				type="number" min="1" max="20"
				value="<?php echo esc_attr( $instance['tweet-limit'] ); ?>"
			/>
		</p>

		<p class="jetpack-twitter-timeline-widget-type-container">
			<label for="<?php echo $this->get_field_id( 'type' ); ?>">
				<?php esc_html_e( 'Widget Type:', 'jetpack' ); ?>
				<?php echo $this->get_docs_link( '#widget-type' ); ?>
			</label>
			<select
				name="<?php echo $this->get_field_name( 'type' ); ?>"
				id="<?php echo $this->get_field_id( 'type' ); ?>"
				class="jetpack-twitter-timeline-widget-type widefat"
			>
				<option value="profile"<?php selected( $instance['type'], 'profile' ); ?>>
					<?php esc_html_e( 'Profile', 'jetpack' ); ?>
				</option>
				<option value="widget-id"<?php selected( $instance['type'], 'widget-id' ); ?>>
					<?php esc_html_e( 'Widget ID', 'jetpack' ); ?>
				</option>
			</select>
		</p>

		<p class="jetpack-twitter-timeline-widget-id-container">
			<label
				for="<?php echo $this->get_field_id( 'widget-id' ); ?>"
				data-widget-type="widget-id"
				<?php echo ( 'widget-id' === $instance['type'] ? '' : 'style="display: none;"' ); ?>
			>
				<?php esc_html_e( 'Widget ID:', 'jetpack' ); ?>
				<?php echo $this->get_docs_link( '#widget-id' ); ?>
			</label>
			<label
				for="<?php echo $this->get_field_id( 'widget-id' ); ?>"
				data-widget-type="profile"
				<?php echo ( 'profile' === $instance['type'] ? '' : 'style="display: none;"' ); ?>
			>
				<?php esc_html_e( 'Twitter Username:', 'jetpack' ); ?>
				<?php echo $this->get_docs_link( '#twitter-username' ); ?>
			</label>
			<input
				class="widefat"
				id="<?php echo $this->get_field_id( 'widget-id' ); ?>"
				name="<?php echo $this->get_field_name( 'widget-id' ); ?>"
				type="text"
				value="<?php echo esc_attr( $instance['widget-id'] ); ?>"
			/>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'chrome-noheader' ); ?>">
				<?php esc_html_e( 'Layout Options:', 'jetpack' ); ?>
			</label>
			<br />
			<input
				type="checkbox"<?php checked( in_array( 'noheader', $instance['chrome'] ) ); ?>
				id="<?php echo $this->get_field_id( 'chrome-noheader' ); ?>"
				name="<?php echo $this->get_field_name( 'chrome' ); ?>[]"
				value="noheader"
			/>
			<label for="<?php echo $this->get_field_id( 'chrome-noheader' ); ?>">
				<?php esc_html_e( 'No Header', 'jetpack' ); ?>
			</label>
			<br />
			<input
				type="checkbox"<?php checked( in_array( 'nofooter', $instance['chrome'] ) ); ?>
				id="<?php echo $this->get_field_id( 'chrome-nofooter' ); ?>"
				name="<?php echo $this->get_field_name( 'chrome' ); ?>[]"
				value="nofooter"
			/>
			<label for="<?php echo $this->get_field_id( 'chrome-nofooter' ); ?>">
				<?php esc_html_e( 'No Footer', 'jetpack' ); ?>
			</label>
			<br />
			<input
				type="checkbox"<?php checked( in_array( 'noborders', $instance['chrome'] ) ); ?>
				id="<?php echo $this->get_field_id( 'chrome-noborders' ); ?>"
				name="<?php echo $this->get_field_name( 'chrome' ); ?>[]"
				value="noborders"
			/>
			<label for="<?php echo $this->get_field_id( 'chrome-noborders' ); ?>">
				<?php esc_html_e( 'No Borders', 'jetpack' ); ?>
			</label>
			<br />
			<input
				type="checkbox"<?php checked( in_array( 'transparent', $instance['chrome'] ) ); ?>
				id="<?php echo $this->get_field_id( 'chrome-transparent' ); ?>"
				name="<?php echo $this->get_field_name( 'chrome' ); ?>[]"
				value="transparent"
			/>
			<label for="<?php echo $this->get_field_id( 'chrome-transparent' ); ?>">
				<?php esc_html_e( 'Transparent Background', 'jetpack' ); ?>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'link-color' ); ?>">
				<?php _e( 'Link Color (hex):', 'jetpack' ); ?>
			</label>
			<input
				class="widefat"
				id="<?php echo $this->get_field_id( 'link-color' ); ?>"
				name="<?php echo $this->get_field_name( 'link-color' ); ?>"
				type="text"
				value="<?php echo esc_attr( $instance['link-color'] ); ?>"
			/>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'border-color' ); ?>">
				<?php _e( 'Border Color (hex):', 'jetpack' ); ?>
			</label>
			<input
				class="widefat"
				id="<?php echo $this->get_field_id( 'border-color' ); ?>"
				name="<?php echo $this->get_field_name( 'border-color' ); ?>"
				type="text"
				value="<?php echo esc_attr( $instance['border-color'] ); ?>"
			/>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'theme' ); ?>">
				<?php _e( 'Timeline Theme:', 'jetpack' ); ?>
			</label>
			<select
				name="<?php echo $this->get_field_name( 'theme' ); ?>"
				id="<?php echo $this->get_field_id( 'theme' ); ?>"
				class="widefat"
			>
				<option value="light"<?php selected( $instance['theme'], 'light' ); ?>>
					<?php esc_html_e( 'Light', 'jetpack' ); ?>
				</option>
				<option value="dark"<?php selected( $instance['theme'], 'dark' ); ?>>
					<?php esc_html_e( 'Dark', 'jetpack' ); ?>
				</option>
			</select>
		</p>
	<?php
	}
}
