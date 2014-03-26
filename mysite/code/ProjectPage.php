<?php
/**
 * Defines the ProjectPage page type
 */
class ProjectPage extends Page {
    static $db = array(
		'Location'		=> 'Varchar',
		'Architect'		=> 'Varchar',
		'FootageLabel1'	=> 'Varchar',
		'FootageAmt1'	=> 'Decimal',
		'FootageLabel2'	=> 'Varchar',
		'FootageAmt2'	=> 'Decimal',
		'FootageLabel3'	=> 'Varchar',
		'FootageAmt3'	=> 'Decimal',
		'EstimatedCost'	=> 'Currency',
		'OptionLabel'	=> 'Varchar',
		'OptionValue'	=> 'Varchar',
		'Materials'		=> 'HTMLText',
		'Highlights'	=> 'HTMLText',
		'OptionalTextLabel' => 'Varchar',
		'OptionalText'	=> 'HTMLText'
    );

    static $has_one = array(
		'Photo1'		=> 'Image',
		'Photo2'		=> 'Image',
		'Photo3'		=> 'Image',
		'Photo4'		=> 'Image',
		'Photo5'		=> 'Image',
		'Photo6'		=> 'Image',
		'Photo7'		=> 'Image',
		'Photo8'		=> 'Image',
		'Photo9'		=> 'Image'
	);

	//static $icon = "framework/docs/en/tutorials/_images/treeicons/news-file.gif";
	
	public function getCMSFields() 
	{
	    $fields = parent::getCMSFields();

	    $fields->addFieldToTab('Root.Main', $locationField = new TextField('Location','Location of Project'));
		//$locationField->setConfig('showcalendar', true);

	    $fields->addFieldToTab('Root.Main', new TextField('Architect','Architect Name'));
		$fields->addFieldToTab('Root.Main', new TextField('FootageLabel1','Square Footage Label 1'));
		$fields->addFieldToTab('Root.Main', new NumericField('FootageAmt1','Square Footage Amount 1'));
		$fields->addFieldToTab('Root.Main', new TextField('FootageLabel2','Square Footage Label 2'));
		$fields->addFieldToTab('Root.Main', new NumericField('FootageAmt2','Square Footage Amount 2'));
		$fields->addFieldToTab('Root.Main', new TextField('FootageLabel3','Square Footage Label 3'));
		$fields->addFieldToTab('Root.Main', new NumericField('FootageAmt3','Square Footage Amount 3'));
		$fields->addFieldToTab('Root.Main', new CurrencyField('EstimatedCost','Estimated Cost'));
		$fields->addFieldToTab('Root.Main', new TextField('OptionLabel','Option Label'));
		$fields->addFieldToTab('Root.Main', new TextField('OptionValue','Option Value'));
		$fields->addFieldToTab('Root.Main', new HTMLEditorField('Materials','Materials Used'));
		$fields->addFieldToTab('Root.Main', new HTMLEditorField('Highlights','Project Highlights'));
		$fields->addFieldToTab('Root.Main', new TextField('OptionalTextLabel','Optional Text Label'));
		$fields->addFieldToTab('Root.Main', new HTMLEditorField('OptionalText','Optional Text'));
		$fields->removeByName('Content');
		
		$fields->addFieldToTab("Root.Images", new UploadField('Photo1', 'First Photo'));
		$fields->addFieldToTab("Root.Images", new UploadField('Photo2', 'Second Photo'));
		$fields->addFieldToTab("Root.Images", new UploadField('Photo3', 'Third Photo'));
		$fields->addFieldToTab("Root.Images", new UploadField('Photo4', 'Fourth Photo'));
		$fields->addFieldToTab("Root.Images", new UploadField('Photo5', 'Fifth Photo'));
		$fields->addFieldToTab("Root.Images", new UploadField('Photo6', 'Sixth Photo'));
		$fields->addFieldToTab("Root.Images", new UploadField('Photo7', 'Seventh Photo'));
		$fields->addFieldToTab("Root.Images", new UploadField('Photo8', 'Eighth Photo'));
		$fields->addFieldToTab("Root.Images", new UploadField('Photo9', 'Ninth Photo'));

	    return $fields;
	}
	
	function teaserHighlights(){
		return strip_tags($this->Highlights);
	}
	
	public function validFootageTotal(){
		$total = $this->getFootageTotal();
		return $total>0 && ($this->FootageAmt2>0 || $this->FootageAmt3>0) ? true : false;
	}
	
	public function getFootageTotal(){
		$FootageTotal = $this->FootageAmt1 ? $this->FootageAmt1 : 0;
		$FootageTotal += $this->FootageAmt2 ? $this->FootageAmt2 : 0;
		$FootageTotal += $this->FootageAmt3 ? $this->FootageAmt3 : 0;
		
		return number_format($FootageTotal);
	}
	
	public function validFootageAmt($num){
		$valid = false;
		switch($num){
			case 1:
				if($this->FootageAmt1 > 0) $valid = true;
			break;
			case 2:
				if($this->FootageAmt2 > 0) $valid = true;
			break;
			case 3:
				if($this->FootageAmt3 > 0) $valid = true;
			break;
		}
		return $valid;
	}
	
	public function outputMarginNumber(){
		$cnt = 0;
		if($this->FootageAmt1 > 0 || $this->FootageAmt2 > 0 || $this->FootageAmt3 > 0){
			$cnt+=2;
		}
		if($this->FootageAmt1 > 0) $cnt++;
		if($this->FootageAmt2 > 0) $cnt++;
		if($this->FootageAmt3 > 0) $cnt++;
		if($this->OptionLabel) $cnt++;
		if($this->OptionValue) $cnt++;
		
		return $cnt;
	}
	
	public function outputFootageAmt($num){
		$output = 0;
		switch($num){
			case 1:
				$output = number_format($this->FootageAmt1);
			break;
			case 2:
				$output = number_format($this->FootageAmt2);
			break;
			case 3:
				$output = number_format($this->FootageAmt3);
			break;
		}
		return $output;
	}
	
	public function validEstimatedCost(){
		return $this->EstimatedCost>0 ? true : false;
	}
	
	public function outputEstimatedCost(){
		return '$'.number_format($this->EstimatedCost);
	}
	
	function backLink(){
		$backLink = preg_replace('/[^\/]+\/$/','',$_SERVER["REQUEST_URI"]);
		preg_match('/([^\/]+)\/$/',$backLink,$m);
		return '<a href="'.$backLink.'" class="back-link">&laquo; Back to '.ucwords(str_replace('-',' ',$m[1])).'</a>';
	}
}
 
class ProjectPage_Controller extends Page_Controller {
     
}