<div class="content-container">    
    <article>
        <h1>$Title</h1>
        $Content       
        <div class="content">$Content</div>
    </article>
    <% loop Children %>
	    <% include ArticleTeaser %>
	<% end_loop %>
        $Form
</div>
<% include SideBar %>