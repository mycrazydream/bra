<div class="content-container">
    <article>
        <h1 class="project-title">$Title</h1>
		<p class="project-location">$Location</p>
		<br class="clear">
		<div class="project-left">
			<div class="project-architect">
				<label class="project-label">Architect</label>
				<p>$Architect</p>
			</div>
			
			<% if OptionLabel && OptionValue %>
			<div class="project-option">
				<label class="project-label">$OptionLabel</label>
				<p>$OptionValue</p>
			</div>
			<% end_if %>
			
			<div class="project-footage">
				<label class="project-label">Square Footage</label>
				<br class="clear">
				<% if FootageLabel1 %>
				<label class="project-footage-label">$FootageLabel1</label>
				<% end_if %>
				<% if validFootageAmt(1) %>
				<p class="project-footage-val">$outputFootageAmt(1)</p>
				<br class="clear">
				<% end_if %>
				<% if FootageLabel2 %>
				<label class="project-footage-label">$FootageLabel2</label>
				<% end_if %>
				<% if validFootageAmt(2) %>
				<p class="project-footage-val">$outputFootageAmt(2)</p>
				<br class="clear">
				<% end_if %>
				<% if FootageLabel3 %>
				<label class="project-footage-label">$FootageLabel3</label>
				<% end_if %>
				<% if validFootageAmt(3) %>
				<p class="project-footage-val">$outputFootageAmt(3)</p>
				<br class="clear">
				<% end_if %>
				<hr>
				<label class="project-footage-label project-total-label">Total</label>
				<p class="project-footage-val">$getFootageTotal()</p>
				<br class="clear">
			</div>
		
			<div class="project-cost{$outputMarginNumber}">
				<label class="project-label">Estimated Cost</label>
				<p class="project-cost-val">$outputEstimatedCost()</p>
			</div>
			
			
		</div>
		<div class="project-right">
			<% if Photo1 %>
			<a href="$Photo1.URL" class="fancy project-photo project-photo-main" title="$Photo1.Title" rel="project_group">
				<img src="$Photo1.SetWidth(300).URL" width="300" alt="$Photo1.Title">
			</a>
			<% end_if %>
		</div>
		
		<br class="clear">
		
		<div class="project-thumbnails">
			<% if Photo2 %>
			<a href="$Photo2.URL" class="fancy project-photo project-photo-thumb" title="$Photo2.Title" rel="project_group">
				<img src="$Photo2.SetHeight(80).URL" height="80" alt="$Photo2.Title">
			</a>
			<% end_if %>
			
			<% if Photo3 %>
			<a href="$Photo3.URL" class="fancy project-photo project-photo-thumb" title="$Photo3.Title" rel="project_group">
				<img src="$Photo3.SetHeight(80).URL" height="80" alt="$Photo3.Title">
			</a>
			<% end_if %>
			
			<% if Photo4 %>
			<a href="$Photo4.URL" class="fancy project-photo project-photo-thumb" title="$Photo4.Title" rel="project_group">
				<img src="$Photo4.SetHeight(80).URL" height="80" alt="$Photo4.Title">
			</a>
			<% end_if %>
			
			<% if Photo5 %>
			<a href="$Photo5.URL" class="fancy project-photo project-photo-thumb" title="$Photo5.Title" rel="project_group">
				<img src="$Photo5.SetHeight(80).URL" height="80" alt="$Photo5.Title">
			</a>
			<% end_if %>
			
			<% if Photo6 %>
			<a href="$Photo6.URL" class="fancy project-photo project-photo-thumb" title="$Photo6.Title" rel="project_group">
				<img src="$Photo6.SetHeight(80).URL" height="80" alt="$Photo6.Title">
			</a>
			<% end_if %>
			
			<% if Photo7 %>
			<a href="$Photo7.URL" class="fancy project-photo project-photo-thumb" title="$Photo7.Title" rel="project_group">
				<img src="$Photo7.SetHeight(80).URL" height="80" alt="$Photo7.Title">
			</a>
			<% end_if %>
			
			<% if Photo8 %>
			<a href="$Photo8.URL" class="fancy project-photo project-photo-thumb" title="$Photo8.Title" rel="project_group">
				<img src="$Photo8.SetHeight(80).URL" height="80" alt="$Photo8.Title">
			</a>
			<% end_if %>
			
			<% if Photo9 %>
			<a href="$Photo9.URL" class="fancy project-photo project-photo-thumb" title="$Photo9.Title" rel="project_group">
				<img src="$Photo9.SetHeight(80).URL" height="80" alt="$Photo9.Title">
			</a>
			<% end_if %>
		</div>
		
		<% if Materials %>
		<div class="project-materials">
			<label class="project-label">Materials Used</label>
			<p>$Materials</p>
		</div>
		<% end_if %>
		
		<% if Highlights %>
		<div class="project-highlights">
			<label class="project-label">Project Highlights</label>
			<p>$Highlights</p>
		</div>
		<% end_if %>
		
		<% if OptionalText %>
		<div class="project-optional">
			<label class="project-label">$OptionalTextLabel</label>
			<p>$OptionalText</p>
		</div>
		<% end_if %>
    </article>
	<br><br>
	$backLink
</div>
<% include SideBar %>