<div class="content-container">
    <article>
        <h1 class="staff-name">$Title</h1>
		<p class="staff-title">$ProTitle</p>
		<% if LicensesTxt %>
		<p class="staff-license">$LicensesTxt</p>
		<% end_if %>
        <div class="content staff-photo-bio">
            <a href="$Photo.URL" class="fancy staff-photo">
				<img src="$Photo.SetWidth(140).URL" width="140" alt="Staff photo of $Title">
			</a>
            $Bio
		</div>
		<div class="content">
			<% if Education %>
			<section class="staff-education">
				<label>Education</label>
				<p>$Education</p>
			</section>
			<% end_if %>
			
			<% if Experience %>
			<section class="staff-experience">
				<label>Years of Experience</label>
				<p>$Experience</p>
			</section>
			<% end_if %>
			
			<% if Affiliations %>
			<section class="staff-affiliations">
				<label>Professional Affiliations</label>
				<ul>
				<% loop $Affiliations.Items %>
				<li>$Value</li>
				<% end_loop %>
				</ul>
			</section>
			<% end_if %>
			
			<% if Projects %>
			<section class="staff-projects">
				<label>Notable Projects</label>
				<ul>
				<% loop $Projects.Items %>
				<li>$Value</li>
				<% end_loop %>
				</ul>
			</section>
			<% end_if %>
			
			<% if OldProjects %>
			<section class="staff-old-projects">
				<label>Notable Projects Prior to Joining Brooks Ransom Associates</label>
				<ul>
				<% loop $OldProjects.Items %>
				<li>$Value</li>
				<% end_loop %>
				</ul>
			</section>
			<% end_if %>
			
			<% if Related %>
			<section class="staff-related">
				<label>Related Experience</label>
				<ul>
				<% loop $Related.Items %>
				<li>$Value</li>
				<% end_loop %>
				</ul>
			</section>
			<% end_if %>
		</div>
    </article>
</div>
<% include SideBar %>