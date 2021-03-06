<div class="actions columns large-2 medium-3">
	<h3><?= __('Actions') ?></h3>
	<ul class="side-nav">
		<li><?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $tag->id], ['confirm' => __('Are you sure you want to delete # %s?', $tag->id)]) ?></li>
		<li><?= $this->Html->link(__('List Tags'), ['action' => 'index']) ?></li>
		<li><?= $this->Html->link(__('List BookmarksTags'), ['controller' => 'BookmarksTags', 'action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__('New Bookmarks Tag'), ['controller' => 'BookmarksTags', 'action' => 'add']) ?> </li>
		<li><?= $this->Html->link(__('List Bookmarks'), ['controller' => 'Bookmarks', 'action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__('New Bookmark'), ['controller' => 'Bookmarks', 'action' => 'add']) ?> </li>
                <li><?= $this->Html->link(__('Login'), ['controller' => 'Users', 'action' => 'login']) ?></li>
                <li><?= $this->Html->link(__('Logout'), ['controller' => 'Users', 'action' => 'logout']) ?></li>
	</ul>
</div>
<div class="tags form large-10 medium-9 columns">
<?= $this->Form->create($tag) ?>
	<fieldset>
		<legend><?= __('Edit Tag') ?></legend>
	<?php
		echo $this->Form->input('title');
	?>
	</fieldset>
<?= $this->Form->button(__('Submit')) ?>
<?= $this->Form->end() ?>
</div>
