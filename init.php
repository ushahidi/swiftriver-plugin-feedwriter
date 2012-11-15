<?php defined('SYSPATH') OR die('No direct script access');

/**
 * Init for the FeedWriter plugin
 *
 * @package   SwiftRiver
 * @author    Ushahidi Team
 * @category  Plugins
 * @copyright (c) 2008-2012 Ushahidi Inc <http://ushahidi.com>
 */

class Feedwriter_Init
{
	public static function feed_url($format)
	{
		$url = URL::site(Route::get('feeds')->uri(array(
			'account' => Request::current()->param('account'),
			'name'	  => Request::current()->param('name'),
			'directory' => strtolower(Request::current()->controller()),
			'format'  => $format
		)), true);
		
		if (($token = self::get_token())) {
			$url .= '?at='.$token;
		}
		
		return $url;
	}
	
	public static function get_token()
	{
		$token = NULL;
		
		if (strlen(Request::current()->param('name')) > 0)
		{
			$account = ORM::factory('Account', 
				array('account_path' => Request::current()->param('account')));
				
			if (strtolower(Request::current()->controller()) == 'bucket')
			{
				$bucket = ORM::factory('Bucket')
					->where('bucket_name_url', '=', Request::current()->param('name'))
					->where('account_id', '=', $account->id)
					->find();
					
				if ( ! (bool) $bucket->bucket_publish AND isset($bucket->public_token))
				{
					$token = $bucket->public_token;
				} 
			}
			else
			{
				$river = ORM::factory('River')
					->where('river_name_url', '=', Request::current()->param('name'))
					->where('account_id', '=', $account->id)
					->find();
					
				if ( ! (bool) $river->river_public AND isset($river->public_token))
				{
					$token = $river->public_token;
				}
			}
		}
		
		return $token;
	}
	
	public static function add_meta()
	{
		if ((strtolower(Request::current()->controller()) == 'bucket' OR
			strtolower(Request::current()->controller()) == 'river') AND
			strlen(Request::current()->param('name')) > 0)
		{
			echo '<link rel="alternate" title="RSS" type="application/rss+xml" href="'.self::feed_url('rss').
				'" /><link rel="alternate" title="Atom" type="application/atom+xml" href="'.
				self::feed_url('atom').'" />';
		}
	}   

	public static function add_icon()
	{
		if ((strtolower(Request::current()->controller()) == 'bucket' OR
			strtolower(Request::current()->controller()) == 'river') AND
			strlen(Request::current()->param('name')) > 0)
		{
			$icon = View::factory("feedwriter/icon");
			$icon->feed_url = self::feed_url('rss');
			echo $icon;
		}
	}
}

Swiftriver_Event::add('swiftriver.template.meta', array('Feedwriter_Init', 'add_meta'));
Swiftriver_Event::add('swiftriver.template.head', array('Feedwriter_Init', 'add_icon'));

// Bind the plugin to valid URLs
Route::set('feeds', '<account>/<directory>/<name>/<format>',
	array(
		'directory' => '(river|bucket)',
		'format' => '(rss|atom)'
	))
	->defaults(array(
		'controller' => 'feedwriter',
		'action' => 'index'
	));



?>
