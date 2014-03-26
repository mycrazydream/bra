<article>
    <h2><a href="$Link" title="Read more on &quot;{$Title}&quot;">$Title</a></h2>
	<div class="project-teaser-location">$Location</div>
	<% if Photo1 %>
	<a href="$Link" title="$Photo1.Title">
		<img src="$Photo1.SetWidth(100).URL" width="100" alt="$Photo1.Title image" class="teaser-project-image">
	</a>
	<% end_if %>
	<p>
		$teaserHighlights <a href="$Link" title="Read more on &quot;{$Title}&quot;" class="read-more">read more &raquo;</a>
	</p>
	<br class="clear" />
	
    
</article>