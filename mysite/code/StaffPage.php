<?php
 
class StaffPage extends Page 
{
    static $db = array(
		'ProTitle'		=> 'Varchar',
		'LicensesTxt'	=> 'Text',
		'Bio'			=> 'HTMLText',
		'Education'		=> 'Text',
		'Experience'	=> 'Text',
		'Affiliations'	=> 'MultiValueField',
		'Projects'		=> 'MultiValueField',
		'OldProjects'	=> 'MultiValueField',
		'Related'		=> 'MultiValueField'
    );

    static $has_one = array(
        'Photo' => 'Image'
    );
     
    public function getCMSFields() 
	{
        $fields = parent::getCMSFields();
        
		$fields->addFieldToTab('Root.Main', new TextField('ProTitle','Professional Title'), 'Content');
		$fields->addFieldToTab('Root.Main', new TextField('LicensesTxt','Licenses Text (to describe engineer licenses, not necessarily enumerate all)'), 'Content');
		$fields->addFieldToTab('Root.Main', new HTMLEditorField('Bio','Bio'), 'Content');
		$fields->addFieldToTab('Root.Main', new TextField('Education','Education'), 'Content');
		$fields->addFieldToTab('Root.Main', new TextField('Experience','Years of Experience'), 'Content');
		$fields->addFieldToTab('Root.Main', new MultiValueTextField('Affiliations','Professional Affiliations'), 'Content');
		$fields->addFieldToTab('Root.Main', new MultiValueTextField('Projects','Notable Projects'), 'Content');
		$fields->addFieldToTab('Root.Main', new MultiValueTextField('OldProjects','Notable Projects Prior to Joining Brooks Ransom Associates'), 'Content');
		$fields->addFieldToTab('Root.Main', new MultiValueTextField('Related','Related Experience'), 'Content');
        $fields->addFieldToTab("Root.Images", new UploadField('Photo'));
		$fields->removeByName('Content');
         
        return $fields;
    }
/*
	public function getMVField($fieldname){
		$field = $this->{$fieldname};
		foreach($field as $key => $options) { 
			$forms->push(new ArrayData(array (
				'Title'     => $options['title'],
				'ClassName' => $key,
				'Link' => $this->Link($key)
			)));
		}
	}
	*/
}
 
class StaffPage_Controller extends Page_Controller 
{
     
}