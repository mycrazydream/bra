<?php
/**
 * Defines the ContactUs page type
 */

class ContactUs extends Page 
{
	static $db = array();
	
	static $has_one = array();
	
	//																																																																																																																													static $icon = "themes/tutorial/images/treeicons/home";
	
	function success(){
		return isset($_GET['success']) ? $_GET['success'] : false;
	}
}
 
class ContactUs_Controller extends Page_Controller 
{
	static $contact_subjects = array(
		'career'	=> 'Career',
		'file' 		=> 'File Uploads',
		'other'		=> 'Other',
		'project'	=> 'Project',
		'solicit'	=> 'Soliciting',
		'website'	=> 'Website'
	);
	
	public function ContactUsForm() 
	{
		$textarea = new TextareaField('Message', 'Message');
		$textarea->setRows(4)->setColumns(30);
		
		$email_field = new EmailField('Email','Email Address');
		$email_field->setAttribute('title','Please give us a real email address so we can get back to you');
		
		$attachment = new FileField('email-attachment', 'File to attach');
		$attachment->setAttribute('title','Fair warning: large files will take time to upload, and files larger than 20MB are probably never going to get through to us');
		$attachment->allowedExtensions = array('gif','jpg','doc','docx','xls','xlst','xlsx','cad','pdf','png',
							'txt','dwg','dwf','avi','dwfx','mpg','mpeg','dxf','psd','rvt','eps','ai','rfa','zip','rte');
							
		$subject = 	new DropdownField(
			'Subject',
			'Subject',
			self::$contact_subjects
		);
		
		$subject->setAttribute('title','Choose `Other` if any other subject does not seem correct');
		
		$checkbox_field = new CheckboxField('carbon-copy','Send copy to self');
		$checkbox_field->setAttribute('title','Check this box if you would like to receive a copy of your email as well');
		
		// Create fields
		$fields = new FieldList(
			$email_field,
			$subject,
			$textarea,
			$attachment,
			$checkbox_field,
			new HiddenField('s-p-a-m')
		);

		// Create actions
		$actions = new FieldList(
			new FormAction('sendMessage', 'Send Message')
		);
		
		$validator = new RequiredFields('Email', 'Message', 'Subject');

		$form = new Form($this, 'ContactUsForm', $fields, $actions, $validator);
		
		$form->setLegend("Send us a message");
		
		return $form;
	}
	
	public function sendMessage($data, $form)
	{
		$subject 	= self::$contact_subjects[$data['Subject']];
		$message 	= $data['Message'];
		$email		= $data['Email'];
		
		
		
$html = <<<EOT
<table width="100%" border="0" cellpadding="4" cellspacing="0">
<tr>
	<td>From:</td>
	<td>$email</td>
</tr>
<tr>
	<td>Subject:</td>
	<td>$subject</td>
</tr>
<tr>
	<td>Message:</td>
	<td>$message</td>
</tr>
</table>
EOT;

		$mailer	= new Email();
		$mailer->setTo("info@brooksransom.com");
		$mailer->setFrom("website@brooksransom.com");
		$mailer->setSubject("Contact from BRA website: $subject");
		$mailer->setBody($html);

		if(isset($data['carbon-copy']) && $data['carbon-copy']){
			$mailer->setCc($data['Email']);
		}
		
		$mailer->replyTo($data['Email']);
		if(isset($_FILES['email-attachment'])){
			$mailer->attachFile($_FILES['email-attachment']['tmp_name'], $_FILES['email-attachment']['name'], $_FILES['email-attachment']['type']);
		}
		
		$result 	= $mailer->send();
		$success 	= $result ? '1' : '0';
		
		$this->redirect("/about-us/contact-us/?success=$success");
	}
}