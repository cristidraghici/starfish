<?php /* Starfish Framework Template protection */ die(); ?>
<div class="container-fluid">
        <ul class="cbp-vimenu">
                <li class="cbp-vicurrent"><a href="{/}notes">Notes</a></li>
                <li><a href="{/}categories">Categories</a></li>
                <li><a href="{/}users">Users</a></li>
                <li><a href="{/}logout" class="logout">Logout</a></li>
        </ul>

        <div class="main">

                <div class="row">
                        <div class="col-md-9">
                                <h1>Notes</h1>
                                <p>PostIT notes to your liking :)</p>

                                <?php foreach ($categories as $key=>$value): ?>
                                <h2><?php echo $value['name']; ?></h2>
                                <?php $count = 0; ?>
                                
                                <ul class="stories">
                                <?php foreach ($notes as $k2=>$v2): if ($v2['category_id'] == $value['_id']): $count++; ?>
                                <li class="story">
                                       <a class="note" href="{/}notes/edit/<?php echo $value['_id']; ?>"><?php echo nl2br($v2['content']); ?></a>
	                               <div class="clearfix"></div>
	                               <a href="{/}notes/delete/<?php echo $value['nr_crt']; ?>" class="del"><small>x</small></a>
                                </li>
                                <?php endif; endforeach; ?>
                                </ul>
                                
                                <div class="clearfix"></div>
                                <?php endforeach; ?>
                        </div>

                        <div class="col-md-3">
                                <h3>Add a new note</h3>
                                <form action="{/}notes/add" method="post" role="form">
                                        <div class="form-group">
                                                <label for="note">Content</label>
                                                <textarea class="form-control" id="content" name="content" rows=10 required=""></textarea>
                                        </div>
                                        <div class="form-group">
                                                <?php foreach ($categories as $key=>$value) : ?>
                                                <div class="radio">
                                                        <label>
                                                                <input type="radio" name="category_id" value="<?php echo $value['_id']; ?>" <?php if ($key==0) { echo 'checked=true'; } ?> >
                                                                <?php echo $value['name']; ?>
                                                        </label>
                                                </div>
                                                <?php endforeach; ?>
                                        </div>

                                        <button type="submit" class="btn btn-default">Add a note</button>
                                </form>

                        </div>
                </div>

        </div>
</div>