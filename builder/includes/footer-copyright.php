<?php
add_action('wp_head', function (){
	// Thay thế nội dung dưới footer
	if (BUILDER_IS_TEMPLATE) {
		$blogname = get_bloginfo('name');
		$blogname = str_ireplace(['AZ9s Team', 'W2s Team'], '', $blogname);
		update_option('blogname', $blogname);
		foreach (['az9s-com', 'web2s-vn', 'flatsome-child'] as $name) {
			$optionName = 'theme_mods_' . $name;
			$content = get_option($optionName);
			$content['site_logo'] = preg_replace('#https?\:/\/(.+?)\/#i', '/', $content['site_logo']);
			$content['footer_left_text'] = '
				© Bản quyền thuộc về ' . $blogname . '
				<span style="padding-left: 5px;margin-left: 5px;border-left: 1px solid;">
					Thiết kế bởi
					<a style="color: #bfbfbf" target="_blank" href="' . BUILDER_HOME . '">' . strtoupper(BUILDER_DOMAIN) . '</a>
				</span>
			';
			update_option($optionName, $content);
		}
	}
});
