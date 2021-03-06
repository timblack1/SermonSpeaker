<?php
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');

$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$saveOrder	= $listOrder == 'series.ordering';
if ($saveOrder) :
	$saveOrderingUrl = 'index.php?option=com_sermonspeaker&task=series.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'serieList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
endif;
$assoc		= !empty(JFactory::getApplication()->item_associations);
?>
<script type="text/javascript">
	Joomla.orderTable = function() {
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		} else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_sermonspeaker&view=series'); ?>" method="post" name="adminForm" id="adminForm">
<?php if(!empty( $this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo JText::_('COM_SERMONSPEAKER_FILTER_SEARCH_DESC');?></label>
				<input type="text" name="filter_search" placeholder="<?php echo JText::_('COM_SERMONSPEAKER_FILTER_SEARCH_DESC'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_SERMONSPEAKER_FILTER_SEARCH_DESC'); ?>" />
			</div>
			<div class="btn-group pull-left hidden-phone">
				<button class="btn tip" type="submit" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
				<button class="btn tip" type="button" onclick="document.id('filter_search').value='';this.form.submit();" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC');?></label>
				<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC');?></option>
					<option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING');?></option>
					<option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING');?></option>
				</select>
			</div>
			<div class="btn-group pull-right">
				<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY');?></label>
				<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
					<?php echo JHtml::_('select.options', $this->getSortFields(), 'value', 'text', $listOrder);?>
				</select>
			</div>
		</div>
		<div class="clearfix"> </div>

		<table class="table table-striped" id="serieList">
			<thead>
				<tr>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'series.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
					</th>
					<th width="1%" class="hidden-phone">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>
					<th width="1%" style="min-width:40px" class="nowrap center">
						<?php echo JHtml::_('grid.sort', 'JSTATUS', 'series.state', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'series.title', $listDirn, $listOrder); ?>
					</th>
					<?php if ($assoc) : ?>
						<th width="5%" class="nowrap hidden-phone">
							<?php echo JHtml::_('grid.sort', 'COM_SERMONSPEAKER_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder); ?>
						</th>
					<?php endif;?>
					<th width="5%" class="hidden-phone">
						<?php echo JHtml::_('grid.sort',  'COM_SERMONSPEAKER_FIELD_PICTURE_LABEL', 'series.avatar', $listDirn, $listOrder); ?>
					</th>
					<th width="7%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort',  'JGLOBAL_HITS', 'series.hits', $listDirn, $listOrder); ?>
					</th>
					<th width="5%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'series.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($this->items as $i => $item) :
				$ordering	= ($listOrder == 'series.ordering');
				$canEdit	= $user->authorise('core.edit', 'com_sermonspeaker.category.'.$item->catid);
				$canCheckin	= $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
				$canEditOwn	= $user->authorise('core.edit.own', 'com_sermonspeaker.category.'.$item->catid) && $item->created_by == $userId;
				$canChange	= $user->authorise('core.edit.state', 'com_sermonspeaker.category.'.$item->catid) && $canCheckin;
				?>
				<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid; ?>">
					<td class="order nowrap center hidden-phone">
					<?php if ($canChange) :
						$disableClassName = '';
						$disabledLabel	  = '';

						if (!$saveOrder) :
							$disabledLabel    = JText::_('JORDERINGDISABLED');
							$disableClassName = 'inactive tip-top';
						endif; ?>
						<span class="sortable-handler <?php echo $disableClassName?>" title="<?php echo $disabledLabel?>" rel="tooltip">
							<i class="icon-menu"></i>
						</span>
						<input type="text" style="display:none"  name="order[]" size="5" value="<?php echo $item->ordering;?>" class="width-20 text-area-order " />
					<?php else : ?>
						<span class="sortable-handler inactive" >
							<i class="icon-menu"></i>
						</span>
					<?php endif; ?>
					</td>
					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td class="center">
						<div class="btn-group">
							<?php echo JHtml::_('jgrid.published', $item->state, $i, 'series.', $canChange); ?>
							<?php echo JHtml::_('jgrid.isdefault', $item->home, $i, 'series.', $canChange && !$item->home);?>
						</div>
					</td>
					<td class="nowrap has-context">
						<div class="pull-left">
							<?php if ($item->checked_out) : ?>
								<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'series.', $canCheckin); ?>
							<?php endif; ?>
							<?php if ($canEdit || $canEditOwn) : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_sermonspeaker&task=serie.edit&id=' . $item->id);?>" title="<?php echo JText::_('JACTION_EDIT');?>">
									<?php echo $this->escape($item->title); ?></a>
							<?php else : ?>
								<span title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias));?>"><?php echo $this->escape($item->title); ?></span>
							<?php endif; ?>
							<div class="small">
								<?php echo JText::_('JCATEGORY') . ": " . $this->escape($item->category_title); ?>
							</div>
						</div>
						<div class="pull-left">
							<?php
								// Create dropdown items
								JHtml::_('dropdown.edit', $item->id, 'serie.');
								JHtml::_('dropdown.divider');
								if ($item->state) :
									JHtml::_('dropdown.unpublish', 'cb' . $i, 'series.');
								else :
									JHtml::_('dropdown.publish', 'cb' . $i, 'series.');
								endif;

								JHtml::_('dropdown.divider');

								if ($this->state->get('filter.state') == 2) :
									JHtml::_('dropdown.unarchive', 'cb' . $i, 'series.');
								else :
									JHtml::_('dropdown.archive', 'cb' . $i, 'series.');
								endif;

								if ($item->checked_out) :
									JHtml::_('dropdown.checkin', 'cb' . $i, 'series.');
								endif;

								if ($this->state->get('filter.state') == -2) :
									JHtml::_('dropdown.untrash', 'cb' . $i, 'series.');
								else :
									JHtml::_('dropdown.trash', 'cb' . $i, 'series.');
								endif;

								// Render dropdown list
								echo JHtml::_('dropdown.render');
								?>
						</div>
					</td>
					<?php if ($assoc) : ?>
						<td class="hidden-phone">
							<?php if ($item->association) : ?>
								<?php echo JHtml::_('sermonspeakeradministrator.association', $item->id, 'serie'); ?>
							<?php endif; ?>
						</td>
					<?php endif;?>
					<td class="center small hidden-phone">
						<?php if (!$item->avatar){
							$item->avatar = JURI::root().'media/com_sermonspeaker/images/'.$this->state->get('params')->get('defaultpic', 'nopict.jpg');
						}
						if (!parse_url($item->avatar, PHP_URL_SCHEME)) {
							$item->avatar = JURI::root().trim($item->avatar, '/.');
						} ?>
						<img src="<?php echo $item->avatar; ?>" border="1" width="50" height="50">
					</td>
					<td class="center small hidden-phone">
						<?php echo $item->hits; ?>
						<?php if ($canEdit || $canEditOwn) : ?>
							&nbsp;<a href="index.php?option=com_sermonspeaker&task=serie.reset&id=<?php echo $item->id; ?>"><i class="icon-loop" rel="tooltip" title="<?php echo JText::_('JSEARCH_RESET'); ?>"> </i></a>
						<?php endif; ?>
					</td>
					<td class="small hidden-phone">
						<?php if ($item->language == '*'):?>
							<?php echo JText::alt('JALL', 'language'); ?>
						<?php else:?>
							<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
						<?php endif;?>
					</td>
					<td class="center hidden-phone">
						<?php echo (int) $item->id; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php echo $this->pagination->getListFooter(); ?>
		<?php //Load the batch processing form. ?>
		<?php echo $this->loadTemplate('batch'); ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>