<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Detour Auto Extension
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Extension
 * @author		Simon Andersohn
 * @link		
 */
 
include_once PATH_THIRD.'detour_auto/config.php';

class Detour_auto_ext {
	
	public $settings		= array();
	public $description		= DETOUR_AUTO_DESCRIPTION;
	public $docs_url		= DETOUR_AUTO_DOCS_URL;
	public $name			= DETOUR_AUTO_NAME;
	public $settings_exist	= 'y';
	public $version			= DETOUR_AUTO_VERSION;
	
	private $EE;
	private $EE2;
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		$this->EE = get_instance();
		$this->site_id = ($this->EE->config->item('site_id')) ? $this->EE->config->item('site_id') : 1;
		
		if (defined('APP_VER') && version_compare(APP_VER, '3.0.0', '<'))
		{
			$this->EE2 = TRUE;
		}
		
		$this->settings = isset($settings[$this->site_id]) ? $settings[$this->site_id] : array();
		
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Settings Form
	 */
    public function settings_form($settings)
    {

		$this->EE->load->library('table');
		$this->EE->lang->loadfile('detour_auto');
		$this->EE->load->model('status_model');

        // MSM compatibility
		$vars = isset($settings[$this->site_id]) ? $settings[$this->site_id] : array();
		
		// Dates select menu
		$fields['date_list'] = array(
			'' => '',
			'+1 day' => '+1 day',
			'+2 days' => '+2 days',
			'+3 days' => '+3 days',
			'+1 week' => '+1 week',
			'+2 weeks' => '+2 weeks',
			'+3 weeks' => '+3 weeks',
			'+1 month' => '+1 month',
			'+2 months' => '+2 months',
			'+3 months' => '+3 months',
			'+6 months' => '+6 months',
			'+1 year' => '+1 year'
		);
		
		$fields['allowed_time_list'] = array(
			'' => '',
			'+1 hour' => '+1 hour',
			'+2 hours' => '+2 hours',
			'+3 hours' => '+3 hours',
			'+6 hours' => '+6 hours',
			'+12 hours' => '+12 hours',
			'+1 day' => '+1 day',
			'+2 days' => '+2 days',
			'+3 days' => '+3 days',
			'+5 days' => '+5 days'
		);
		
		
		$fields['channel_allowed_time'] = isset($vars['channel_allowed_time']) ? $vars['channel_allowed_time'] : '';
		
		// Channel URL settings
		$fields['channel_settings'] = array();
		
		$this->EE->db->select('*')
				->from('channels')
				->where('site_id', $this->site_id)
				->order_by('channel_title', 'asc');
		$query = $this->EE->db->get();
		
		foreach($query->result_array() as $row)
		{
			
			$channel_url_path = parse_url($row['channel_url']);
			$channel_uri = isset($vars['channel_url'][$row['channel_id']]['uri']) ? trim($vars['channel_url'][$row['channel_id']]['uri'], '/') : '';
			
			if (substr($channel_uri, -2) == '%%')
			{
				$channel_uri = rtrim(rtrim($channel_uri, '%%'), '/');
				$vars['channel_url'][$row['channel_id']]['wildcard'] = 'y';
			}

			$fields['channel_settings'][$row['channel_id']]['uri'] = array(
                'name' 		=> 'channel_url['.$row['channel_id'].'][uri]',
                'label' 	=> $row['channel_title'],
                'type'		=> 'input',
				'value'		=> $channel_uri,
				'placeholder'	=> trim($channel_url_path['path'], '/'),
			);
			$fields['channel_settings'][$row['channel_id']]['wildcard'] = array(
                'name' 		=> 'channel_url['.$row['channel_id'].'][wildcard]',
                'label' 	=> lang('wildcard'),
                'type'		=> 'checkbox',
				'value'		=> (isset($vars['channel_url'][$row['channel_id']]['wildcard']) && !empty($channel_uri)) ? $vars['channel_url'][$row['channel_id']]['wildcard'] : 'n',
			);
			$fields['channel_settings'][$row['channel_id']]['date'] = array(
                'name' 		=> 'channel_url['.$row['channel_id'].'][date]',
                'label' 	=> lang('expiry_time'),
                'type'		=> 'dropdown',
				'value'		=> (isset($vars['channel_url'][$row['channel_id']]['date']) && !empty($channel_uri)) ? $vars['channel_url'][$row['channel_id']]['date'] : '',
			);
			
			$statuses = array();
			$statuses['open'] = lang('open');
			$statuses['closed'] = lang('closed');
			
			// EE4 stores statuses in separate table
			if (ee()->db->table_exists('channels_statuses'))
			{
				$this->EE->db->select('status')
						->join('channels_statuses', 'channels_statuses.status_id = statuses.status_id', 'inner')
						->where('channel_id', $row['channel_id'])
						->order_by('status_order', 'ASC')
						->distinct();

				$query = $this->EE->db->get('statuses');

			}
			else
			{	
				$query = $this->EE->status_model->get_statuses('', $row['channel_id']);
			}
			
			if ($query->num_rows())
			{
				foreach ($query->result_array() as $status)
				{
					$status_name = ($status['status'] == 'open' OR $status['status'] == 'closed') ? lang($status['status']) : $status['status'];
					$statuses[form_prep($status['status'])] = form_prep($status_name);
				}
			}
			
			$fields['channel_settings'][$row['channel_id']]['statuses'] = array(
				'name'		=> 'channel_url['.$row['channel_id'].'][statuses]',
				'label'		=> lang('statuses'),
				'type'		=> 'dropdown',
				'data'		=> $statuses,
				'value'		=> (isset($vars['channel_url'][$row['channel_id']]['statuses'])) ? $vars['channel_url'][$row['channel_id']]['statuses'] : array('open')
			);
			
		}
		
		
		// Category Groups
		$cat_query = $this->EE->db->select('*')
				->from('category_groups')
				->where('site_id', $this->site_id)
				->get();
		
		foreach($cat_query->result_array() as $cat_row)
		{
			$cat_group_names[$cat_row['group_id']] = $cat_row['group_name'];
		}
		
		// Category URL settings
		$fields['category_settings'] = array();
		
		$this->EE->db->select('*')
				->from('channels')
				->where('site_id', $this->site_id)
				->where('cat_group <> ""')
				->order_by('channel_title', 'asc');
		$query = $this->EE->db->get();
		
		foreach($query->result_array() as $row)
		{
			$cat_groups = explode('|', $row['cat_group']);
			
			$fields['category_settings'][$row['channel_id']]['channel_title'] = $row['channel_title']. ' ('.$row['channel_name'].')';
			
			foreach($cat_groups as $group_id)
			{
				$category_uri = isset($vars['category_url'][$row['channel_id']]['channel_categories'][$group_id]['uri']) ? trim($vars['category_url'][$row['channel_id']]['channel_categories'][$group_id]['uri'], '/') : '';
				if (substr($category_uri, -2) == '%%')
				{
					$category_uri = rtrim(rtrim($category_uri, '%%'), '/');
					$vars['category_url'][$row['channel_id']]['channel_categories'][$group_id]['wildcard'] = 'y';
				}
			
				$fields['category_settings'][$row['channel_id']]['channel_categories'][$group_id]['uri'] = array(
					'name' 		=> 'category_url['.$row['channel_id'].'][channel_categories]['.$group_id.'][uri]',
					'label' 	=> 'Category Group: '.$cat_group_names[$group_id],
					'type'		=> 'input',
					'value'		=> $category_uri,
				);
				$fields['category_settings'][$row['channel_id']]['channel_categories'][$group_id]['wildcard'] = array(
					'name' 		=> 'category_url['.$row['channel_id'].'][channel_categories]['.$group_id.'][wildcard]',
					'label' 	=> lang('wildcard'),
					'type'		=> 'checkbox',
					'value'		=> (isset($vars['category_url'][$row['channel_id']]['channel_categories'][$group_id]['wildcard']) && !empty($category_uri)) ? $vars['category_url'][$row['channel_id']]['channel_categories'][$group_id]['wildcard'] : 'n',
				);
				$fields['category_settings'][$row['channel_id']]['channel_categories'][$group_id]['date'] = array(
					'name' 		=> 'category_url['.$row['channel_id'].'][channel_categories]['.$group_id.'][date]',
					'label' 	=> lang('expiry_time'),
					'type'		=> 'dropdown',
					'value'		=> (isset($vars['category_url'][$row['channel_id']]['channel_categories'][$group_id]['date']) && !empty($category_uri)) ? $vars['category_url'][$row['channel_id']]['channel_categories'][$group_id]['date'] : '',
				);
			}
		}
		
		$this->_settings_url = $this->EE2 ? 'C=addons_extensions'.AMP.'M=save_extension_settings'.AMP.'file=detour_auto' : ee('CP/URL', 'addons/settings/detour_auto/save'); 
		
		$fields['settings_url'] = $this->_settings_url;

		// Load it up and return it for rendering
		return $this->EE->load->view('settings_form', $fields, TRUE);
    }
	
	
	/**
	 * Save Settings
	 */
	public function save_settings()
    {
	
		if (empty($_POST))
		{
			show_error(lang('unauthorized_access'));
		}
		
		unset($_POST['submit']);
		
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->update('extensions', array('settings' => serialize(array($this->site_id => $_POST))));
	
		$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('preferences_updated'));
		
