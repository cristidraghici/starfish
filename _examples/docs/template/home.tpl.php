<?php /* Starfish Framework Template protection */ die(); ?>
<div class="row">
	
	<div class="col-md-8">
		<?php echo $content; ?>
	</div>
	
	<div class="col-md-4">	
		
		<div class="tree-search" style="display: none;">
			<form>
				<div class="form-group">
					<div class="input-group">
						<input type="text" class="form-control" placeholder="">
						<div class="input-group-addon"><i class="glyphicon glyphicon-search"></i></div>
					</div>
				</div>
			</form>
		</div>

		<?php echo $tree; ?>
	</div>
	
</div>