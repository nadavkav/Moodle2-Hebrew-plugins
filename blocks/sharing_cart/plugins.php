<?php
/**
 * Sharing Cart: Plugins
 *
 * @author VERSION2 Inc.
 * @version $Id: plugins.php,v 1.3 2009/12/04 10:31:51 akiococom Exp $
 * @package sharingcart
 */

/**
	<Usage>
	
	@ /blocks/sharing_cart/plugins/foo.php
	
	class sharing_cart_plugin_foo implements sharing_cart_plugin
	{
		...
	}
	
	sharing_cart_plugins::register(new sharing_cart_plugin_foo());
 */

  // require_once($CFG->libdir.'/dml/moodle_database.php');

interface sharing_cart_plugin
{
	public function get_command(); /* @return string or null */
	public function get_header();  /* @return string or null */
	public function get_footer();  /* @return string or null */
	public function get_import();  /* @return string or null */
	public function get_script();  /* @return string or null */
}

class sharing_cart_plugins
{
	public static function register(sharing_cart_plugin $instance)
	{
		$name = preg_replace('/^sharing_cart_plugin_/', '', get_class($instance));
		self::$instances[$name] = $instance;
	}
	public static function get_config($plugin, $user = 0)
	{
	  global $DB;
	  if ($record = $DB->get_record('sharing_cart_plugins', array('plugin' => $plugin),'*')) {
			return unserialize($record->data);
		} else {
			return null;
		}
	}
	public static function set_config($plugin, $data, $user = 0)
	{
		if (record_exists('sharing_cart_plugins', 'plugin', $plugin, 'user', $user)) {
			return set_field('sharing_cart_plugins', 'data', addslashes(serialize($data)),
			                 'plugin', $plugin, 'user', $user);
		} else {
			$record         = new stdClass;
			$record->plugin = $plugin;
			$record->user   = $user;
			$record->data   = addslashes(serialize($data));
			return $DB->insert_record('sharing_cart_plugins', $record);
		}
	}
	public static function get_string($id, $plugin, $a = null, $extralocations = null)
	{
		if (!is_array($extralocations)) {
			$extralocations = array();
		}
		$extralocations[] = dirname(__FILE__).'/plugins/'.$plugin.'/lang/';
		return get_string($id, $plugin, $a/*, $extralocations*/);
	}
	public static function get_commands()
	{
		return self::apply('get_command');
	}
	public static function get_headers()
	{
		return self::apply('get_header');
	}
	public static function get_footers()
	{
		return self::apply('get_footer');
	}
	public static function get_imports()
	{
		return self::apply('get_import');
	}
	public static function get_scripts()
	{
		return self::apply('get_script');
	}
	public static function enum()
	{
		return array_keys(self::$instances);
	}
	public static function load($dir = null)
	{
		static $loaded_dirs = array();
		if ($dir === null) {
			$dir = dirname(__FILE__).'/plugins';
		}
		$prev_num_instances = count(self::$instances);
		if (!isset($loaded_dirs[$dir]) && is_dir($dir)) {
			$d = dir($dir);
			while (($e = $d->read()) !== false) {
				$pi = pathinfo($e);
				if (!empty($pi['extension']) && $pi['extension'] == 'php') {
					if ($e != 'plugin.php') {
						@include $dir.DIRECTORY_SEPARATOR.$e;
					}
				}
			}
			$d->close();
			$loaded_dirs[$dir] = true;
		}
		return count(self::$instances) - $prev_num_instances;
	}
	
	private static function apply($method)
	{
		self::load();
		
		$enabled_plugins = array_flip(
			array_filter(explode(',', $GLOBALS['CFG']->sharing_cart_plugins))
		);
		$values = array();
		foreach (self::$instances as $name => $instance) {
			if (isset($enabled_plugins[$name]))
				$values[] = $instance->$method();
		}
		return array_filter($values);
	}
	private static $instances = array();
}

?>