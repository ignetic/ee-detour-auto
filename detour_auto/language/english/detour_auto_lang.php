<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$lang = array(

	'detour_auto_description' => 'Automatically create redirects for "Detour Pro" addon when renaming an entry or category URL Title.',
	'channel_redirect_settings' => 'Channel Redirect Settings',
	'channel_settings' => 'Channel Settings',
	'channel_settings_description' => '
		<p><b>Channel URI</b>: Set this for each of the channels which you would like redirected when the entry\'s URL title is changed.</p>
		<p>You can also use <code>{entry_date format=""}</code> within the URI using <a href="http://expressionengine.com/user_guide/templates/date_variable_formatting.html" target="_blank">date variable formatting</a>.</p>
		<blockquote>
			<p><em>Example: if an entry is found at blog/article/my-blog-title, enter blog/article.</em></p>
			<p><em>If this entry\'s URL title is changed to \'new-blog-title\', a new 301 redirect would be added to Detour Pro with blog/article/new-blog-title.</em></p>
		</blockquote>
		<p><b>Wildcard</b>: If this is enabled then anything placed after the channel uri will also be redirected.</p>
		<blockquote>
			<p><em>Example: if a entry\'s content is found at blog/article/my-blog-title/comments/success and the \'my-blog-title\' URL title is changed to \'new-blog-title\', this would then be redirected to blog/article/new-blog-title/comments/success.</em></p>
			<p><em>This uses \'Wildcard Redirect with Segment Replacement (%% )\'. See http://cityzen.com/products/detour-pro</em></p>
		</blockquote>
		<p><b>Expiry Time</b>: Select this to specify when the redirect should expire. Leave blank to allow no expiry date.</p>
		<p><b>Allow Statuses</b>: Select this to only enable redirects for the selected statuses.</p>
		<p><i><b>Important</b>: only set directs for channels which you are sure require redirects and where prefix URI are constant across all the channel\'s entries.</i></p>
	',
	'channel_allowed_time' => 'Allowed Time',
	'channel_allowed_time_description' => '
		Only create detours for entries that are older than the specified time.<br>
		<em>This allows time from when the entry is created in cases where new entries may be changed multiple times before making them live.</em><br>
	',
	
	'category_redirect_settings' => 'Category Redirect Settings',
	'category_settings' => 'Category Settings',
	'category_settings_description' => '
		<p><b>Category URI</b>: Set this for each of the channel\'s category which you would like redirected when the category\'s URL title is changed.</p>
		<blockquote>
			<p><em>Example: if a category for a channel is found at blog/category/world, enter blog/category.</em></p>
			<p><em>If this category\'s URL title is changed to \'new-world\', a new 301 redirect would be added to Detour Pro with blog/category/new-world.</em></p>
		</blockquote>
		<p><b>Wildcard</b>: If this is enabled then anything placed after the category will also be redirected.</p>
		<blockquote>
			<p><em>Example: if an entry is found at blog/category/world/living and the category URL title is changed to \'new-world\', this entry would then be redirected to blog/category/new-world/living.</em></p>
			<p><em>This uses \'Wildcard Redirect with Segment Replacement (%% )\'. See http://cityzen.com/products/detour-pro</em></p>
		</blockquote>
		<p><b>Expiry Time</b> Select this to specify when the redirect should expire. Leave blank to allow no expiry date.</p>
		<p><i><b>Note</b>: only set directs for categories which you are sure require redirects.</i></p>
	',
	
	'channel_name' => 'Channel Name',
	'channel_uri' => 'Channel URI',
	'category_name' => 'Category Name',
	'category_uri' => 'Category URI',
	
	'end_date' => 'End Date',
	'expiry_date' => 'Expiry Date',
	'expiry_time' => 'Expiry Time',
	'wildcard' => 'Wildcard (/%%)',
	'allow_statuses' => 'Allow Statuses',
	
);



/* End of file lang.detour_auto.php */
/* Location: /system/expressionengine/third_party/detour_auto/language/english/lang.detour_auto.php */
