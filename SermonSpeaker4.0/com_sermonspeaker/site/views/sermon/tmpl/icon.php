<?php
defined('_JEXEC') or die;
JHTML::addIncludePath(JPATH_COMPONENT.'/helpers');
JHTML::_('behavior.tooltip');
JHTML::_('behavior.modal');
JHTML::stylesheet('media/com_sermonspeaker/css/icon.css');
$user		= JFactory::getUser();
$canEdit	= $user->authorise('core.edit', 'com_sermonspeaker');
$canEditOwn	= $user->authorise('core.edit.own', 'com_sermonspeaker');
$config = array(
		'alt_player' => 1,
		'count' => 1,
		'type' => 'audio',
	);
$player = new SermonspeakerHelperPlayer($this->item, $config);
?>
<div class="ss-sermon-container<?php echo htmlspecialchars($this->params->get('pageclass_sfx')); ?>">
<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
<?php endif; ?>
<h2><a href="<?php echo JRoute::_(SermonspeakerHelperRoute::getSermonRoute($this->item->slug)); ?>"><?php echo $this->item->sermon_title; ?></a></h2>
<?php if ($canEdit || ($canEditOwn && ($user->id == $this->item->created_by))) : ?>
	<ul class="actions">
		<li class="edit-icon">
			<?php echo JHtml::_('icon.edit', $this->item, $this->params, array('type' => 'sermon')); ?>
		</li>
	</ul>
