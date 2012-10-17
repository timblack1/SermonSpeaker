<?php
defined('_JEXEC') or die;

JHTML::addIncludePath(JPATH_COMPONENT.'/helpers');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
JHtml::_('bootstrap.tooltip');

$user		= JFactory::getUser();
$canEdit	= $user->authorise('core.edit', 'com_sermonspeaker');
$canEditOwn	= $user->authorise('core.edit.own', 'com_sermonspeaker');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$limit 		= (int)$this->params->get('limit', '');
$player		= new SermonspeakerHelperPlayer($this->items);
?>
<div class="category-list<?php echo $this->pageclass_sfx;?> ss-sermons-container<?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	<?php endif;
	if ($this->params->get('show_category_title', 1) or $this->params->get('page_subheading')) : ?>
		<h2>
			<?php echo $this->escape($this->params->get('page_subheading'));
			if ($this->params->get('show_category_title')) : ?>
				<span class="subheading-category"><?php echo $this->category->title;?></span>
			<?php endif; ?>
		</h2>
	<?php endif;
	if ($this->params->get('show_description', 1) or $this->params->get('show_description_image', 1)) : ?>
		<div class="category-desc">
			<?php if ($this->params->get('show_description_image') and $this->category->getParams()->get('image')) : ?>
				<img src="<?php echo $this->category->getParams()->get('image'); ?>"/>
			<?php endif;
			if ($this->params->get('show_description') and $this->category->description) :
				echo JHtml::_('content.prepare', $this->category->description, '', 'com_sermonspeaker.category');
			endif; ?>
			<div class="clr"></div>
		</div>
	<?php endif;
	if (in_array('sermons:player', $this->columns) and count($this->items)) : ?>
		<div class="ss-sermons-player">
			<hr class="ss-sermons-player" />
			<?php if ($player->player != 'PixelOut') : ?>
				<div id="playing">
					<img id="playing-pic" class="picture" src="" />
					<span id="playing-duration" class="duration"></span>
					<div class="text">
						<span id="playing-title" class="title"></span>
						<span id="playing-desc" class="desc"></span>
					</div>
					<span id="playing-error" class="error"></span>
				</div>
			<?php endif;
			echo $player->mspace;
			echo $player->script;
			?>
			<hr class="ss-sermons-player" />
			<?php if ($player->toggle) : ?>
				<div>
					<img class="pointer" src="media/com_sermonspeaker/images/Video.png" onclick="Video()" alt="Video" title="<?php echo JText::_('COM_SERMONSPEAKER_SWITCH_VIDEO'); ?>" />
					<img class="pointer" src="media/com_sermonspeaker/images/Sound.png" onclick="Audio()" alt="Audio" title="<?php echo JText::_('COM_SERMONSPEAKER_SWITCH_AUDIO'); ?>" />
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<div class="cat-items">
		<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" id="adminForm" name="adminForm" class="form-inline">
			<?php if ($this->params->get('filter_field') or $this->params->get('show_pagination_limit')) : ?>
				<div class="filters btn-toolbar">
					<?php if ($this->params->get('filter_field')) :?>
						<div class="btn-group">
							<label class="filter-search-lbl element-invisible" for="filter-search">
								<span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
								<?php echo JText::_('JGLOBAL_FILTER_LABEL').'&#160;'; ?>
							</label>
							<input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="input-medium" onchange="document.adminForm.submit();" title="<?php echo JText::_('COM_SERMONSPEAKER_FILTER_SEARCH_DESC'); ?>" placeholder="<?php echo JText::_('COM_SERMONSPEAKER_FILTER_SEARCH_DESC'); ?>" />
						</div>
						<div class="btn-group filter-select">
							<select name="book" id="filter_books" class="input-medium" onchange="this.form.submit()">
								<?php echo JHtml::_('select.options', $this->books, 'value', 'text', $this->state->get('scripture.book'), true);?>
							</select>
							<select name="month" id="filter_months" class="input-medium" onchange="this.form.submit()">
								<option value="0"><?php echo JText::_('COM_SERMONSPEAKER_SELECT_MONTH'); ?></option>
								
								<?php echo JHtml::_('select.options', $this->months, 'value', 'text', $this->state->get('date.month'), true);?>
							</select>
							<select name="year" id="filter_years" class="input-medium" onchange="this.form.submit()">
								<option value="0"><?php echo JText::_('COM_SERMONSPEAKER_SELECT_YEAR'); ?></option>
								<?php echo JHtml::_('select.options', $this->years, 'year', 'year', $this->state->get('date.year'), true);?>
							</select>
						</div>
					<?php endif;
					if ($this->params->get('show_pagination_limit')) : ?>
						<div class="btn-group pull-right">
							<label class="element-invisible">
								<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>
							</label>
							<?php echo $this->pagination->getLimitBox(); ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="clearfix"></div>
			<?php if (!count($this->items)) : ?>
				<div class="no_entries alert alert-error"><?php echo JText::sprintf('COM_SERMONSPEAKER_NO_ENTRIES', JText::_('COM_SERMONSPEAKER_SERMONS')); ?></div>
			<?php else : ?>
				<table class="table table-striped table-hover table-condensed">
					<thead><tr>
						<?php if (in_array('sermons:num', $this->columns)) : ?>
							<th class="num hidden-phone hidden-tablet">
								<?php if (!$limit) :
									echo JHTML::_('grid.sort', 'COM_SERMONSPEAKER_SERMONNUMBER', 'sermon_number', $listDirn, $listOrder);
								else :
									echo JText::_('COM_SERMONSPEAKER_SERMONNUMBER');
								endif; ?>
							</th>
						<?php endif; ?>
						<th class="ss-title">
							<?php if (!$limit) :
								echo JHTML::_('grid.sort', 'JGLOBAL_TITLE', 'sermon_title', $listDirn, $listOrder);
							else :
								echo JText::_('JGLOBAL_TITLE');
							endif; ?>
						</th>
						<?php if (in_array('sermons:scripture', $this->columns)) : ?>
							<th class="ss-col ss-scripture hidden-phone">
								<?php if (!$limit) :
									echo JHTML::_('grid.sort', 'COM_SERMONSPEAKER_FIELD_SCRIPTURE_LABEL', 'book', $listDirn, $listOrder);
								else :
									echo JText::_('COM_SERMONSPEAKER_FIELD_SCRIPTURE_LABEL');
								endif; ?>
							</th>
						<?php endif;
						if (in_array('sermons:speaker', $this->columns)) : ?>
							<th class="ss-col ss-speaker hidden-phone">
								<?php if (!$limit) :
									echo JHTML::_('grid.sort', 'COM_SERMONSPEAKER_SPEAKER', 'name', $listDirn, $listOrder);
								else :
									echo JText::_('COM_SERMONSPEAKER_SPEAKER');
								endif; ?>
							</th>
						<?php endif;
						if (in_array('sermons:date', $this->columns)) : ?>
							<th class="ss-col ss-date">
								<?php if (!$limit) :
									echo JHTML::_('grid.sort', 'COM_SERMONSPEAKER_FIELD_DATE_LABEL', 'sermons.sermon_date', $listDirn, $listOrder);
								else :
									echo JText::_('COM_SERMONSPEAKER_FIELD_DATE_LABEL');
								endif; ?>
							</th>
						<?php endif;
						if (in_array('sermons:length', $this->columns)) : ?>
							<th class="ss-col ss-length hidden-phone hidden-tablet">
								<?php if (!$limit) :
									 echo JHTML::_('grid.sort', 'COM_SERMONSPEAKER_FIELD_LENGTH_LABEL', 'sermon_time', $listDirn, $listOrder);
								else :
									echo JText::_('COM_SERMONSPEAKER_FIELD_LENGTH_LABEL');
								endif; ?>
							</th>
						<?php endif;
						if (in_array('sermons:series', $this->columns)) : ?>
							<th class="ss-col ss-series hidden-phone">
								<?php if (!$limit) :
									 echo JHTML::_('grid.sort', 'COM_SERMONSPEAKER_SERIES', 'series_title', $listDirn, $listOrder);
								else :
									echo JText::_('COM_SERMONSPEAKER_SERIES');
								endif; ?>
							</th>
						<?php endif;
						if (in_array('sermons:addfile', $this->columns)) : ?>
							<th class="ss-col ss-addfile hidden-phone">
								<?php if (!$limit) :
									 echo JHTML::_('grid.sort', 'COM_SERMONSPEAKER_ADDFILE', 'addfileDesc', $listDirn, $listOrder);
								else :
									echo JText::_('COM_SERMONSPEAKER_ADDFILE');
								endif; ?>
							</th>
						<?php endif;
						if (in_array('sermons:hits', $this->columns)) : ?>
							<th class="ss-col ss-hits hidden-phone hidden-tablet">
								<?php if (!$limit) :
									echo JHTML::_('grid.sort', 'JGLOBAL_HITS', 'hits', $listDirn, $listOrder);
								else :
									echo JText::_('JGLOBAL_HITS');
								endif; ?>
							</th>
						<?php endif;
						if (in_array('sermons:download', $this->columns)) : 
							$prio	= $this->params->get('fileprio'); ?>
							<th class="ss-col ss-dl hidden-phone"></th>
						<?php endif; ?>
					</tr></thead>
				<!-- Begin Data -->
					<tbody>
						<?php foreach($this->items as $i => $item) : ?>
							<tr id="sermon<?php echo $i; ?>" class="<?php echo ($item->state) ? '': 'system-unpublished '; ?>cat-list-row<?php echo $i % 2; ?>">
								<?php if (in_array('sermons:num', $this->columns)) : ?>
									<td class="num hidden-phone hidden-tablet">
										<?php echo $item->sermon_number; ?>
									</td>
								<?php endif; ?>
								<td class="ss-title">
									<?php echo SermonspeakerHelperSermonspeaker::insertSermonTitle($i, $item, $player);
									if ($canEdit or ($canEditOwn and ($user->id == $item->created_by))) : ?>
										<span class="list-edit pull-left width-50">
											<?php echo JHtml::_('icon.edit', $item, $this->params, array('type' => 'sermon')); ?>
										</span>
									<?php endif; ?>
									<?php if (!$item->state) : ?>
										<span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
									<?php endif; ?>
								</td>
								<?php if (in_array('sermons:scripture', $this->columns)) : ?>
									<td class="ss-col ss-scripture hidden-phone">
										<?php $scriptures = SermonspeakerHelperSermonspeaker::insertScriptures($item->scripture, '<br />');
										echo JHTML::_('content.prepare', $scriptures); ?>
									</td>
								<?php endif;
								if (in_array('sermons:speaker', $this->columns)) : ?>
									<td class="ss-col ss-speaker hidden-phone">
										<?php if ($item->speaker_state):
											echo SermonspeakerHelperSermonSpeaker::SpeakerTooltip($item->speaker_slug, $item->pic, $item->name);
										else :
											echo $item->name;
										endif; ?>
									</td>
								<?php endif;
								if (in_array('sermons:date', $this->columns)) : ?>
									<td class="ss-col ss-date">
										<?php if ($item->sermon_date != '0000-00-00 00:00:00'):
											echo JHTML::date($item->sermon_date, JText::_($this->params->get('date_format')), true);
										endif; ?>
									</td>
								<?php endif;
								if (in_array('sermons:length', $this->columns)) : ?>
									<td class="ss-col ss-length hidden-phone hidden-tablet">
										<?php echo SermonspeakerHelperSermonspeaker::insertTime($item->sermon_time); ?>
									</td>
								<?php endif;
								if (in_array('sermons:series', $this->columns)) : ?>
									<td class="ss-col ss-series hidden-phone">
										<?php if ($item->series_state): ?>
											<a href="<?php echo JRoute::_(SermonspeakerHelperRoute::getSerieRoute($item->series_slug)); ?>">
												<?php echo $item->series_title; ?>
											</a>
										<?php else:
											echo $item->series_title;
										endif; ?>
									</td>
								<?php endif;
								if (in_array('sermons:addfile', $this->columns)) : ?>
									<td class="ss-col ss-addfile hidden-phone">
										<?php echo SermonspeakerHelperSermonspeaker::insertAddfile($item->addfile, $item->addfileDesc); ?>
									</td>
								<?php endif;
								if (in_array('sermons:hits', $this->columns)) : ?>
									<td class="ss-col ss-hits hidden-phone hidden-tablet">
										<?php echo $item->hits; ?>
									</td>
								<?php endif;
								if (in_array('sermons:download', $this->columns)) : 
									$file = ($item->videofile && ($prio || !$item->audiofile)) ? 'video' : 'audio'; ?>
									<td class="ss-col ss-dl hidden-phone">
										<?php echo SermonspeakerHelperSermonspeaker::insertdlbutton($item->slug, $file, 3); ?>
									</td>
								<?php endif; ?>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif;
			if ($user->authorise('core.edit.own', 'com_sermonspeaker')) :
				echo JHtml::_('icon.create', $this->category, $this->params);
			endif;
			if ($this->params->get('show_pagination') and ($this->pagination->get('pages.total') > 1)) : ?>
				<div class="pagination">
					<?php if ($this->params->get('show_pagination_results', 1)) : ?>
						<p class="counter pull-right">
							<?php echo $this->pagination->getPagesCounter(); ?>
						</p>
					<?php endif;
					echo $this->pagination->getPagesLinks(); ?>
				</div>
			<?php endif; ?>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<input type="hidden" name="limitstart" value="" />
		</form>
	</div>
	<?php if (!empty($this->children[$this->category->id]) and $this->maxLevel != 0) : ?>
		<div class="cat-children">
			<h3><?php echo JTEXT::_('JGLOBAL_SUBCATEGORIES'); ?></h3>
			<?php echo $this->loadTemplate('children30'); ?>
		</div>
	<?php endif; ?>
</div>