<?php /* Starfish Framework Template protection */ die(); ?>
<div>
        <ul class="pagination">        
                <?php foreach ($data as $key=>$item): ?>

                <?php if (isset($item['link']) && strlen($item['link']) > 0): ?>

                <li class="<?php echo $item['class']; ?>"><a href="<?php echo $item['link']; ?>"><?php echo $item['name']; ?></a></li>

                <?php else: ?>

                <li class="<?php echo $item['class']; ?>"><span><?php echo $item['name']; ?></span></li>

                <?php endif; ?>

                <?php endforeach; ?>
        </ul>
</div>