<?php endif; ?>
<div id="sermon-infobox">
	<div id="sermon-player-container">
		<?php if (in_array('sermon:player', $this->columns)) : ?>
			<div class="ss-player">
				<?php if (!$player->status): ?>
					<span class="no_entries"><?php echo $player->error; ?></span>
				<?php else:
					echo $player->mspace;
					echo $player->script;
				endif; ?>
			</div>
		<?php endif;
		if ($this->params->get('popup_player') || in_array('sermon:download', $this->columns)) : ?>
			<div class="ss-mp3-links">
			<?php if ($this->params->get('popup_player')) : ?>
				<a href="<?php echo JURI::current(); ?>" class="new-window" onclick="popup = window.open('<?php echo JRoute::_(SermonspeakerHelperRoute::getSermonRoute($this->item->slug).'&layout=popup&tmpl=component'); ?>', 'PopupPage', 'height=<?php echo $player->popup['height']; ?>,width=<?php echo $player->popup['width']; ?>,scrollbars=yes,resizable=yes'); return false">
					<?php echo JText::_('COM_SERMONSPEAKER_POPUPPLAYER'); ?>
				</a>
			<?php endif;
			if ($this->params->get('popup_player') && in_array('sermon:download', $this->columns)) : ?>
				<br />
			<?php endif;
			if ($this->item->audiofile && in_array('sermon:download', $this->columns)) : ?>
				<a id="sermon_download" href="<?php echo JRoute::_('index.php?task=download&id='.$this->item->slug.'&type=audio'); ?>" class="download">
					<?php echo JText::_('COM_SERMONSPEAKER_DOWNLOADBUTTON_AUDIO'); ?>
				</a>
			<?php endif; ?>
			</div>
		<?php endif;
		if (in_array('sermon:player', $this->columns) && $this->item->videofile) :
			if ($player->status) : ?>
				<br style="clear:left;" />
			<?php endif; ?>
			<div class="ss-player-video">
				<a href="<?php echo JURI::current(); ?>" onclick="popup = window.open('<?php echo  JRoute::_(SermonspeakerHelperRoute::getSermonRoute($this->item->slug).'&layout=popup&type=video&tmpl=component'); ?>', 'PopupPage', 'height=<?php echo $player->popup['height']; ?>,width=<?php echo $player->popup['width']; ?>,scrollbars=yes,resizable=yes'); return false">
					<img src="media/com_sermonspeaker/images/player.png">
				</a>
			</div>
		<?php endif;
		if ($this->item->videofile && in_array('sermon:download', $this->columns)) : ?>
			<div class="ss-mp3-links">
				<a id="sermon_download" href="<?php echo JRoute::_('index.php?task=download&id='.$this->item->slug.'&type=video'); ?>" class="download">
					<?php echo JText::_('COM_SERMONSPEAKER_DOWNLOADBUTTON_VIDEO'); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
	<div class="ss-fields-container">
		<?php if (in_array('sermon:speaker', $this->columns) && $this->item->speaker_id): ?>
			<div class="ss-field field-speaker" title="<?php echo JText::_('COM_SERMONSPEAKER_SPEAKER'); ?>">
				<?php if ($this->item->speaker_state):
					echo SermonspeakerHelperSermonSpeaker::SpeakerTooltip($this->item->speaker_slug, $this->item->pic, $this->item->name); 
				else: 
					echo $this->item->name;
				endif; ?>
			</div>
		<?php endif; ?>
		<?php if (in_array('sermon:scripture', $this->columns) && $this->item->scripture) : ?>
			<div class="ss-field field-bible" title="<?php echo JText::_('COM_SERMONSPEAKER_FIELD_SCRIPTURE_LABEL'); ?>">
				<?php $scriptures = SermonspeakerHelperSermonspeaker::insertScriptures($this->item->scripture, '; ');
				echo JHTML::_('content.prepare', $scriptures); ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="ss-fields-container">
		<?php if (in_array('sermon:date', $this->columns) && ($this->item->sermon_date != '0000-00-00 00:00:00')) : ?>
			<div class="ss-field field-calendar" title="<?php echo JText::_('COM_SERMONSPEAKER_FIELD_DATE_LABEL'); ?>">
				<?php echo JHTML::Date($this->item->sermon_date, JText::_('DATE_FORMAT_LC1'), true); ?>
			</div>
		<?php endif;
		if (in_array('sermon:length', $this->columns) && ($this->item->sermon_time != '00:00:00')) : ?>
			<div class="ss-field field-time" title="<?php echo JText::_('COM_SERMONSPEAKER_FIELD_LENGTH_LABEL'); ?>">
				<?php echo SermonspeakerHelperSermonspeaker::insertTime($this->item->sermon_time); ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="ss-fields-container">
		<?php if (in_array('sermon:series', $this->columns) && $this->item->series_id) : ?>
			<div class="ss-field field-series" title="<?php echo JText::_('COM_SERMONSPEAKER_SERIE_TITLE'); ?>">
				<?php if ($this->item->series_state) : ?>
					<a href="<?php echo JRoute::_(SermonspeakerHelperRoute::getSerieRoute($this->item->series_slug)); ?>">
				<?php echo $this->escape($this->item->series_title); ?></a>
				<?php else :
					echo $this->escape($this->item->series_title);
				endif; ?>
			</div>
		<?php endif;
		if (in_array('sermon:addfile', $this->columns) && $this->item->addfile) : ?>
			<div class="ss-field field-addfile" title="<?php echo JText::_('COM_SERMONSPEAKER_ADDFILE'); ?>">
				<?php echo SermonspeakerHelperSermonspeaker::insertAddfile($this->item->addfile, $this->item->addfileDesc, 1); ?>
			</div>
		<?php endif; ?>
	</div>
	<br style="clear:both" />
</div>
<?php if (in_array('sermon:notes', $this->columns) && $this->item->notes) : ?>
	<div class="ss-notes">
		<?php echo JHTML::_('content.prepare', $this->item->notes); ?>
	</div>
<?php endif;
if ($this->params->get('enable_keywords', 0)):
	$tags = SermonspeakerHelperSermonspeaker::insertSearchTags($this->item); 
	if ($tags): ?>
		<div class="tag"><?php echo JText::_('COM_SERMONSPEAKER_TAGS').' '.$tags; ?></div>
	<?php endif;
endif;
if (in_array('sermon:hits', $this->columns) && $this->item->hits) : ?>
	<div class="hits" title="<?php echo JText::_('JGLOBAL_HITS'); ?>">
		<?php echo JText::_('JGLOBAL_HITS').': '.$this->item->hits; ?>
	</div>
<?php endif;
// Support for JComments
$comments = JPATH_BASE.'/components/com_jcomments/jcomments.php';
if ($this->params->get('enable_jcomments') && file_exists($comments)) : ?>
	<div class="jcomments">
		<?php
		require_once($comments);
		echo JComments::showComments($this->item->id, 'com_sermonspeaker', $this->item->sermon_title); ?>
	</div>
<?php endif; ?>
</div>