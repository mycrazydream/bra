<?php
/**
 * Defines the ProjectHolder page type
 */
class ProjectHolder extends Page {
	
    static $db = array();

    static $has_one = array();

	//static $icon = "framework/docs/en/tutorials/_images/treeicons/news-file.gif";
    
    static $allowed_children = array('ProjectPage');
}
  
class ProjectHolder_Controller extends Page_Controller 
{
	/*public function init() 
	{
	    RSSFeed::linkToFeed($this->Link() . "rss");   
	    parent::init();
	}
	
	public function rss() 
	{
	    $rss = new RSSFeed($this->Children(), $this->Link(), "BRA Consulting Structural Engineers");
	    $rss->outputToBrowser();
	}*/
}