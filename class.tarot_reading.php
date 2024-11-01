<?php

function tarot_reading_add_scripts() {
	wp_enqueue_script('tarot_reading_js', plugins_url( '/tarot_reading.js', __FILE__ ));
	wp_enqueue_style('tarot_reading_css', plugins_url( '/tarot_reading.css', __FILE__ ));
}

class TarotReading {
	private static $initiated = false;

	public static function init() {
		if (!self::$initiated) {
			self::init_hooks();
		}
	}


	private static function init_hooks() {
		self::$initiated = true;
		add_action('wp_enqueue_scripts', 'tarot_reading_add_scripts');

		// 占いの埋め込み用
		add_shortcode('tarot_reading', function($attrs) {
			$name = self::getName($attrs);
			if (preg_match("#^\d+$#", $name) == 0) {
				ob_start();
				?><div>ショートコードのnameの設定に誤りがあります。</div><?php
				return ob_get_clean();
			}
			$urls = self::getURLs($name);
			if (empty($urls)) {
				ob_start();
				?><div>設定 - タロット占い で設定が行われていないか、ショートコードのnameの設定に誤りがあります。</div><?php
				return ob_get_clean();
			}
			$url = $urls[array_rand($urls)];
			ob_start();
			?>

			<div class="tarot_reading">
				<input type="hidden" class="tarot_reading_name" value="<?= $name ?>">
				<input type="hidden" class="tarot_reading_url" value="<?= $url ?>">
				<input type="hidden" class="tarot_reading_path" value="<?= plugins_url( '/', __FILE__ ) ?>">
				<div class="tarot_reading_cards">
					<div class="tarot_reading_step1" style="display:block"><img src="" width="652" height="910"></div>
					<div class="tarot_reading_step2" style="display:none"><img src="" width="652" height="910"></div>
					<div class="tarot_reading_step3" style="display:none"><img src="" width="652" height="910"></div>
<div>
	<!-- height="910" width="652" -->
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 172.5 240.8">
<g fill="#000000" paint-order="stroke fill markers" cursor="pointer">
<path d="M0 0h172.5v174.164H0z" opacity=".001" class="tarot_reading_rect" />
<circle r="20.129" cy="203.619" cx="29.976" opacity=".001" class="tarot_reader_icon" />
<path d="M57.739 185.088l106.061-.18V231.3H57.107v-39.586l-4.145-1.81z" opacity=".001" class="tarot_reader_balloon" />
</g>
</svg>
</div>
				</div>
			</div>

			<?php
			return ob_get_clean();
		});


		// 確認用
		add_shortcode('tarot_reading_urls', function($attrs) {
			$name = self::getName($attrs);
			if (preg_match("#^\d+$#", $name) == 0) {
				ob_start();
				?><div>ショートコードのnameの設定に誤りがあります。</div><?php
				return ob_get_clean();
			}
			$urls = self::getURLs($name);
			ob_start();
			echo "<ul>";
			foreach ($urls as $key => $url) {
				?><li><a target="_blank" href="<?= substr($url, strlen($name) + 1); ?>"><?= substr($url, strlen($name) + 1); ?></a></li><?php
			}
			echo "</ul>";
			return ob_get_clean();
		});

		add_action('template_redirect', function() {
			if (!is_page() && !is_single()) {
				return;
			}
			$ref = wp_get_referer();
			$current_url = get_permalink(get_queried_object_id());
			$urls = self::getURLs('');
			foreach ($urls as $key => $url) {
				if ($current_url == $url && (!$ref || strpos($ref, home_url()) !== 0)) {
					wp_safe_redirect(home_url());
					return;
				}
			}
		});

		add_filter('get_related_wp_query_args', function($args) {
			$ids = self::getPostIds();
			$args['post__not_in'] = $ids;
			return $args;
		});

		// add_filter('widget_new_entries_args', function($args) {
		// 	$ids = self::getPostIds();
		// 	$args['post__not_in'] = implode(',', $ids);
		// 	return $args;
		// });
		// add_filter('widget_entries_args', function($args) {
		// 	$ids = self::getPostIds();
		// 	$args['post__not_in'] = implode(',', $ids);
		// 	return $args;
		// });
		add_filter('widget_posts_args', function($args) {
			$ids = self::getPostIds();
			$args['post__not_in'] = $ids;
			return $args;
		});

		add_filter('pre_get_posts', 'TarotReading::filterPosts');
	}

	private static function getPostIds() {
		$urls = self::getURLs('');
		$ids = array();
		foreach ($urls as $key => $url) {
			$post_id = url_to_postid($url);
			if ($post_id != 0) {
				array_push($ids, $post_id);
			}
		}
		return $ids;
	}

	public static function filterPosts($query) {
		remove_filter('pre_get_posts', __METHOD__);
		$ids = self::getPostIds();
		if ($query->is_home) {
			$query->set('post__not_in', $ids);
		}
		if ($query->is_feed) {
			$query->set('post__not_in', $ids);
		}
		if (!is_admin() && $query->is_search) {
			$query->set('post__not_in', $ids);
		}
		if (!is_admin() && $query->is_archive) {
			$query->set('post__not_in', $ids);
		}
		return $query;
	}

	private static function getName($attrs) {
		$attrs = shortcode_atts(array('name' => ''), $attrs);
		$name = $attrs['name'];
		return $name;
	}

	private static function getURLs($name) {
		$json = get_option('tarot_reading');
		$setting = json_decode($json, true);
		$urls = array();

		$op = null;
		for ($i = 0; $i < count($setting); $i++) {
			if ($name == '') {
				self::extractUrls($setting[$i]['urls'], $urls);
			} else if ($setting[$i]['shortcode'] == $name) {
				self::extractUrls($setting[$i]['urls'], $urls);
				break;
			}
		}
		return $urls;
	}

	private static function extractUrls($urls, &$array) {
		$lines = explode("\n", $urls);
		foreach ($lines as $key => $line) {
			$line = trim($line);
			if (preg_match('#^https?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))$#', $line) == 1) {
				array_push($array, $line);
			}
		}
	}
}
