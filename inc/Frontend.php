<?php

namespace Inc;

class Frontend
{
	protected string $plugin_name;
	protected array $posts = [];
	protected string $type_post = 'slider-kindred';
	
	
	public function __construct($plugin_name)
	{
		$this->plugin_name = $plugin_name;
	}
	
	protected function getPosts()
	{
		if (!$this->posts) {
			
			$query = new \WP_Query([
				                       'offset'         => 0,
				                       'post_type'      => $this->type_post,
				                       'posts_per_page' => -1,
				                       'orderby'        => 'meta_value_num',
				                       'meta_key'        => '_carousel_slider_order_value',
				                       'order'          => 'ASC',
			
			                       ]);
			$this->posts = $query->get_posts();
		}
		
		return $this->posts;
	}
	
	function build_slider()
	{
		$posts = $this->getPosts();
		
		$html = '<div class="splide"><div class="splide__track"><ul class="splide__list">';
		foreach ($posts as $post) {
			$link = get_post_meta($post->ID,'_carousel_slider_slide_link_value');
			$target = get_post_meta($post->ID,'_carousel_slider_slide_link_target_value');
			
			$image_src = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), '900x408');
			$button = '';
			if($link) $button = '<a class="btn btn-primary" target="' .$target[0] .'" href="' . $link[0] . '">Click</a>';
			$html .= '<li class="splide__slide">
                        <img title="' . $post->post_title . '" src="' . $image_src[0] . '" data-thumb="' . $image_src[0] . '" alt="' . $post->post_title . '"/>
                        <div class="meta"><h3 class="texto-encima">'.$post->post_title .'</h3>
                        <p class="centrado">'.$post->post_content .'</p>' . $button . '</div></li>';
		}
		
		$html .= '</ul></div></div>';
		return $html;
	}
	
	function custom_post_type()
	{
		register_post_type($this->type_post, [
			'public'            => true,
			'label'             => $this->type_post,
			'show_ui'           => true,
			'show_in_menu'      => true,
			'show_in_nav_menus' => true,
			'show_in_admin_bar' => true,
			'menu_position'     => 5,
			'menu_icon'         => 'dashicons-slides',
			'supports'          => [
				'title',
				'thumbnail',
				'editor'
			]
		]);
		
		add_theme_support('post-thumbnails', [$this->type_post]);
		add_image_size('450x204', 450, 204, true);
		add_image_size('600x306', 600, 306, true);
		add_image_size('900x408', 900, 408, true);
		
		
	}
	
	public function add_meta_box()
	{
		add_meta_box(
			'slider_kindred_id', 'slider kindred meta box',
			[
				$this,
				'carousel_slider_meta_box_callback'
			]
		);
	}
	
	public function save_meta_box($post_id)
	{
		
		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */
		
		// Check if our nonce is set.
		if (!isset($_POST['carousel_slider_meta_box_nonce']))
			return $post_id;
		
		$nonce = $_POST['carousel_slider_meta_box_nonce'];
		
		// Verify that the nonce is valid.
		if (!wp_verify_nonce($nonce, 'carousel_slider_inner_custom_box'))
			return $post_id;
		
		// If this is an autosave, our form has not been submitted,
		//     so we don't want to do anything.
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			return $post_id;
		
		// Check the user's permissions.
		if ('page' == $_POST['post_type']) {
			
			if (!current_user_can('edit_page', $post_id))
				return $post_id;
			
		} else {
			
			if (!current_user_can('edit_post', $post_id))
				return $post_id;
		}
		
		/* OK, its safe for us to save the data now. */
		
		// Sanitize the user input.
		$link_target = sanitize_text_field($_POST['carousel_slider_slide_link_target']);
		$order = sanitize_text_field($_POST['carousel_slider_order']);
		
		if ((trim($_POST['carousel_slider_slide_link'])) != '') {
			
			$carousel_link = esc_url($_POST['carousel_slider_slide_link']);
			
		} else {
			$carousel_link = esc_url(get_permalink());
		}
		
		// Update the meta field.
		update_post_meta($post_id, '_carousel_slider_slide_link_value', $carousel_link);
		update_post_meta($post_id, '_carousel_slider_slide_link_target_value', $link_target);
		update_post_meta($post_id, '_carousel_slider_order_value', $order);
	}
	
	
	public function carousel_slider_meta_box_callback($post)
	{
		
		// Add an nonce field so we can check for it later.
		wp_nonce_field('carousel_slider_inner_custom_box', 'carousel_slider_meta_box_nonce');
		
		// Use get_post_meta to retrieve an existing value from the database.
		$carousel_link = get_post_meta($post->ID, '_carousel_slider_slide_link_value', true);
		$link_target = get_post_meta($post->ID, '_carousel_slider_slide_link_target_value', true);
		$order = get_post_meta($post->ID, '_carousel_slider_order_value', true);
		
		?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="carousel_slider_slide_link">
                        Slider link
                    </label>
                </th>
                <td>
                    <input type="text" class="regular-text" id="carousel_slider_slide_link"
                           name="carousel_slider_slide_link" value="<?php echo esc_attr($carousel_link); ?>"
                           style="width:100% !important">
                    <p>Write slide link URL. If you want to use current slide link, just leave it blank. If you do not
                        want any link write (#) without bracket or write desired link..</p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="carousel_slider_slide_link_target">Link Target</label>
                </th>
                <td>
                    <select name="carousel_slider_slide_link_target">
                        <option value="_self" <?php selected($link_target, '_self'); ?>>Self</option>
                        <option value="_blank" <?php selected($link_target, '_blank'); ?>>Blank</option>
                    </select>
                    <p>Select Self to open the slide in the same frame as it was clicked (this is default) or select
                        Blank open the slide in a new window or tab.</p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="carousel_slider_order">
                        Order
                    </label>
                </th>
                <td>
                    <input placeholder="1" type="number" class="regular-text" id="carousel_slider_order"
                           name="carousel_slider_order" value="<?php echo esc_attr($order); ?>"
                           style="width:100% !important">
                    <p>select a order to show </p>
                </td>
            </tr>
        </table>
		<?php
	}
	
	function enqueue()
	{
		// enqueue all our scripts
		wp_enqueue_style('splide-css', plugins_url('../lib/css/splide.min.css', __FILE__));
		wp_enqueue_style('splide-default', plugins_url('../lib/css/themes/splide-default.min.css', __FILE__));
		wp_enqueue_style('splide-custom', plugins_url('../assets/css/style.css', __FILE__));
		
		if (!is_admin()) {
			wp_enqueue_script('splide-js', plugins_url('../lib/js/splide.js', __FILE__));
			wp_enqueue_script('custom-js', plugins_url('../assets/scripts/custom.js', __FILE__));
		}
		
	}
}