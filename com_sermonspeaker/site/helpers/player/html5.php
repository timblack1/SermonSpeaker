<?php
defined('_JEXEC') or die;

require_once(JPATH_SITE.'/components/com_sermonspeaker/helpers/player.php');

/**
 * HTML5
 */
class SermonspeakerHelperPlayerHtml5 extends SermonspeakerHelperPlayer
{
	private static $script_loaded;

	public function isSupported($file)
	{
		// Always true since no actual player is loaded
		return true;
	}

	public function getName()
	{
		return 'HTML5 Player';
	}

	public function preparePlayer($item, $config)
	{
		$this->config	= $config;
		$this->player	= $this->getName();

		if(is_array($item))
		{
			$this->mspace	= '<div class="alert"><button type="button" class="close" data-dismiss="alert">&times;</button><strong>Warning!</strong> '.$this->player.' doesn\'t support Playlists</div>';
			$this->script	= '';
			return false;
		}
		$type	= $this->config['prio'] ? 'v' : 'a';
		$this->setDimensions('21px', '250px');
		$this->setPopup($type);

		$autoplay	= $this->config['autostart'] ? 'autoplay="autoplay"' : '';
		$this->mode	= ($this->config['prio']) ? 'video' : 'audio';
		$property	= $this->mode.'file';
		$file		= $item->$property;
		$this->mspace	= '<'.$this->mode.' id="mediaspace'.$this->config['count'].'" '.$autoplay.' controls="controls" width="'.$this->config[$type.'width'].'" height="'.$this->config[$type.'height'].'">'
							.'<source src="'.SermonspeakerHelperSermonspeaker::makeLink($file).'">'
						.'</'.$this->mode.'>';

		$this->script	= '';
		$this->toggle	= false;

		return;
	}
}