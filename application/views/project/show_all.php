<h1>All Projects</h1>
<ul>
	<?php foreach ($projects as $project):?><li><?=html::anchor('project/view/'.$project->id, $project->name)?>
	</li><?php endforeach?>
</ul>