		$this->_base_url = $this->EE2 ? BASE.AMP.'C=addons_extensions'.AMP.'M=extension_settings'.AMP.'file=detour_auto' : ee('CP/URL', 'addons/settings/detour_auto'); 
		
		$redirect_url = $this->_base_url;
		
		$this->EE->functions->redirect($redirect_url);
		
	}
	
	
	// ----------------------------------------------------------------------
	
	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see http://codeigniter.com/user_guide/database/index.html for
	 * more information on the db class.
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		// Setup custom settings in this array.
		$this->settings = array();
		
		if ($this->EE2)
		{
			$hooks = array(
				'entry_submission_ready'	=> 'entry_submission_ready', // entry_submission_ready($meta, $data, $autosave)
				'delete_entries_loop'		=> 'delete_entries_loop', // delete_entries_loop($val, $channel_id)
				'category_save'				=> 'category_save', // category_save($cat_id, $data)
				'category_delete'			=> 'category_delete', // category_delete($cat_ids)
				'cp_js_end'					=> 'cp_js_end',
			);
		}
		else
		{
			$hooks = array(
				'after_channel_entry_update'			=> 'after_channel_entry_update', // after_channel_entry_update($entry, $values, $modified)
				'after_channel_entry_delete'			=> 'after_channel_entry_delete', // after_channel_entry_delete($entry, $values)
					//'after_channel_entry_bulk_delete'		=> 'delete_entries_loop', // EE4+ after_channel_entry_bulk_delete($delete_ids)
				'after_category_update'					=> 'after_category_update', // after_category_update($category, $values, $modified)
				'after_category_delete'					=> 'after_category_delete', // after_category_delete($category, $values)
					//'after_category_bulk_delete'			=> 'category_delete_loop', // EE4+ after_category_bulk_delete($delete_ids)
				//'cp_js_end'							=> 'cp_js_end',
			);	
		}

		foreach ($hooks as $hook => $method)
		{
			$data = array(
				'class'		=> __CLASS__,
				'method'	=> $method,
				'hook'		=> $hook,
				'settings'	=> serialize($this->settings),
				'version'	=> $this->version,
				'enabled'	=> 'y'
			);

			$this->EE->db->insert('extensions', $data);			
		}
	}	


	// ----------------------------------------------------------------------
	
	/**
	 * entry_submission_ready
	 *
	 * @param $meta (array) – Entry’s metadata (channel_id, entry_date, i.e. fields for exp_channel_titles)
	 * @param $data (array) – Entry’s field data
	 * @param $autosave (boolean) – TRUE if the submission is a non-publishing autosave
	 * @return void
	 */
	public function entry_submission_ready($meta, $data, $autosave)
	{
		if ($autosave === TRUE) return;
		
		$vars['entry_id'] = $data['entry_id'];
		$vars['channel_id'] = $data['channel_id'];
		$vars['url_title'] = $meta['url_title'];
		$vars['entry_date'] = $meta['entry_date'];
		
		$orig = array();
		
		// Get original data before it's been saved
		$this->EE->db->select('url_title, channel_id, entry_date')
						->where('entry_id', $data['entry_id'])
						->where('site_id', $this->site_id);
		
		$query = $this->EE->db->get('channel_titles');
		
		foreach($query->result_array() as $row)
		{
			$orig['channel_id'] = $row['channel_id'];
			$orig['url_title'] = $row['url_title'];
			$orig['entry_date'] = $row['entry_date'];
		}
		
		$this->entry_update($vars, $orig);
		
	}
	
	/**
	 * after_channel_entry_update
	 *
	 * @param $entry (object) – Current ChannelEntry model object
	 * @param $values (array) – The ChannelEntry model object data as an array
	 * @param $modified (array) – An array of all the old values that were changed
	 * @return void
	 */
	public function after_channel_entry_update($entry, $values, $modified)
	{

		$vars['entry_id'] = $values['entry_id'];
		$vars['channel_id'] = $values['channel_id'];
		$vars['url_title'] = $values['url_title'];
		$vars['entry_date'] = $values['entry_date'];
		
		$orig['channel_id'] = isset($modified['channel_id']) ? $modified['channel_id'] : $values['channel_id'];
		$orig['url_title'] = isset($modified['url_title']) ? $modified['url_title'] : $values['url_title'];
		$orig['entry_date'] = isset($modified['entry_date']) ? $modified['entry_date'] : $values['entry_date'];

		$this->entry_update($vars, $orig);
		
	}

	/**
	 * entry_update
	 *
	 * @param $data (array) – The ChannelEntry data as an array
	 * @param $modified (array) – An array of all the old values that were changed
	 * @return void
	 */
	public function entry_update($data, $modified)
	{

//@ Consider catering for structure, pages & template routes
		
		if (!isset($data['entry_id'])) return; 
		
		// Get statuses
		$statuses = array();
		if ( (isset($this->settings['channel_url'][$data['channel_id']]['statuses']) && is_array($this->settings['channel_url'][$data['channel_id']]['statuses'])) )
		{
			$statuses = $this->settings['channel_url'][$data['channel_id']]['statuses'];
		}
		
		// Check if entry exists
		$this->EE->db->select('url_title, channel_id, entry_date')
						->where('entry_id', $data['entry_id'])
						->where('site_id', $this->site_id);
		
		if (!empty($statuses))
		{
			$this->EE->db->where_in('status', $statuses);
		}
		
		$query = $this->EE->db->get('channel_titles');
				
		if ($query->num_rows() == 0)
		{
			// We can check to see if a redirect already exists for this url - double check that this is a new entry:
			$query = $this->EE->db->select('url_title, channel_id')
					->from('channel_titles')
					->where('url_title', $data['url_title'])
					->where('site_id', $this->site_id)
					->get();

			//  As this is a new entry, we can remove (or end) the old redirect.
			if ($query->num_rows() == 0)
			{
				if ( (isset($this->settings['channel_url'][$data['channel_id']]['uri']) && !empty($this->settings['channel_url'][$data['channel_id']]['uri'])) )
				{
					$entry_url = trim($this->settings['channel_url'][$data['channel_id']]['uri'],'/').'/'.$data['url_title'];
			
					// Change the End Date of the redundant redirect to yesterday!
					//$this->EE->db->where("(original_url = '".$entry_url."' OR original_url = '".$entry_url."/%%') 
					//	AND site_id = ".$this->site_id);
					$this->EE->db->update(
						'detours',
						// update
						array(
							'end_date' => date('Y-m-d', strtotime('-1 day'))
						),
						// where
						array(
							'original_url'  => $entry_url,
							'site_id'  => $this->site_id
						)
					);
				}
			}

			return;
		}
	
		if (empty($modified)) return; 
		
		$orig_channel_id = $modified['channel_id'];
		$orig_url_title = $modified['url_title'];
		$orig_entry_date = $modified['entry_date'];


		// Allow time from when the entry is created - in cases where new entries have been changed multiple times
		// This won't affect search engines in a short amount of time that the entry is live.
		if (isset($this->settings['channel_allowed_time']) && !empty($this->settings['channel_allowed_time']))
		{
			if (strtotime($this->settings['channel_allowed_time'], $data['entry_date']) > ee()->localize->now)
			{
				return;
			}
		}

		// Has any of this changed?
		if ($orig_url_title != $data['url_title'] || $orig_channel_id != $data['channel_id'] || $orig_entry_date != $data['entry_date'])
		{
		
			// Create url from channel settings
			// Also allow for channel changes

			if ( (isset($this->settings['channel_url'][$data['channel_id']]['uri']) && !empty($this->settings['channel_url'][$data['channel_id']]['uri']))
				&& (isset($this->settings['channel_url'][$orig_channel_id]['uri']) && !empty($this->settings['channel_url'][$orig_channel_id]['uri'])) )
			{

				$channel_time = isset($this->settings['channel_url'][$orig_channel_id]['date']) ? $this->settings['channel_url'][$orig_channel_id]['date'] : '';
				
				$start_date = strtotime('now'); // $data['entry_date']; //@CHECK: should this not be NOW?
				$end_date = !empty($channel_time) ? strtotime($channel_time) : '';
				$uri = trim($this->settings['channel_url'][$data['channel_id']]['uri'],'/');
				
				// Check for wildcards
				$wildcard = ((isset($this->settings['channel_url'][$orig_channel_id]['wildcard']) && $this->settings['channel_url'][$orig_channel_id]['wildcard'] == 'y')) ? TRUE : FALSE;

				if (substr($uri, -2) == '%%')
				{
					$parsed_uri = parse_uri_variables($uri);
					$original_url = rtrim(rtrim($uri, '%%'), '/').'/'.$orig_url_title;
					$new_url = rtrim(rtrim($uri, '%%'), '/').'/'.$data['url_title'];
					$wildcard = TRUE;
				}
				else
				{
					$original_url = $uri.'/'.$orig_url_title;
					$new_url = $uri.'/'.$data['url_title'];
				}
				
				// add any dates to uri
				$original_url = $this->parse_date($original_url, $orig_entry_date);
				$new_url = $this->parse_date($new_url, $data['entry_date']);

				// Check if uri has changed
				if ($original_url == $new_url)
				{
					return;
				}

				// Add redirects
				$this->update_detours($original_url, $new_url, $start_date, $end_date, FALSE);

				// If wildcard redirects exist, add them too
				if ($wildcard === TRUE)
				{
					$this->update_detours($original_url, $new_url, $start_date, $end_date, $wildcard);
				}				
	

			}
		}

		return; 
		
	}


	// ----------------------------------------------------------------------
	
	
	/**
	 * category_update
	 *
	 * @param $cat_id (int) – ID of category saved, $category_data (array) – Category meta data
	 * @return Void 
	 */
	public function category_save($cat_id, $data)
	{
		
		$modified['cat_url_title'] = $this->EE->input->post('orig_cat_url_title');
		
		$this->category_update($data, $modified);
		
	}
	
	/**
	 * after_category_update
	 *
	 * @param $category (object) – Current Category model object
	 * $values (array) – The Category model object data as an array
	 * $modified (array) – An array of all the old values that were changed
	 * @return Void 
	 */
	public function after_category_update($category, $values, $modified)
	{

		$data['cat_id'] = $values['cat_id'];
		$data['group_id'] = $values['group_id'];
		$data['cat_url_title'] = $values['cat_url_title'];

		$this->category_update($data, $modified);
		
	}
	
	/**
	 * category_update
	 *
	 * @param $data (array) – Category meta data
	 * @return Void 
	 */
	public function category_update($data, $modified)
	{

		// This relies on javascript so check this javascript created field exists before doing anything...
		/*if ( ! isset($_POST['detour_cat_enabled']) || $_POST['detour_cat_enabled'] != "y")
		{
			return;
		}*/

		// Categories can be used within multiple channels, so cater for them here too
		$channel_query = $this->EE->db->select('*')
				->from('channels')
				->where('site_id', $this->site_id)
				->where('cat_group <> ""')
				->order_by('channel_title', 'asc')
				->get();
		
		foreach($channel_query->result_array() as $channel_data)
		{
			$cat_groups = explode('|', $channel_data['cat_group']);

			foreach($cat_groups as $group_id)
			{
				
				if ( isset($this->settings['category_url'][$channel_data['channel_id']]['channel_categories'][$group_id]['uri']) && !empty($this->settings['category_url'][$channel_data['channel_id']]['channel_categories'][$group_id]['uri'])
						&& $group_id == $data['group_id']
				)
				{
				
					// Category URL settings
					$uri = trim($this->settings['category_url'][$channel_data['channel_id']]['channel_categories'][$group_id]['uri'] ,'/');
				
					if (isset($modified['cat_url_title']) && ! empty($modified['cat_url_title']) )
					{
						$orig_cat_url_title = $modified['cat_url_title'];
						
						$wildcard = FALSE;
						
						if (substr($uri, -2) == '%%')
						{
							$original_url = rtrim(rtrim($uri, '%%'), '/').'/'.$orig_cat_url_title;
							$new_url = rtrim(rtrim($uri, '%%'), '/').'/'.$data['cat_url_title'];
							$wildcard = TRUE;
						}
						else
						{
							$original_url = $uri.'/'.$orig_cat_url_title;
							$new_url = $uri.'/'.$data['cat_url_title'];
						}
						
						$wildcard = (isset($this->settings['category_url'][$channel_data['channel_id']]['channel_categories'][$group_id]['wildcard']) && $this->settings['category_url'][$channel_data['channel_id']]['channel_categories'][$group_id]['wildcard'] == 'y') ? TRUE : $wildcard;
						$channel_time = isset($this->settings['category_url'][$channel_data['channel_id']]['channel_categories'][$group_id]['date']) ? $this->settings['category_url'][$channel_data['channel_id']]['channel_categories'][$group_id]['date'] : '';

						$start_date = strtotime('now');
						$end_date = !empty($channel_time) ? strtotime($channel_time) : '';
						
						// Add redirects
						$this->update_detours($original_url, $new_url, $start_date, $end_date, FALSE);

						// If wildcard redirects exist, add them too
						if ($wildcard === TRUE)
						{
							$this->update_detours($original_url, $new_url, $start_date, $end_date, $wildcard);
						}
					}
					else
					{
						// Remove redirect if this new entry is replacing the url
						$new_url = $uri.'/'.$data['cat_url_title'];

						$this->EE->db->update(
							'detours',
							// update
							array(
								'end_date' => date('Y-m-d', strtotime('-1 days'))
							),
							// where
							array(
								'original_url'  => $new_url,
								'site_id'  => $this->site_id
							)
						);
					}

				}
			}
		}
	}
	

	
	// ----------------------------------------------------------------------
	
	/**
	 * delete_entries_loop
	 *
	 * If there is nothing to redirect to after deleting, there would be no need to redirect to it. So delete them.
	 *
	 * @param $val(int), $channel_id(int)
	 * @return Void 
	 */
	function delete_entries_loop($val, $channel_id)
	{
		$url_title = '';
		
		$query = $this->EE->db->select('url_title, channel_id')
				->from('channel_titles')
				->where('entry_id', $val)
				->where('site_id', $this->site_id)
				->get();
		
		foreach($query->result_array() as $row)
		{
			$url_title = $row['url_title'];
		}
		
		$this->entry_delete($val, $channel_id, $url_title);
	}
	
	/**
	 * after_channel_entry_delete
	 *
	 * If there is nothing to redirect to after deleting, there would be no need to redirect to it. So delete them.
	 *
	 * @param $entry (object) – Current ChannelEntry model object
	 * $values (array) – The ChannelEntry model object data as an array
	 * @return Void 
	 */
	function after_channel_entry_delete($entry, $values)
	{
		$this->entry_delete($values['entry_id'], $values['channel_id'], $values['url_title']);
	}
		
	/**
	 * entry_delete
	 *
	 * If there is nothing to redirect to after deleting, there would be no need to redirect to it. So delete them.
	 *
	 * @param $val(int), $channel_id(int)
	 * @return Void 
	 */
	function entry_delete($entry_id, $channel_id, $url_title='')
	{
		if ( ! empty($url_title)
			&& (isset($this->settings['channel_url'][$channel_id]['uri']) && !empty($this->settings['channel_url'][$channel_id]['uri']))
			&& (isset($this->settings['channel_url'][$channel_id]['uri']) && !empty($this->settings['channel_url'][$channel_id]['uri'])) )
		{
		
			$entry_url = trim($this->settings['channel_url'][$channel_id]['uri'],'/').'/'.$url_title;
		
			// remove this entry
			$this->EE->db->delete(
				'detours',
				array(
					'new_url' => $entry_url,
					'site_id'  => $this->site_id
				)
			);
		
		}
	
	}


	// ----------------------------------------------------------------------
	
	/**
	 * category_delete
	 *
	 * A bit pointless but if there is nothing to redirect to after deleting, then why would be need the redirect? So delete them.
	 *
	 * @param $category (object) – Current Category model object
	 * @param $values (array) – The Category model object data as an array
	 * @return Void 
	 */
	public function after_category_delete($category, $values)
	{
		$this->category_delete(array($values['cat_id']));
	}
	
	/**
	 * category_delete
	 *
	 * A bit pointless but if there is nothing to redirect to after deleting, then why would be need the redirect? So delete them.
	 *
	 * @param $cat_ids (array) – Array of category IDs being deleted
	 * @return Void 
	 */
	public function category_delete($cat_ids)
	{

		foreach($cat_ids as $cat_id)
		{
			
			// Get cat_url_title
			$categories = $this->EE->db->select('*')
					->from('categories')
					->where('site_id', $this->site_id)
					->where('cat_id', $cat_id)
					->get();

			foreach($categories->result_array() as $category)
			{
				$cat_group_id = $category['group_id'];
				$cat_url_title = $category['cat_url_title'];
			}

					
			// Categories can be used within multiple channels, so cater for them here too
			$this->EE->db->select('*')
					->from('channels')
					->where('site_id', $this->site_id)
					->where('cat_group <> ""')
					->order_by('channel_title', 'asc');
			$channel_query = $this->EE->db->get();
			
			foreach($channel_query->result_array() as $channel_data)
			{
				$cat_groups = explode('|', $channel_data['cat_group']);

				foreach($cat_groups as $group_id)
				{
					
					if ( isset($this->settings['category_url'][$channel_data['channel_id']]['channel_categories'][$group_id]['uri']) && !empty($this->settings['category_url'][$channel_data['channel_id']]['channel_categories'][$group_id]['uri'])
							&& $group_id == $cat_group_id
					)
					{

						// Category URL settings
						$cat_url = trim($this->settings['category_url'][$channel_data['channel_id']]['channel_categories'][$group_id]['uri'] ,'/').'/'.$cat_url_title;
		
						$this->EE->db->delete(
							'detours',
							array(
								'new_url' => $cat_url,
								'site_id'  => $this->site_id
							)
						);
						
						// Wildcard URLs
						$this->EE->db->delete(
							'detours',
							array(
								'new_url' => $cat_url.'/',
								'site_id'  => $this->site_id
							)
						);
			
					}
				}
			}
		
		}
		
	}


	// --------------------------------------------------------------------
	
	/**
	 * cp_js_end
	 *
	 * This adds orig_cat_url_title field to capture and post the original value after submission
	 * 
	 * @param $data
	 * @return $data
	 */
	function cp_js_end()
	{
		$data = '';
		
		if ($this->EE->extensions->last_call !== FALSE)
		{
			$data = $this->EE->extensions->last_call;
		}
		
		$js = '
			$(".pageContents form").has("input[name=cat_name]#cat_name").find("input[name=cat_url_title]#cat_url_title").each(function() {
				var $cat_url_title = $(this);
				if ($(".pageContents form").find("input[name=cat_id]").length > 0) {
					$cat_url_title.after(
						$("<input>")
							.attr("type", "hidden")
							.attr("name", "orig_cat_url_title")
							.val($cat_url_title.val())
					);
				}
			});
		
		';
		
		$js = ( ! empty($js)) ? NL . '(function($) {'.$js.'})(jQuery);' : '';
	
		return $data . $js;
	}
	
	
	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}

	// ----------------------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return 	mixed	void on update / false if none
	 */
	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
	}	
	
	
	// ----------------------------------------------------------------------
	
	private function parse_date($str, $date)
	{
		//entry_date format
		if ( preg_match_all( '#\{entry_date\s+format=([\"\'])([^\\1]*?)\\1\}#', $str, $matches, PREG_SET_ORDER )  )
		{
			foreach ( $matches as $match )
			{
				if ( isset( $match[2] ) )
				{
					if ( version_compare( APP_VER, '2.6.0', '<' ) )
					{
						$str = str_replace( $match[0], $this->EE->localize->decode_date( $match[2], $date ) , $str );
					}
					else
					{
						$str = str_replace( $match[0], $this->EE->localize->format_date( $match[2], $date ) , $str );
					}

				}
			}
		}
		return $str;
	}
	
	
	// ----------------------------------------------------------------------
	
	private function update_detours($original_url, $new_url, $start_date='', $end_date='', $wildcard=FALSE)
	{
		if (empty($original_url) || empty($new_url))
		{
			return FALSE;
		}
		
		$original_url_wildcard = $original_url;
		$new_url_wildcard = $new_url;
		
		if ($wildcard === TRUE)
		{
			$original_url .= '/';
			$original_url_wildcard .= '/%%';
			$new_url .= '/';
			$new_url_wildcard .= '/%%';
		}
		
		// Remove any existing redirects "from" the new url
		$this->EE->db->delete(
			'detours',
			array(
				'original_url' => $new_url_wildcard,
				'site_id'  => $this->site_id
			)
		);

		// Check if new url exists within Detour Pro and overwrite with new url
		// i.e. change target/redirect url if already redirecting to this entry
		$this->EE->db->update(
			'detours',
			// update
			array(
				'new_url'  => $new_url
			),
			// where
			array(
				'new_url' => $original_url,
				'site_id'  => $this->site_id
			)
		);
		
		// Check if original url and new url already set and add new redirect if not found
		$query = $this->EE->db->select('*')
				->from('detours')
				->where('original_url', $original_url_wildcard)
				->where('new_url', $new_url)
				->where('site_id', $this->site_id)
				->get();
			
		if ($query->num_rows() == 0)
		{
			$insert_data = array(
				'original_url' => $original_url_wildcard,
				'new_url'  => $new_url,
				'site_id'  => $this->site_id,
				'start_date' => date('Y-m-d', $start_date)
			);
			if (!empty($end_date))
			{
				$insert_data['end_date'] = date("Y-m-d", $end_date);
			}
			$this->EE->db->insert(
				'detours',
				$insert_data
			);
		}
		
		// Clean up any duplicates and redirects to self
		$this->EE->db->delete(
			'detours',
			array(
				'original_url' => $new_url_wildcard,
				'new_url' => $new_url,
				'site_id'  => $this->site_id
			)
		);
		
	}
	
	// ----------------------------------------------------------------------
}

/* End of file ext.detour_auto.php */
/* Location: /system/expressionengine/third_party/detour_auto/ext.detour_auto.php */