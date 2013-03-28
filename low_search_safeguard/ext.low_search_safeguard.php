<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Low Search Safeguard Extension class
 *
 * @package        low_search_safeguard
 * @author         Lodewijk Schutte ~ Low <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-search
 * @copyright      Copyright (c) 2013, Low
 */
class Low_search_safeguard_ext {

	// --------------------------------------------------------------------
	// PROPERTIES
	// --------------------------------------------------------------------

	/**
	 * Extension settings
	 *
	 * @access      public
	 * @var         array
	 */
	public $settings = array();

	/**
	 * Extension name
	 *
	 * @access      public
	 * @var         string
	 */
	public $name = 'Low Search Safeguard';

	/**
	 * Extension version
	 *
	 * @access      public
	 * @var         string
	 */
	public $version = '0.9.0';

	/**
	 * Extension description
	 *
	 * @access      public
	 * @var         string
	 */
	public $description = 'Adds simple anti-spam measures to Low Search';

	/**
	 * Do settings exist?
	 *
	 * @access      public
	 * @var         bool
	 */
	public $settings_exist = TRUE;

	/**
	 * Documentation link
	 *
	 * @access      public
	 * @var         string
	 */
	public $docs_url = '#';

	// --------------------------------------------------------------------

	/**
	 * EE Instance
	 *
	 * @access      private
	 * @var         object
	 */
	private $EE;

	/**
	 * Current class name
	 *
	 * @access      private
	 * @var         string
	 */
	private $class_name;

	/**
	 * Current site id
	 *
	 * @access      private
	 * @var         int
	 */
	private $site_id;

	/**
	 * Hooks used
	 *
	 * @access      private
	 * @var         array
	 */
	private $hooks = array(
		'low_search_catch_search'
	);

	/**
	 * Default settings
	 *
	 * @access      private
	 * @var         array
	 */
	private $default_settings = array(
		'allow_html' => 'n',
		'allow_urls' => 'n',
		'honeypot'   => '',
		'blacklist'  => '',
		'feedback'   => 'Input not allowed'
	);

	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access     public
	 * @param      mixed     Array with settings or FALSE
	 * @return     null
	 */
	public function __construct($settings = array())
	{
		// Get global instance
		$this->EE =& get_instance();

		// Get site id
		$this->site_id = $this->EE->config->item('site_id');

		// Set Class name
		$this->class_name = ucfirst(get_class($this));

		// Set settings
		$this->settings = array_merge($this->default_settings, $settings);
	}

	// --------------------------------------------------------------------

	/**
	 * Settings
	 *
	 * @access     public
	 * @param      array
	 * @return     array
	 */
	public function settings()
	{
		return array(
			'allow_html' => array('r', array('y'=>lang('yes'), 'n'=>lang('no')), $this->default_settings['allow_html']),
			'allow_urls' => array('r', array('y'=>lang('yes'), 'n'=>lang('no')), $this->default_settings['allow_urls']),
			'honeypot'   => array('i', '', $this->default_settings['honeypot']),
			'blacklist'  => array('t', '', $this->default_settings['blacklist']),
			'feedback'   => array('i', '', $this->default_settings['feedback'])
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Check incoming keywords
	 *
	 * @access     public
	 * @param      array
	 * @return     array
	 */
	public function low_search_catch_search($data)
	{
		// -------------------------------------------
		// Get the latest version of $data
		// -------------------------------------------

		if ($this->EE->extensions->last_call !== FALSE)
		{
			$data = $this->EE->extensions->last_call;
		}

		// -------------------------------------------
		// Check for HTML in the keywords
		// -------------------------------------------

		if ($this->settings['allow_html'] == 'n')
		{
			if ($data['keywords'] != strip_tags($data['keywords']))
			{
				$this->_abort();
			}
		}

		// -------------------------------------------
		// Check for URLs in the keywords
		// -------------------------------------------

		if ($this->settings['allow_urls'] == 'n')
		{
			if (preg_match('#https?://#', $data['keywords']))
			{
				$this->_abort();
			}
		}

		// -------------------------------------------
		// Check for honeypot
		// -------------------------------------------

		if ($hp = $this->settings['honeypot'])
		{
			if ( ! empty($data[$hp]))
			{
				$this->_abort();
			}

			// Don't send it along
			unset($data[$hp]);
		}

		// -------------------------------------------
		// Check for blacklist
		// -------------------------------------------

		if ($blacklist = array_unique(array_filter(preg_split('/(\s|\n)/', $this->settings['blacklist']))))
		{
			foreach ($blacklist AS $word)
			{
				$word = preg_quote($word, '#');

				if (preg_match("#\b{$word}\b#i", $data['keywords']))
				{
					$this->_abort();
				}
			}
		}

		// -------------------------------------------
		// Everything's fine
		// -------------------------------------------

		return $data;
	}

	/**
	 *	Go back from whence thou cameth!
	 */
	private function _abort()
	{
		$this->EE->session->set_flashdata('error_message', $this->settings['feedback']);
		$this->EE->functions->redirect($_SERVER['HTTP_REFERER']);
	}

	// --------------------------------------------------------------------

	/**
	 * Activate extension
	 *
	 * @access     public
	 * @return     null
	 */
	public function activate_extension()
	{
		foreach ($this->hooks AS $hook)
		{
			$this->_add_hook($hook);
		}
	}

	/**
	 * Update extension
	 *
	 * @access     public
	 * @param      string    Saved extension version
	 * @return     null
	 */
	public function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}

		// init data array
		$data = array();

		// Update to 1.0.0
		// if (version_compare($current, '1.0.0', '<'))
		// {
		// }

		// Add version to data array
		$data['version'] = $this->version;

		// Update records using data array
		$this->EE->db->where('class', $this->class_name);
		$this->EE->db->update('extensions', $data);
	}

	/**
	 * Disable extension
	 *
	 * @access     public
	 * @return     null
	 */
	public function disable_extension()
	{
		// Delete records
		$this->EE->db->where('class', $this->class_name);
		$this->EE->db->delete('extensions');
	}

	// --------------------------------------------------------------------
	// PRIVATE METHODS
	// --------------------------------------------------------------------

	/**
	 * Add hook to table
	 *
	 * @access     private
	 * @param      string
	 * @return     void
	 */
	private function _add_hook($hook)
	{
		$this->EE->db->insert('extensions', array(
			'class'    => $this->class_name,
			'method'   => $hook,
			'hook'     => $hook,
			'settings' => serialize($this->settings),
			'priority' => 5,
			'version'  => $this->version,
			'enabled'  => 'y'
		));
	}

}
// END CLASS

/* End of file ext.low_search_safeguard.php */