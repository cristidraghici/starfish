<?php /* Starfish Framework Template protection */ die(); ?>
<!-- Team Section -->
<section id="team" class="bg-light-gray">
        <div class="container">
                <div class="row">
                        <div class="col-lg-12 text-center">
                                <h2 class="section-heading">Our Amazing Team</h2>
                                <h3 class="section-subheading text-muted">Great many thanks to the people contributing to our project.</h3>
                        </div>
                </div>
                <div class="row">
                <?php foreach ($contributors as $key=>$value): ?>
                
                <div class="col-sm-2">
                    <div class="team-member">
                        <a href="<?php echo $value['html_url']; ?>"><img src="<?php echo $value['avatar_url']; ?>" class="img-responsive img-circle" alt="<?php echo $value['login']; ?>"></a>
                        <a href="<?php echo $value['html_url']; ?>">
                                <h4><?php echo $value['login']; ?></h4>
                        </a>
                    </div>
                </div>
                
                <?php endforeach; ?>
                </div>
        </div>
</section>