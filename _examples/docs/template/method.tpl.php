<?php /* Starfish Framework Template protection */ die(); ?>

<h2><a href="{./}#<?php echo $anchor; ?>" name="<?php echo $anchor; ?>" id="<?php echo $anchor; ?>"><?php echo $title; ?></a>(<?php echo $parameters; ?>)</h2>

<div class="method">
	
	<div class="comments">
		<?php echo $comments; ?>
	</div>
	
	<div class="body">
		<h3><i class="glyphicon glyphicon-cog"></i> <a href="">Show code</a></h3>
		
		<div class="highlight">
		<?php echo $body; ?>
		</div>
	</div>
</div>