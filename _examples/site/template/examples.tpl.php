<?php /* Starfish Framework Template protection */ die(); ?>

<!-- Examples Grid Section -->
<section id="examples" class="bg-light-gray">
        <div class="container">
                <div class="row">
                        <div class="col-lg-12 text-center">
                                <h2 class="section-heading">Examples</h2>
                                <h3 class="section-subheading text-muted">Below you will find example implementations of Starfish PHP Framework.</h3>
                        </div>
                </div>

                <div class="row">

                        <?php foreach ($examples as $key=>$value): ?>
                        <div class="col-md-4 col-sm-6 portfolio-item">
                                <a href="#portfolio-<?php echo $value['title']; ?>" class="portfolio-link" data-toggle="modal">
                                        <img src="<?php echo $value['screenshot']; ?>" class="img-responsive" alt="" style="max-height: 200px;">
                                </a>
                                <div class="portfolio-caption">
                                        <h4><?php echo $value['title']; ?></h4>
                                        <p class="text-muted">Last update: <?php echo $value['modified']; ?></p>
                                </div>
                        </div>
                        <?php endforeach; ?>
                </div>
        </div>
</section>

<!-- Portfolio Modals -->
<!-- Use the modals below to showcase details about your portfolio projects! -->

<!-- Portfolio Modal 1 -->
<?php foreach ($examples as $key=>$value): ?>
<div class="portfolio-modal modal fade" id="portfolio-<?php echo $value['title']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="container">
                <div class="row">
                        <div class="modal-content">
                                <div class="close-modal" data-dismiss="modal">
                                        <div class="lr">
                                                <div class="rl">
                                                </div>
                                        </div>
                                </div>

                                <div class="col-lg-8 col-lg-offset-2">
                                        <div class="modal-body">
                                                <!-- Project Details Go Here -->
                                                <h2><?php echo $value['title']; ?></h2>
                                                <img class="img-responsive" src="<?php echo $value['screenshot']; ?>" alt="">
                                                <br style="clear: both;">

                                                <?php echo $value['content']; ?>

                                                <button type="button" class="btn btn-primary" data-dismiss="modal"><i class="fa fa-times"></i> Close Project</button>
                                        </div>
                                </div>
                        </div>
                </div>
        </div>
</div>
<?php endforeach; ?>
