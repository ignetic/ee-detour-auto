<style type="text/css">
#mainContent table td.header, #mainContent table tr:hover > td.header { background: #ABB7C3 !important; color: #fff; }
#mainContent table td.header h4, #mainContent table tr:hover > td.header h4 { color: #fff; font-weight: normal; margin: 0; }
::-webkit-input-placeholder { color:#bbb; }
::-moz-placeholder { color:#bbb; }
:-ms-input-placeholder { color:#bbb; }
input:-moz-placeholder { color:#bbb; }
.mainTable select[multiple] {width: 100%;}
.mainTable th, .mainTable td { white-space: normal; vertical-align: top; }
</style>

<?php echo form_open($settings_url, 'id="dpa_settings"')?>

<h3><?= lang('detour_auto_description') ?></h3>
	
<?php

/* Channels Settings */

$this->table->set_template($cp_table_template);
$this->table->set_heading(
	lang('channel_name'),
	lang('channel_uri'),
	lang('wildcard'),
	lang('expiry_time'),
	lang('allow_statuses')
);

	$channel_setting_table = '';

	foreach ($channel_settings as $channel_setting)
	{
		$this->table->add_row(
			"<label>{$channel_setting['uri']['label']}</label>",
			form_input($channel_setting['uri']['name'], $channel_setting['uri']['value'], 'placeholder="'.$channel_setting['uri']['placeholder'].'"'),
			form_checkbox($channel_setting['wildcard']['name'], 'y', ($channel_setting['wildcard']['value'] == 'y') ? TRUE : FALSE),
			form_dropdown($channel_setting['date']['name'], $date_list, $channel_setting['date']['value']),
			form_multiselect($channel_setting['statuses']['name'].'[]', $channel_setting['statuses']['data'], $channel_setting['statuses']['value'])
		);
	}

$channel_setting_table = $this->table->generate();
$this->table->clear();

/* Channels Table */

$this->table->set_template($cp_table_template);
$this->table->set_heading(
	array('data' => lang('description'), 'style' => 'width:25%;'),
	lang('channel_redirect_settings')
);

	$this->table->add_row(
		array('data' => '<h3>'.lang('channel_settings').'</h3>' . lang('channel_settings_description'), 'style' => 'vertical-align:top;'),
		$channel_setting_table
	);
	
	$this->table->add_row(
		'<strong>'.lang('channel_allowed_time').'</strong>',
		lang('channel_allowed_time_description') . BR . form_dropdown('channel_allowed_time', $allowed_time_list, $channel_allowed_time)
	);

echo $this->table->generate();
$this->table->clear();




/* Categories Settings */

$this->table->set_template($cp_table_template);
$this->table->set_heading(
	lang('category_name'),
	lang('category_uri'),
	lang('wildcard'),
	lang('expiry_time')
);

	$category_setting_table = '';
	
	foreach ($category_settings as $category_setting)
	{
	
		$this->table->add_row(array('data' => "<h4><strong>Channel:</strong> ".$category_setting['channel_title']."</h4>",  'colspan' => 4, 'class' => 'header'));
	
		if (isset($category_setting['channel_categories']))
		{
			foreach ($category_setting['channel_categories'] as $category_url)
			{

				if (is_array($category_url))
				{
					$this->table->add_row(
						"<label>{$category_url['uri']['label']}</label>".((isset($category_url['uri']['detail'])) ? "<div class='subtext'>{$category_url['uri']['detail']}</div>" : ''),
						form_input($category_url['uri']['name'], $category_url['uri']['value']),
						form_checkbox($category_url['wildcard']['name'], 'y', ($category_url['wildcard']['value'] == 'y') ? TRUE : FALSE),
						form_dropdown($category_url['date']['name'], $date_list, $category_url['date']['value'])
					);
				}
			}
		}
	}
	
$category_setting_table = $this->table->generate();
$this->table->clear();

/* Categories Table */

$this->table->set_template($cp_table_template);
$this->table->set_heading(
	array('data' => lang('description'), 'style' => 'width:25%;'),
	lang('category_redirect_settings')
);

	$this->table->add_row(
		array('data' => '<h3>'.lang('category_settings').'</h3>' . lang('category_settings_description'), 'style' => 'vertical-align:top;'),
		$category_setting_table
	);

echo $this->table->generate();
$this->table->clear();


?>

<p class="centerSubmit"><?=form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'btn submit'))?></p>

<?=form_close()?>