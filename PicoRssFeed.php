<?php

class PicoRssFeed extends AbstractPicoPlugin
{

    //variable declarations

    protected $giveFeed = false;  // boolean to determine if the user has typed example.com/feed
    protected $feedTitle = '';    // title of the feed will be the site title
    protected $reverse = false;

    /**
     * This plugin is enabled by default?
     *
     * @see AbstractPicoPlugin::$enabled
     * @var boolean
     */
    protected $enabled = true;

    /**
     * This plugin depends on ...
     *
     * @see AbstractPicoPlugin::$dependsOn
     * @var string[]
     */
    protected $dependsOn = array();

    /**
     * Triggered after Pico has loaded all available plugins
     *
     * This event is triggered nevertheless the plugin is enabled or not.
     * It is NOT guaranteed that plugin dependencies are fulfilled!
     *
     * @see    Pico::getPlugin()
     * @see    Pico::getPlugins()
     * @param  object[] &$plugins loaded plugin instances
     * @return void
     */

    public function onConfigLoaded(array &$config)
    {
        // Get site data
        $this->feedTitle = $config['site_title'];
        $this->reverse = (strtolower($config['pages_order'] ?? 'asc') == 'desc') ? true : false;
    }

    /**
     * Triggered after Pico has evaluated the request URL
     *
     * @see    Pico::getRequestUrl()
     * @param  string &$url part of the URL describing the requested contents
     * @return void
     */
    public function onRequestUrl(&$url)
    {
        // If example.com/feed, then true
        if ($url == 'feed') {
            $this->giveFeed = true;
        }
    }


    public function onPagesLoaded(
        array &$pages,
        array &$currentPage = null,
        array &$previousPage = null,
        array &$nextPage = null
    )
    {
        // If this is the feed link, return RSS feed
        if ($this->giveFeed) {
            $pico = $this->getPico();
            $base_url = $pico->getBaseUrl();
            $feed_url = $base_url.'/?feed';

            //Sitemap found, 200 OK
            header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
            header("Content-Type: application/rss+xml; charset=UTF-8");

            //RSS Start
            $rss = '<?xml version="1.0" encoding="utf-8"?>';
            $rss .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
            $rss .= '<channel><title>';
            $rss .= $this->feedTitle;
            $rss .= '</title>';
            $rss .= '<atom:link href="'.$feed_url.'" rel="self" type="application/rss+xml" />';

            //Page loop
            $items = ($this->reverse) ? array_reverse($pages) : $pages;

            foreach ($items as $page) {
                if (!empty($page['date']) && ! $page['hidden']) {
                    $rss .= '<item>';
                    $rss .= '<title>';
                    $rss .= $page['title'];
                    $rss .= '</title>';

                    $rss .= '<description>';
                    $rss .= $page['description'];
                    $rss .= '</description>';

                    $rss .= '<link>';
                    $rss .= $page['url'];
                    $rss .= '</link>';

                    $rss .= '<pubDate>';
                    $rss .= date('c', $page['time']);
                    $rss .= '</pubDate>';

                    $rss .= '<guid>';
                    $rss .= $page['url'];
                    $rss .= '</guid>';

                    $rss .= '</item>';
                }
            }
            //RSS End
            $rss .= '</channel></rss>';
            //Show generated sitemap
            die($rss);

        }

    }
}
