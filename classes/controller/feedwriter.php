<?php defined('SYSPATH') OR die('No direct script access');

/**
 * Controller for the FeedWriter plugin
 *
 * @package   SwiftRiver
 * @author    Ushahidi Team
 * @category  Plugins
 * @copyright (c) 2008-2012 Ushahidi Inc <http://ushahidi.com>
 */

include 'plugins/feedwriter/classes/libs/rss.php';
include 'plugins/feedwriter/classes/libs/atom.php';

class Controller_Feedwriter extends Controller_Swiftriver
{
    protected $bucket;
    protected $droplets;
    protected $ready;

    public function before()
    {
        parent::before();
        $this->auto_render = false;
        $this->bucket = ORM::factory('bucket')
            ->where('bucket_name_url', '=', $this->request->param('name'))
            ->find();

        // If load successful, grab most recent 10 droplets and save
        if ($this->bucket->loaded())
        {
            $droplets = Model_Bucket::get_droplets($this->user->id,
                $this->bucket->id, 0, NULL, PHP_INT_MAX, FALSE, array(), 10);
            $this->droplets = $droplets['droplets'];
        }
        else
            throw new HTTP_Exception_404();
    }

    public function action_rss()
    {
        $account = $this->bucket->account->account_path;
        $name = $this->bucket->bucket_name;

        $feed = new Feedwriter_Rss;
        $feed->setTitle($account.' / '.$name);
        $feed->setLink(URL::site($this->bucket->get_base_url(), true));
        $feed->setDescription('Drops from '.$this->bucket->bucket_name.
            ' on Swiftriver');
        $feed->setLanguage(Kohana::$config->load('feedwriter')->get('language'));
        $feed->setCopyright(Kohana::$config->load('feedwriter')->get('copyright'));

        if (count($this->droplets) > 0)
        {
            $feed->setUpdated($this->droplets[0]['droplet_date_pub']);
            foreach ($this->droplets as $k => $v)
            {
                $url = URL::site($this->request->param('account').'/bucket/'.
                    $this->request->param('name').'/drop/'.$v['id'], true);
                $feed->addItem(array(
                    'title'       => $v['droplet_title'],
                    'guid'        => $url,
                    'link'        => $url,
                    'description' => $v['droplet_content'],
                    'time'        => $v['droplet_date_pub']
                ));
            }
        }

        $this->response->headers('Content-Type', 'text/xml');
        echo $feed->generate();
    }
    
    public function action_atom()
    {
        $account = $this->bucket->account->account_path;
        $name = $this->bucket->bucket_name;

        $feed = new Feedwriter_Atom;
        $feed->setTitle($account.' / '.$name);
        $feed->setLink(URL::site($this->bucket->get_base_url(), true));
        $feed->setAuthor('SwiftRiver / '.$account, URL::site($account, true));
        $feed->setDescription('Drops from '.$this->bucket->bucket_name.
            ' on Swiftriver');
        $feed->setCopyright(Kohana::$config->load('feedwriter')->get('copyright'));

        if (count($this->droplets) > 0)
        {
            $feed->setUpdated($this->droplets[0]['droplet_date_pub']);
            foreach ($this->droplets as $k => $v)
            {
                $url = URL::site($this->request->param('account').'/bucket/'.
                    $this->request->param('name').'/drop/'.$v['id'], true);
                $feed->addItem(array(
                    'title'   => $v['droplet_title'],
                    'id'      => $url,
                    'link'    => $url,
                    'content' => $v['droplet_content'],
                    'time'    => $v['droplet_date_pub'],
                    'author'  => $v['identity_name'],
                ));
            }
        }

        $this->response->headers('Content-Type', 'text/xml');
        echo $feed->generate();
    }
}

?>
