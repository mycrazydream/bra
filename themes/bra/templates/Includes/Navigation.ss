<nav class="primary">
	<span class="nav-open-button">²</span>
	<ul>
		<% loop Menu(1) %>	  
			<li class="$LinkingMode<% if Last %> last<% end_if %>">
				<div class="vline"></div>
				<a href="$Link" data-title="$Title.XML">$MenuTitle.XML</a>
				<div class="arrow-up"></div>
				<div class="dark-red"></div>
			</li>
		<% end_loop %>
		<div style="clear:both;width:0px;height:0px;"></div>
	</ul>
</nav>