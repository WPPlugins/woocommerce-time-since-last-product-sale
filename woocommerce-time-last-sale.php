<?php
/*
Plugin Name: WooCommerce Time Since Last Sale
Plugin URI: http://www.algoritmika.com/shop/wordpress-woocommerce-time-since-last-product-sale-plugin/
Description: Plugin extends WordPress WooCommerce by adding time since last sale to product page.
Version: 1.0.1
Author: Algoritmika Ltd.
Author URI: http://www.algoritmika.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/
?>
<?php
if ( ! class_exists( 'CWooTimeSinceLastSalePlugin' ) ) 
{
	class CWooTimeSinceLastSalePlugin
	{		
		protected $defaultOptions;
		///////////////////////////////////////////////////////////////////////
		public function __construct()
		{
			delete_option('wootlslite_option_group');
			//checking if no options set -> then set default values			
			$this->defaultOptions = array(
				"wootlslite_is_private"							=>	TRUE,
				
				"wootlslite_text"								=>	"<div style='font-size: x-small;'>\n<strong>Time since last sale: </strong> <span style='color: green;'>[TIME]</span>.\n</div>",
				
				"wootlslite_time"								=>	365,				
				"wootlslite_do_use_only_completed_orders"		=>	TRUE,				
				
				"wootlslite_do_display_before_main_content"		=>	TRUE,
				"wootlslite_before_main_content_priority"		=>	10,
				
				"wootlslite_do_display_before_single_product"	=>	FALSE,
				"wootlslite_before_single_product_priority"		=>	10,
				
				"wootlslite_do_display_single_product"			=>	FALSE, //woocommerce_single_product_summary
				"wootlslite_single_product_priority"			=>	10,				
				
				"wootlslite_do_display_after_single_product"	=>	FALSE,
				"wootlslite_after_single_product_priority"		=>	10,				
				
				"wootlslite_do_display_after_main_content"		=>	FALSE,
				"wootlslite_after_main_content_priority"		=>	10,
				
				"wootlslite_do_display_on_custom_action"		=>	FALSE,
				"wootlslite_custom_action"						=>	"woocommerce_before_single_product_summary",
				"wootlslite_custom_action_priority"				=>	10,					
				
			);
			//$options = array();
			$options = get_option('wootlslite_option_group');
			if ($options == NULL) $options = array();
			foreach ($this->defaultOptions as $key => $value)
			{
				if (array_key_exists($key, $options) == FALSE)
				{
					$options[$key] = $value;
				}
			}			
			update_option('wootlslite_option_group', $options);
						
			if ($options['wootlslite_do_display_before_main_content'])
				add_action( 'woocommerce_before_main_content', array($this, 'print_last_sale_time'), $options['wootlslite_before_main_content_priority']);	
		
			add_shortcode('TIMELASTSALE', array($this, 'print_last_sale_time'));
						
			//Settings
			if(is_admin()){
				add_action('admin_menu', array($this, 'add_plugin_options_page'));
				add_action('admin_init', array($this, 'options_page_init'));
			}			
		}
		///////////////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////
		public function add_plugin_options_page()
		{
			add_submenu_page( 
				'woocommerce', 
				'WooCommerce Time Since Last Sale Settings Admin', 
				'Time Since Last Sale', 
				'manage_options', 
				'wootlslite-settings-admin', array($this, 'create_admin_page'));
		}		
		///////////////////////////////////////////////////////////////////////
		public function create_admin_page()
		{
			?><div class="wrap">
				<h2>Time Since Last Product Sale Options</h2>		
				<h3>To change options below - please get <a href="http://www.algoritmika.com/shop/wordpress-woocommerce-time-since-last-product-sale-pro-plugin/">WordPress WooCommerce Time Since Last Product’s Sale Pro Plugin</a>.</h3>				
				<form method="post" action="options.php">
					<?php
					settings_fields('wootlslite_option_group');	
					do_settings_sections('wootlslite-settings-admin');
				?>
					<?php submit_button(); ?>
				</form>
			</div><?php
		}
		///////////////////////////////////////////////////////////////////////
		public function options_page_init()
		{		
			register_setting('wootlslite_option_group', 'wootlslite_option_group', array($this, 'check_wootlslite_fields'));			
			add_settings_section('wootlslite_settings_section_id', '', '', 'wootlslite-settings-admin');			
			
			add_settings_field('wootlslite_is_private', 'Display only to admin', array($this, 'create_wootlslite_is_private'), 'wootlslite-settings-admin', 'wootlslite_settings_section_id');
			
			add_settings_field('wootlslite_text', 'Text to output', array($this, 'create_wootlslite_text_field'), 'wootlslite-settings-admin', 'wootlslite_settings_section_id');			
			
			add_settings_field('wootlslite_time', 'Days to cover', array($this, 'create_wootlslite_time_field'), 'wootlslite-settings-admin', 'wootlslite_settings_section_id');			
			add_settings_field('wootlslite_do_use_only_completed_orders', 'Use only completed orders', array($this, 'create_wootlslite_do_use_only_completed_orders'), 'wootlslite-settings-admin', 'wootlslite_settings_section_id');			
					
			add_settings_field('wootlslite_do_display_before_main_content', 'Display before main content', array($this, 'create_wootlslite_do_display_before_main_content'), 'wootlslite-settings-admin', 'wootlslite_settings_section_id');			
			add_settings_field('wootlslite_before_main_content_priority', 'Priority', array($this, 'create_wootlslite_before_main_content_priority_field'), 'wootlslite-settings-admin', 'wootlslite_settings_section_id');
			
			add_settings_field('wootlslite_do_display_before_single_product', 'Display before single product', array($this, 'create_wootlslite_do_display_before_single_product'), 'wootlslite-settings-admin', 'wootlslite_settings_section_id');			
			add_settings_field('wootlslite_before_single_product_priority', 'Priority', array($this, 'create_wootlslite_before_single_product_priority_field'), 'wootlslite-settings-admin', 'wootlslite_settings_section_id');
			
			add_settings_field('wootlslite_do_display_single_product', 'Display on single product summary', array($this, 'create_wootlslite_do_display_single_product'), 'wootlslite-settings-admin', 'wootlslite_settings_section_id');			
			add_settings_field('wootlslite_single_product_priority', 'Priority', array($this, 'create_wootlslite_single_product_priority_field'), 'wootlslite-settings-admin', 'wootlslite_settings_section_id');			
			
			add_settings_field('wootlslite_do_display_after_single_product', 'Display after single product', array($this, 'create_wootlslite_do_display_after_single_product'), 'wootlslite-settings-admin', 'wootlslite_settings_section_id');			
			add_settings_field('wootlslite_after_single_product_priority', 'Priority', array($this, 'create_wootlslite_after_single_product_priority_field'), 'wootlslite-settings-admin', 'wootlslite_settings_section_id');			
	
			add_settings_field('wootlslite_do_display_after_main_content', 'Display after main content', array($this, 'create_wootlslite_do_display_after_main_content'), 'wootlslite-settings-admin', 'wootlslite_settings_section_id');			
			add_settings_field('wootlslite_after_main_content_priority', 'Priority', array($this, 'create_wootlslite_after_main_content_priority_field'), 'wootlslite-settings-admin', 'wootlslite_settings_section_id');
			
			add_settings_field('wootlslite_do_display_on_custom_action', 'Add to custom action', array($this, 'create_wootlslite_do_display_on_custom_action_field'), 'wootlslite-settings-admin', 'wootlslite_settings_section_id');
			add_settings_field('wootlslite_custom_action', 'Action', array($this, 'create_wootlslite_custom_action_field'), 'wootlslite-settings-admin', 'wootlslite_settings_section_id');
			add_settings_field('wootlslite_custom_action_priority', 'Priority', array($this, 'create_wootlslite_custom_action_priority_field'), 'wootlslite-settings-admin', 'wootlslite_settings_section_id');
			
		}
		///////////////////////////////////////////////////////////////////////
		public function check_wootlslite_fields($input)
		{
			$res['wootlslite_is_private'] = $input['wootlslite_is_private'];
		
			$res['wootlslite_text'] = str_replace('"', '\'', $input['wootlslite_text']);

			if ($input['wootlslite_time'] <= 0) $res['wootlslite_time'] = $this->defaultOptions['wootlslite_time'];
			else $res['wootlslite_time'] = $input['wootlslite_time'];
			$res['wootlslite_do_use_only_completed_orders'] = $input['wootlslite_do_use_only_completed_orders'];
			
			$res['wootlslite_do_display_before_main_content'] = $input['wootlslite_do_display_before_main_content'];
			if ($input['wootlslite_before_main_content_priority'] <= 0) $res['wootlslite_before_main_content_priority'] = $this->defaultOptions['wootlslite_before_main_content_priority'];
			else $res['wootlslite_before_main_content_priority'] = $input['wootlslite_before_main_content_priority'];			
			
			$res['wootlslite_do_display_before_single_product'] = $input['wootlslite_do_display_before_single_product'];
			if ($input['wootlslite_before_single_product_priority'] <= 0) $res['wootlslite_before_single_product_priority'] = $this->defaultOptions['wootlslite_before_single_product_priority'];
			else $res['wootlslite_before_single_product_priority'] = $input['wootlslite_before_single_product_priority'];
			
			$res['wootlslite_do_display_single_product'] = $input['wootlslite_do_display_single_product'];
			if ($input['wootlslite_single_product_priority'] <= 0) $res['wootlslite_single_product_priority'] = $this->defaultOptions['wootlslite_single_product_priority'];
			else $res['wootlslite_single_product_priority'] = $input['wootlslite_single_product_priority'];			

			$res['wootlslite_do_display_after_single_product'] = $input['wootlslite_do_display_after_single_product'];
			if ($input['wootlslite_after_single_product_priority'] <= 0) $res['wootlslite_after_single_product_priority'] = $this->defaultOptions['wootlslite_after_single_product_priority'];
			else $res['wootlslite_after_single_product_priority'] = $input['wootlslite_after_single_product_priority'];			
			
			$res['wootlslite_do_display_after_main_content'] = $input['wootlslite_do_display_after_main_content'];	
			if ($input['wootlslite_after_main_content_priority'] <= 0) $res['wootlslite_after_main_content_priority'] = $this->defaultOptions['wootlslite_after_main_content_priority'];
			else $res['wootlslite_after_main_content_priority'] = $input['wootlslite_after_main_content_priority'];			
			
			$res['wootlslite_do_display_on_custom_action'] = $input['wootlslite_do_display_on_custom_action'];			
			$res['wootlslite_custom_action'] = str_replace('"', '\'', $input['wootlslite_custom_action']);	
			if ($input['wootlslite_custom_action_priority'] <= 0) $res['wootlslite_custom_action_priority'] = $this->defaultOptions['wootlslite_custom_action_priority'];
			else $res['wootlslite_custom_action_priority'] = $input['wootlslite_custom_action_priority'];
			
			return $res;
		}		
		///////////////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////		
		public function create_wootlslite_is_private()
		{
			?><input disabled type="checkbox" checked /> <span style="font-style: italic; color: gray;">Default: <?=$converted_res = ($this->defaultOptions['wootlslite_is_private']) ? 'on' : 'off';?></span><?php
		}
		///////////////////////////////////////////////////////////////////////
		public function create_wootlslite_text_field()
		{
			$options = get_option('wootlslite_option_group');
			/*if (empty($options['wootlslite_text']))
				$options['wootlslite_text'] = $this->defaultValueText;*/
			?><span style="font-style: italic; color: gray;">Default:</span><br /><textarea style="width: 95%; height: 75px;" readonly><?=$this->defaultOptions['wootlslite_text']?></textarea><br />
			<span style="font-style: italic; color: gray;">Current value:</span><br />
			<textarea readonly style="width: 95%; height: 150px;" id="wootlslite_text_id" name="wootlslite_option_group[wootlslite_text]"><?=$options['wootlslite_text'];?></textarea><br />
			<span style="font-style: italic; color: gray;">Preview:</span><br /><?=str_replace("[TIME]", "2 hours", $options['wootlslite_text'])?><?php
		}	
		///////////////////////////////////////////////////////////////////////
		public function create_wootlslite_time_field()
		{
			$options = get_option('wootlslite_option_group');
			/*if (empty($options['wootlslite_time']))
				$options['wootlslite_time'] = $this->defaultValueTime;*/
			?><input readonly type="text" style="width: 50px;" id="wootlslite_time_id" name="wootlslite_option_group[wootlslite_time]" value="<?=$options['wootlslite_time'];?>" /> <span style="font-style: italic; color: gray;">Default: <?=$this->defaultOptions['wootlslite_time']?> days</span><?php
		}		
		///////////////////////////////////////////////////////////////////////		
		public function create_wootlslite_do_use_only_completed_orders()
		{
			$options = get_option('wootlslite_option_group');
			$is_checked = ''; //default value
			if ($options['wootlslite_do_use_only_completed_orders'] == TRUE) $is_checked = 'checked';			
			?><input disabled type="checkbox" <?=$is_checked;?> id="wootlslite_do_use_only_completed_orders_id" name="wootlslite_option_group[wootlslite_do_use_only_completed_orders]" /> <span style="font-style: italic; color: gray;">Default: <?=$converted_res = ($this->defaultOptions['wootlslite_do_use_only_completed_orders']) ? 'on' : 'off';?></span><hr/><?php
		}		
		///////////////////////////////////////////////////////////////////////
		public function create_wootlslite_do_display_before_main_content()
		{
			$options = get_option('wootlslite_option_group');
			$is_checked = ''; //default value
			if ($options['wootlslite_do_display_before_main_content'] == TRUE) $is_checked = 'checked';			
			?><input disabled type="checkbox" <?=$is_checked;?> id="wootlslite_do_display_before_main_content_id" name="wootlslite_option_group[wootlslite_do_display_before_main_content]" /> <span style="font-style: italic; color: gray;">Default: <?=$converted_res = ($this->defaultOptions['wootlslite_do_display_before_main_content']) ? 'on' : 'off';?></span><?php
		}	
		///////////////////////////////////////////////////////////////////////
		public function create_wootlslite_before_main_content_priority_field()
		{
			$options = get_option('wootlslite_option_group');
			?><input readonly type="text" style="width: 50px;" id="wootlslite_before_main_content_priority_id" name="wootlslite_option_group[wootlslite_before_main_content_priority]" value="<?=$options['wootlslite_before_main_content_priority'];?>" /> <span style="font-style: italic; color: gray;">Default: <?=$this->defaultOptions['wootlslite_before_main_content_priority']?></span><hr/><?php
		}
		///////////////////////////////////////////////////////////////////////
		public function create_wootlslite_do_display_before_single_product()
		{
			$options = get_option('wootlslite_option_group');
			$is_checked = ''; //default value
			//if (($options['wootlslite_do_display_before_single_product'] == TRUE) || (array_key_exists('wootlslite_do_display_before_single_product', $options) == FALSE)) $is_checked = 'checked';			
			if ($options['wootlslite_do_display_before_single_product'] == TRUE) $is_checked = 'checked';			
			?><input disabled type="checkbox" <?=$is_checked;?> id="wootlslite_do_display_before_single_product_id" name="wootlslite_option_group[wootlslite_do_display_before_single_product]" /> <span style="font-style: italic; color: gray;">Default: <?=$converted_res = ($this->defaultOptions['wootlslite_do_display_before_single_product']) ? 'on' : 'off';?></span><?php
		}	
		///////////////////////////////////////////////////////////////////////
		public function create_wootlslite_before_single_product_priority_field()
		{
			$options = get_option('wootlslite_option_group');
			?><input readonly type="text" style="width: 50px;" id="wootlslite_before_single_product_priority_id" name="wootlslite_option_group[wootlslite_before_single_product_priority]" value="<?=$options['wootlslite_before_single_product_priority'];?>" /> <span style="font-style: italic; color: gray;">Default: <?=$this->defaultOptions['wootlslite_before_single_product_priority']?></span><hr/><?php
		}
		///////////////////////////////////////////////////////////////////////
		public function create_wootlslite_do_display_single_product()
		{
			$options = get_option('wootlslite_option_group');
			$is_checked = ''; //default value
			//if (($options['wootlslite_do_display_single_product'] == TRUE) || (array_key_exists('wootlslite_do_display_single_product', $options) == FALSE)) $is_checked = 'checked';			
			if ($options['wootlslite_do_display_single_product'] == TRUE) $is_checked = 'checked';			
			?><input disabled type="checkbox" <?=$is_checked;?> id="wootlslite_do_display_single_product_id" name="wootlslite_option_group[wootlslite_do_display_single_product]" /> <span style="font-style: italic; color: gray;">Default: <?=$converted_res = ($this->defaultOptions['wootlslite_do_display_single_product']) ? 'on' : 'off';?></span><?php
		}	
		///////////////////////////////////////////////////////////////////////
		public function create_wootlslite_single_product_priority_field()
		{
			$options = get_option('wootlslite_option_group');
			?><input readonly type="text" style="width: 50px;" id="wootlslite_single_product_priority_id" name="wootlslite_option_group[wootlslite_single_product_priority]" value="<?=$options['wootlslite_single_product_priority'];?>" /> <span style="font-style: italic; color: gray;">Default: <?=$this->defaultOptions['wootlslite_single_product_priority']?></span><hr/><?php
		}		
		///////////////////////////////////////////////////////////////////////
		public function create_wootlslite_do_display_after_single_product()
		{
			$options = get_option('wootlslite_option_group');
			$is_checked = ''; //default value
			//if (($options['wootlslite_do_display_after_single_product'] == TRUE) || (array_key_exists('wootlslite_do_display_after_single_product', $options) == FALSE)) $is_checked = 'checked';			
			if ($options['wootlslite_do_display_after_single_product'] == TRUE) $is_checked = 'checked';			
			?><input disabled type="checkbox" <?=$is_checked;?> id="wootlslite_do_display_after_single_product_id" name="wootlslite_option_group[wootlslite_do_display_after_single_product]" /> <span style="font-style: italic; color: gray;">Default: <?=$converted_res = ($this->defaultOptions['wootlslite_do_display_after_single_product']) ? 'on' : 'off';?></span><?php
		}	
		///////////////////////////////////////////////////////////////////////
		public function create_wootlslite_after_single_product_priority_field()
		{
			$options = get_option('wootlslite_option_group');
			?><input readonly type="text" style="width: 50px;" id="wootlslite_after_single_product_priority_id" name="wootlslite_option_group[wootlslite_after_single_product_priority]" value="<?=$options['wootlslite_after_single_product_priority'];?>" /> <span style="font-style: italic; color: gray;">Default: <?=$this->defaultOptions['wootlslite_after_single_product_priority']?></span><hr/><?php
		}		
		///////////////////////////////////////////////////////////////////////
		public function create_wootlslite_do_display_after_main_content()
		{
			$options = get_option('wootlslite_option_group');
			$is_checked = ''; //default value
			if ($options['wootlslite_do_display_after_main_content'] == TRUE) $is_checked = 'checked';			
			?><input disabled type="checkbox" <?=$is_checked;?> id="wootlslite_do_display_after_main_content_id" name="wootlslite_option_group[wootlslite_do_display_after_main_content]" /> <span style="font-style: italic; color: gray;">Default: <?=$converted_res = ($this->defaultOptions['wootlslite_do_display_after_main_content']) ? 'on' : 'off';?></span><?php
		}
		///////////////////////////////////////////////////////////////////////
		public function create_wootlslite_after_main_content_priority_field()
		{
			$options = get_option('wootlslite_option_group');
			?><input readonly type="text" style="width: 50px;" id="wootlslite_after_main_content_priority_id" name="wootlslite_option_group[wootlslite_after_main_content_priority]" value="<?=$options['wootlslite_after_main_content_priority'];?>" /> <span style="font-style: italic; color: gray;">Default: <?=$this->defaultOptions['wootlslite_after_main_content_priority']?></span><hr/><?php
		}		
		///////////////////////////////////////////////////////////////////////
		public function create_wootlslite_do_display_on_custom_action_field()
		{
			$options = get_option('wootlslite_option_group');
			$is_checked = ''; //default value
			if ($options['wootlslite_do_display_on_custom_action'] == TRUE) $is_checked = 'checked';	
			?><input disabled type="checkbox" <?=$is_checked;?> id="wootlslite_do_display_on_custom_action_id" name="wootlslite_option_group[wootlslite_do_display_on_custom_action]" /> <span style="font-style: italic; color: gray;">Default: <?=$converted_res = ($this->defaultOptions['wootlslite_do_display_on_custom_action']) ? 'on' : 'off';?></span><?php
		}		
		///////////////////////////////////////////////////////////////////////
		public function create_wootlslite_custom_action_field()
		{
			$options = get_option('wootlslite_option_group');
			/*if (empty($options['wootlslite_custom_action']))
				$options['wootlslite_custom_action'] = $this->defaultValueCustomAction;*/
			?><input readonly type="text" style="width: 50%;" id="wootlslite_custom_action_id" name="wootlslite_option_group[wootlslite_custom_action]" value="<?=$options['wootlslite_custom_action'];?>" /> <span style="font-style: italic; color: gray;">Default: <?=$this->defaultOptions['wootlslite_custom_action']?></span><?php
		}
		///////////////////////////////////////////////////////////////////////
		public function create_wootlslite_custom_action_priority_field()
		{
			$options = get_option('wootlslite_option_group');
			/*if (empty($options['wootlslite_custom_action']))
				$options['wootlslite_custom_action'] = $this->defaultValueCustomActionPriority;*/
			?><input readonly type="text" style="width: 50px;" id="wootlslite_custom_action_priority_id" name="wootlslite_option_group[wootlslite_custom_action_priority]" value="<?=$options['wootlslite_custom_action_priority'];?>" /> <span style="font-style: italic; color: gray;">Default: <?=$this->defaultOptions['wootlslite_custom_action_priority']?></span><hr/><?php
		}		
		///////////////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////
		public function time_elapsed_string($ptime)
		{
			$etime = (time() - $ptime);

			if ($etime < 1)
				return '0 seconds';			
			
			$a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
						30 * 24 * 60 * 60       =>  'month',
						24 * 60 * 60            =>  'day',
						60 * 60                 =>  'hour',
						60                      =>  'minute',
						1                       =>  'second'
						);

			foreach ($a as $secs => $str)
			{
				$d = $etime / $secs;
				if ($d >= 1)
				{
					$r = round($d);
					return $r . ' ' . $str . ($r > 1 ? 's' : '');
				}
			}
		}
		///////////////////////////////////////////////////////////////////////
		public function get_last_sale_time()		
		{
			if (!is_single())
				return	'';
				
			$options = get_option('wootlslite_option_group');
			
			if ($options['wootlslite_is_private'] == TRUE)
				if (is_super_admin() == FALSE)
					return	'';	

			$cid = get_the_ID();	
			
			//$dateToCover = date('Y-m-d', (time() - 60*60*24*$options['wootlslite_time']));
			
			$tax_query = '';
			if ($options['wootlslite_do_use_only_completed_orders'])
			{
					$tax_query = 
						array(
								array(
									'taxonomy' => 'shop_order_status',
									'field' => 'slug',
									'terms' => array('completed')
								)
						);
							
			}
			
			$args = array(
				'post_type'			=> 'shop_order',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> -1,
				/*'meta_key' 			=> 'post_date',
				'meta_compare' 		=> '>=',
				'meta_value' 		=> $dateToCover,*/
				'orderby'			=> 'date',
				'order'				=> 'DESC',
				'tax_query'			=> $tax_query,
			);
			
			$loop = new WP_Query( $args );
			//echo '<pre>'; print_r($loop); echo '</pre>';
			
			while ( $loop->have_posts() ) : $loop->the_post();
			
				//echo '<pre>'; print_r($loop->post); echo '</pre>';
			
				$order_id = $loop->post->ID;
				$order = new WC_Order($order_id);
				$items = $order->get_items();
				foreach ($items as $item)
				{
					if ($item['product_id'] == $cid)
					{
						$ret = '';
						if ((time() - get_the_time('U')) < 60*60*24*$options['wootlslite_time'])
							$ret .= str_replace(
								"[TIME]",
								$this->time_elapsed_string(get_the_time('U')),
								$options['wootlslite_text']);
						wp_reset_query();
						return $ret;					
					}
				}
	
			endwhile;
			
			$ret = '';//'<p><span style="font-size:xx-small; font-style: italic;">* This item was never sold.</span></p>';
			wp_reset_query();
			return $ret;			
		}
		///////////////////////////////////////////////////////////////////////
		public function print_last_sale_time()
		{
			echo $this->get_last_sale_time();
		}		
		///////////////////////////////////////////////////////////////////////
	}
}
$wooTimeSinceLastSalePlugin = &new CWooTimeSinceLastSalePlugin();

