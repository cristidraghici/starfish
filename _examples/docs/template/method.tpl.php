<?php /* Starfish Framework Template protection */ die(); ?>

<h2><a href="#<?php echo $title; ?>" name="<?php echo $title; ?>"><?php echo $title; ?></a>(<?php echo $parameters; ?>)</h2>

<div class="method">
	
	<div class="comments">
		<?php echo $comments; ?>
	</div>
	
	<div class="body">
		<h3>Show code</h3>
		
		<div class="highlight">
		<?php echo $body; ?>
		</div>
	</div>
</div>