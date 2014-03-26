<?php
/**
 * Defines the HomePage page type
 */

class HomePage extends Page 
{
	static $db = array();
	
	static $has_one = array();
	
	//static $icon = "mysite/images/icons/home";
	
	function isHomePage(){
		$isHomePage = parent::isCurrent();
		return $isHomePage;
	}																																																																																																																											
}
 
class HomePage_Controller extends Page_Controller 
{
	public function LatestNews($num=5) 
	{
	    $holder = DataObject::get_one("ArticleHolder");       
	    return ($holder) ? DataList::create('ArticlePage')->where('"ParentID" = '.$holder->ID)->sort('Date DESC')->limit($num) : false;
	}
}