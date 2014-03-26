<div class="content-container">
    <h1>$Title</h1>
	<% if success %>
		<% if success == 0 %>
			<p>There has been an error sending your message. Please try again shortly.</p>
		<% else %>
			<p class="success">Thank you for your message. Someone will be in contact with you shortly.</p>
		<% end_if %>
	<% else %>
	$ContactUsForm
	<% end_if %>
</div>
<% include SideBar %>