/*function print_last_sale_time()
{
	$wooTimeSinceLastSaleProPluginObject = new CWooTimeSinceLastSalePlugin;
	$wooTimeSinceLastSaleProPluginObject->print_last_sale_time();
}*/
function get_last_sale_time_lite()
{
	$wooTimeSinceLastSalePluginObject = new CWooTimeSinceLastSalePlugin;
	return $wooTimeSinceLastSalePluginObject->get_last_sale_time();
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ( ! class_exists( 'CWooTimeSinceLastSaleWidget' ) ) 
{
	class CWooTimeSinceLastSaleWidget extends WP_Widget {

		/**
		 * Sets up the widgets name etc
		 */
		public function __construct() {
			parent::__construct(
				'wootlslite_widget', // Base ID
				__('WooCommerce Time Since Last Product Sale', 'text_domain'), // Name
				array( 'description' => __( 'Display time since last product sale', 'text_domain' ), ) // Args
			);
		}

		/**
		 * Outputs the content of the widget
		 *
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance ) {
			// outputs the content of the widget
			$lastSaleTime = get_last_sale_time_lite();
			if (empty($lastSaleTime))
				return;
				
			$title = apply_filters( 'widget_title', $instance['title'] );

			echo $args['before_widget'];
			if ( ! empty( $title ) )
				echo $args['before_title'] . $title . $args['after_title'];
			echo $lastSaleTime;
			echo $args['after_widget'];		
		}

		/**
		 * Ouputs the options form on admin
		 *
		 * @param array $instance The widget options
		 */
		public function form( $instance ) {
			// outputs the options form on admin
			if ( isset( $instance[ 'title' ] ) ) {
				$title = $instance[ 'title' ];
			}
			else {
				$title = __( 'Time Since Last Product Sale', 'text_domain' );
			}
			?>
			<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
			</p>		
			<p>To change text and style please go to:<br /><a href="/wp-admin/admin.php?page=wootlslite-settings-admin">WooCommerce->Time Since Last Product Sale</a>.</p>
			<?php 		
		}

		/**
		 * Processing widget options on save
		 *
		 * @param array $new_instance The new options
		 * @param array $old_instance The previous options
		 */
		public function update( $new_instance, $old_instance ) {
			// processes widget options to be saved
			$instance = array();
			$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

			return $instance;		
		}
	}
	/*add_action( 'widgets_init', function(){
		 register_widget( 'CWooTimeSinceLastSaleProWidget' );
	});*/
	add_action('widgets_init',
		create_function('', 'return register_widget("CWooTimeSinceLastSaleWidget");')
	);
}