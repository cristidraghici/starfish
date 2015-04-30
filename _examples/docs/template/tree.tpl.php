<?php /* Starfish Framework Template protection */ die(); ?>
<?php if (count($list) > 0): ?>
<ul class="tree">
	<?php foreach ($list as $key=>$value): ?>
	<li>
		<?php if ($value['type'] == 2): ?>
		<a href="{/}class/<?php echo $value['_id']; ?>"><?php echo $value['title']; ?></a>
		<?php else: ?>
		<?php echo $value['title']; ?>
		<?php endif; ?>
		
		<?php if (isset($value['children'])): ?>
		<?php echo view('tree', array('list'=>$value['children'])); ?>
		<?php endif; ?>
	</li>
	<?php endforeach; ?>
</ul>
<?php endif; ?>