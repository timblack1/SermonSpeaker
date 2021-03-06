<?php
// No direct access
defined('_JEXEC') or die;

/**
 * View to edit a sermon.
 *
 * @package		Sermonspeaker.Administrator
 */
class SermonspeakerViewSermon extends JViewLegacy
{
	protected $state;
	protected $item;
	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->state	= $this->get('State');
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');

		// Check some PHP settings for upload limit so I can show it as an info
		$post_max_size			= ini_get('post_max_size');
		$upload_max_filesize	= ini_get('upload_max_filesize');
		$this->upload_limit		= ($this->return_bytes($post_max_size) < $this->return_bytes($upload_max_filesize)) ? $post_max_size : $upload_max_filesize;

		// add Javascript for Form Elements enable and disable
		$enElem = 'function enableElement(ena_elem, dis_elem) {
			ena_elem.disabled = false;
			dis_elem.disabled = true;
		}';
		// add Javascript for Form Elements enable and disable (J30)
		$toggle = 'function toggleElement(element, state) {
			if (state) {
				document.getElementById(element + "_text_icon").className = "btn add-on icon-cancel";
				document.getElementById(element + "_icon").className = "btn add-on icon-checkmark";
				document.getElementById("jform_" + element + "_text").disabled = true;
				document.getElementById("jform_" + element).disabled = false;
				if(document.getElementById("jform_" + element + "_chzn")){
					jQuery("#jform_" + element).trigger("liszt:updated");
				}
			} else {
				document.getElementById(element + "_text_icon").className = "btn add-on icon-checkmark";
				document.getElementById(element + "_icon").className = "btn add-on icon-cancel";
				document.getElementById("jform_" + element + "_text").disabled = false;
				document.getElementById("jform_" + element).disabled = true;
				if(document.getElementById("jform_" + element + "_chzn")){
					jQuery("#jform_" + element).trigger("liszt:updated");
				}
			}
		}';
		// add Javascript for ID3 Lookup (ajax)
		$lookup	= 'function lookup(elem) {
			xmlhttp = new XMLHttpRequest();
			xmlhttp.onreadystatechange=function(){
				if (xmlhttp.readyState==4 && xmlhttp.status==200){
					var data = JSON.decode(xmlhttp.responseText);
					if (data.status==1){
						if(data.filename_title==false || document.getElementById("jform_title").value==""){
							document.getElementById("jform_title").value = data.title;
							document.getElementById("jform_alias").value = data.alias;
						}
						if(data.sermon_number && document.getElementById("jform_sermon_number")){
							document.getElementById("jform_sermon_number").value = data.sermon_number;
						}
						if(data.sermon_date && document.getElementById("jform_sermon_date")){
							document.getElementById("jform_sermon_date").value = data.sermon_date;
						}
						if(data.sermon_time && document.getElementById("jform_sermon_time")){
							document.getElementById("jform_sermon_time").value = data.sermon_time;
						}
						if(data.series_id && document.getElementById("jform_series_id")){
							document.getElementById("jform_series_id").value = data.series_id;
							if(document.getElementById("jform_series_id_chzn")){
								jQuery("#jform_series_id").trigger("liszt:updated");
							}
						}
						if(data.speaker_id && document.getElementById("jform_speaker_id")){
							document.getElementById("jform_speaker_id").value = data.speaker_id;
							if(document.getElementById("jform_speaker_id_chzn")){
								jQuery("#jform_speaker_id").trigger("liszt:updated");
							}
						}
						if(data.notes && document.getElementById("jform_notes")){
							jInsertEditorText(data.notes, "jform_notes");
						}
						if(data.filesize){
							splits = elem.id.split("_");
							field = splits[0]+"_"+splits[1]+"size";
							if(document.getElementById(field)){
								document.getElementById(field).value = data.filesize;
							}
						}
					} else {
						alert(data.msg);
					}
				}
			}
			xmlhttp.open("POST","index.php?option=com_sermonspeaker&task=file.lookup&format=json",true);
			xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
 			xmlhttp.send("file="+elem.value);
		}';

		$this->params	= JComponentHelper::getParams('com_sermonspeaker');

		$document = JFactory::getDocument();
		$document->addScriptDeclaration($enElem);
		$document->addScriptDeclaration($toggle);
		$document->addScriptDeclaration($lookup);

		// Google Picker
		if ($this->params->get('googlepicker', 0))
		{
			JHtml::Script('http://www.google.com/jsapi');
			$picker = 'google.load(\'picker\', \'1\');
				function createVideoPicker() {
					var picker = new google.picker.PickerBuilder().
						addView(google.picker.ViewId.DOCS_VIDEOS).
						addView(google.picker.ViewId.YOUTUBE).
						addView(google.picker.ViewId.VIDEO_SEARCH).
						addView(google.picker.ViewId.RECENTLY_PICKED).
						setCallback(pickerCallbackVideo).
						build();
					picker.setVisible(true);
				}
				function createAddfilePicker() {
					var picker = new google.picker.PickerBuilder().
						addView(google.picker.ViewId.DOCS).
						addView(google.picker.ViewId.PHOTOS).
						addView(google.picker.ViewId.YOUTUBE).
						addView(google.picker.ViewId.IMAGE_SEARCH).
						addView(google.picker.ViewId.VIDEO_SEARCH).
						addView(google.picker.ViewId.RECENTLY_PICKED).
						setCallback(pickerCallbackAddfile).
						build();
					picker.setVisible(true);
				}
				function pickerCallbackVideo(data) {
					if (data.action == "picked") {
						document.getElementById(\'jform_videofile_text\').value = data.docs[0].url;
					}
				}
				function pickerCallbackAddfile(data) {
					if (data.action == "picked") {
						var value = data.docs[0].url;
						if (data.docs[0].iconUrl){
							if (data.docs[0].url.indexOf("?") == -1){
								value += "?icon=" + data.docs[0].iconUrl;
							} else {
								value += "&icon=" + data.docs[0].iconUrl;
							}
						}
						document.getElementById(\'jform_addfile_text\').value = value;
					}
				}
			';
			$document->addScriptDeclaration($picker);
		}

		$session	= JFactory::getSession();
		// Prepare Flashuploader
		$targetURL 	= JURI::root().'administrator/index.php?option=com_sermonspeaker&task=file.upload&'.$session->getName().'='.$session->getId().'&'.JSession::getFormToken().'=1&format=json';
		// SWFUpload
		JHtml::Script('media/com_sermonspeaker/swfupload/swfupload.js');
		JHtml::Script('media/com_sermonspeaker/swfupload/swfupload.queue.js');
		JHtml::Script('media/com_sermonspeaker/swfupload/fileprogress.js');
		JHtml::Script('media/com_sermonspeaker/swfupload/handlers.js', true);
		$uploader_script = '
			window.onload = function() {
				if(document.id("jform_audiofile_text")){
					upload1 = new SWFUpload({
						upload_url: "'.$targetURL.'&type=audio",
						flash_url : "'.JURI::root().'media/com_sermonspeaker/swfupload/swfupload.swf",
						file_size_limit : "0",
						file_types : "'.$this->getAllowedFiletypes('audio').'",
						file_types_description : "'.JText::_('COM_SERMONSPEAKER_FIELD_AUDIOFILE_LABEL', 'true').'",
						file_upload_limit : "0",
						file_queue_limit : "0",
						button_image_url : "'.JURI::root().'media/com_sermonspeaker/swfupload/XPButtonUploadText_61x22.png",
						button_placeholder_id : "btnUpload1",
						button_width: 61,
						button_height: 22,
						button_window_mode: "transparent",
						debug: false,
						swfupload_loaded_handler: function() {
							document.id("btnCancel1").removeClass("ss-hide");
							document.id("audiopathinfo").removeClass("ss-hide");
							if(document.id("upload_limit_audio")){
								document.id("upload_limit_audio").removeClass("ss-hide");
							}
							if(document.id("upload-noflash")){
								document.id("upload-noflash").destroy();
								document.id("loading").destroy();
							}
						},
						file_dialog_start_handler : fileDialogStart,
						file_queued_handler : fileQueued,
						file_queue_error_handler : fileQueueError,
						file_dialog_complete_handler : fileDialogComplete,
						upload_start_handler : uploadStart,
						upload_progress_handler : uploadProgress,
						upload_error_handler : uploadError,
						upload_success_handler : function uploadSuccess(file, serverData) {
							try {
								var progress = new FileProgress(file, this.customSettings.progressTarget);
								var data = JSON.decode(serverData);
								if (data.status == "1") {
									progress.setComplete();
									progress.setStatus(data.error);
									document.id("jform_audiofile_text").value = data.path;
								} else {
									progress.setError();
									progress.setStatus(data.error);
								}
								progress.toggleCancel(false);
							} catch (ex) {
								this.debug(ex);
							}
						},
						upload_complete_handler : uploadComplete,
						custom_settings : {
							progressTarget : "infoUpload1",
							cancelButtonId : "btnCancel1"
						}
					});
				}
				if(document.id("jform_videofile_text")){
					upload2 = new SWFUpload({
						upload_url: "'.$targetURL.'&type=video",
						flash_url : "'.JURI::root().'media/com_sermonspeaker/swfupload/swfupload.swf",
						file_size_limit : "0",
						file_types : "'.$this->getAllowedFiletypes('video').'",
						file_types_description : "'.JText::_('COM_SERMONSPEAKER_FIELD_VIDEOFILE_LABEL', 'true').'",
						file_upload_limit : "0",
						file_queue_limit : "0",
						button_image_url : "'.JURI::root().'media/com_sermonspeaker/swfupload/XPButtonUploadText_61x22.png",
						button_placeholder_id : "btnUpload2",
						button_width: 61,
						button_height: 22,
						button_window_mode: "transparent",
						debug: false,
						swfupload_loaded_handler: function() {
							document.id("btnCancel2").removeClass("ss-hide");
							document.id("videopathinfo").removeClass("ss-hide");
							if(document.id("upload_limit_video")){
								document.id("upload_limit_video").removeClass("ss-hide");
							}
							if(document.id("upload-noflash")){
								document.id("upload-noflash").destroy();
								document.id("loading").destroy();
							}
						},
						file_dialog_start_handler : fileDialogStart,
						file_queued_handler : fileQueued,
						file_queue_error_handler : fileQueueError,
						file_dialog_complete_handler : fileDialogComplete,
						upload_start_handler : uploadStart,
						upload_progress_handler : uploadProgress,
						upload_error_handler : uploadError,
						upload_success_handler : function uploadSuccess(file, serverData) {
							try {
								var progress = new FileProgress(file, this.customSettings.progressTarget);
								var data = JSON.decode(serverData);
								if (data.status == "1") {
									progress.setComplete();
									progress.setStatus(data.error);
									document.id("jform_videofile_text").value = data.path;
								} else {
									progress.setError();
									progress.setStatus(data.error);
								}
								progress.toggleCancel(false);
							} catch (ex) {
								this.debug(ex);
							}
						},
						upload_complete_handler : uploadComplete,
						custom_settings : {
							progressTarget : "infoUpload2",
							cancelButtonId : "btnCancel2"
						}
					});
				}
				if(document.id("jform_addfile_text")){
					upload3 = new SWFUpload({
						upload_url: "'.$targetURL.'&type=addfile",
						flash_url : "'.JURI::root().'media/com_sermonspeaker/swfupload/swfupload.swf",
						file_size_limit : "0",
						file_types : "'.$this->getAllowedFiletypes('addfile').'",
						file_types_description : "'.JText::_('COM_SERMONSPEAKER_FIELD_VIDEOFILE_LABEL', 'true').'",
						file_upload_limit : "0",
						file_queue_limit : "0",
						button_image_url : "'.JURI::root().'media/com_sermonspeaker/swfupload/XPButtonUploadText_61x22.png",
						button_placeholder_id : "btnUpload3",
						button_width: 61,
						button_height: 22,
						button_window_mode: "transparent",
						debug: false,
						swfupload_loaded_handler: function() {
							document.id("btnCancel3").removeClass("ss-hide");
							document.id("addfilepathinfo").removeClass("ss-hide");
							if(document.id("upload_limit_addfile")){
								document.id("upload_limit_addfile").removeClass("ss-hide");
							}
							if(document.id("upload-noflash")){
								document.id("upload-noflash").destroy();
								document.id("loading").destroy();
							}
						},
						file_dialog_start_handler : fileDialogStart,
						file_queued_handler : fileQueued,
						file_queue_error_handler : fileQueueError,
						file_dialog_complete_handler : fileDialogComplete,
						upload_start_handler : uploadStart,
						upload_progress_handler : uploadProgress,
						upload_error_handler : uploadError,
						upload_success_handler : function uploadSuccess(file, serverData) {
							try {
								var progress = new FileProgress(file, this.customSettings.progressTarget);
								var data = JSON.decode(serverData);
								if (data.status == "1") {
									progress.setComplete();
									progress.setStatus(data.error);
									document.id("jform_addfile_text").value = data.path;
								} else {
									progress.setError();
									progress.setStatus(data.error);
								}
								progress.toggleCancel(false);
							} catch (ex) {
								this.debug(ex);
							}
						},
						upload_complete_handler : uploadComplete,
						custom_settings : {
							progressTarget : "infoUpload3",
							cancelButtonId : "btnCancel3"
						}
					});
				}
			}
		';
		$document->addScriptDeclaration($uploader_script);

		// Destination folder based on mode
		$this->s3audio	= ($this->params->get('path_mode_audio', 0) == 2) ? 1 : 0;
		$this->s3video	= ($this->params->get('path_mode_video', 0) == 2) ? 1 : 0;
		if ($this->s3audio || $this->s3video)
		{
			//include the S3 class   
			require_once JPATH_COMPONENT_ADMINISTRATOR.'/s3/S3.php';
			//AWS access info   
			$awsAccessKey 	= $this->params->get('s3_access_key');
			$awsSecretKey 	= $this->params->get('s3_secret_key');
			$bucket			= $this->params->get('s3_bucket');
			//instantiate the class
			$s3		= new S3($awsAccessKey, $awsSecretKey);
			$region	= $s3->getBucketLocation($bucket);
			$prefix	= ($region == 'US') ? 's3' : 's3-'.$region;

			$this->bucket	= $bucket;
			$this->prefix	= $prefix;
		}


		// Calculate destination path to show
		if ($this->params->get('append_path', 0))
		{
			$changedate	= "function changedate(datestring) {
					if(datestring && datestring != '0000-00-00 00:00:00'){
						year = datestring.substr(0,4);
						month = datestring.substr(5,2);
					} else {
						now = new Date;
						year = now.getFullYear();
						month = now.getMonth()+1;
						if (month < 10){
							month = '0'+month;
						}
					}";
			if(!$this->s3audio){$changedate	.= "document.id('audiopathdate').innerHTML = year+'/'+month+'/';";}
			if(!$this->s3video){$changedate	.= "document.id('videopathdate').innerHTML = year+'/'+month+'/';";}
			$changedate	.= "document.id('addfilepathdate').innerHTML = year+'/'+month+'/';
				}";
			$time	= ($this->item->sermon_date && $this->item->sermon_date != '0000-00-00 00:00:00') ? strtotime($this->item->sermon_date) : time();
			$this->append_date	= date('Y', $time).'/'.date('m', $time).'/';
		}
		else
		{
			$changedate	= "function changedate(datestring) {}";
			$this->append_date	= '';
		}
		$document->addScriptDeclaration($changedate);
		if ($this->params->get('append_path_lang', 0))
		{
			$changelang	= "function changelang(language) {
					if(!language || language == '*'){
						language = '".JFactory::getLanguage()->getTag()."'
					}";
			if(!$this->s3audio){$changelang	.= "document.id('audiopathlang').innerHTML = language+'/';";}
			if(!$this->s3video){$changelang	.= "document.id('videopathlang').innerHTML = language+'/';";}
			$changelang	.= "document.id('addfilepathlang').innerHTML = language+'/';
				}";
			$lang	= ($this->item->language && $this->item->language == '*') ? $this->item->language : JFactory::getLanguage()->getTag();
			$this->append_lang	= $lang.'/';
		}
		else
		{
			$changelang	= "function changelang(language) {}";
			$this->append_lang	= '';
		}
		$document->addScriptDeclaration($changelang);

		// Add javascript validation script
		JText::script('COM_SERMONSPEAKER_JS_CHECK_KEYWORDS', false, true);
		JText::script('COM_SERMONSPEAKER_JS_CHECK_CHARS', false, true);
		$valscript	= 'function check(string, count, mode){
					if(mode){
						split = string.split(",");
						if(split.length > count){
							message = Joomla.JText._("COM_SERMONSPEAKER_JS_CHECK_KEYWORDS");
							message = message.replace("{0}", split.length);
							message = message.replace("{1}", count);
							alert(message);
						}
					}else{
						if(string.length > count){
							message = Joomla.JText._("COM_SERMONSPEAKER_JS_CHECK_CHARS");
							message = message.replace("{0}", string.length);
							message = message.replace("{1}", count);
							alert(message);
						}
					}
				}';
		$document->addScriptDeclaration($valscript);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);
		$user		= JFactory::getUser();
		$userId		= $user->get('id');
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
		$canDo		= SermonspeakerHelper::getActions();
		JToolbarHelper::title(JText::sprintf('COM_SERMONSPEAKER_PAGE_'.($checkedOut ? 'VIEW' : ($isNew ? 'ADD' : 'EDIT')), JText::_('COM_SERMONSPEAKER_SERMONS_TITLE'), JText::_('COM_SERMONSPEAKER_SERMON')), 'sermons');

		// Build the actions for new and existing records.
		if ($isNew)
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::apply('sermon.apply');
				JToolBarHelper::save('sermon.save');
				JToolbarHelper::save2new('sermon.save2new');
			}
			JToolbarHelper::cancel('sermon.cancel');
		}
		else
		{
			// Can't save the record if it's checked out.
			if (!$checkedOut)
			{
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId))
				{
					JToolBarHelper::apply('sermon.apply');
					JToolBarHelper::save('sermon.save');

					// We can save this record, but check the create permission to see if we can return to make a new one.
					if ($canDo->get('core.create'))
					{
						JToolbarHelper::save2new('sermon.save2new');
					}
				}
			}

			// If checked out, we can still save to copy
			if ($canDo->get('core.create'))
			{
				JToolbarHelper::save2copy('sermon.save2copy');
			}

			JToolbarHelper::cancel('sermon.cancel', 'JTOOLBAR_CLOSE');
		}

		if ($this->state->params->get('save_history') && $user->authorise('core.edit'))
		{
			JToolbarHelper::versions('com_sermonspeaker.sermon', $this->item->id);
		}
	}

	// Function to return bytes from the PHP settings. Taken from the ini_get() manual
	protected function return_bytes($val) {
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		switch($last) {
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return $val;
	}

	// Get allowed filetypes
	protected function getAllowedFiletypes($field)
	{
		// sanitize
		$field	= (in_array($field, array('audio', 'video', 'addfile'))) ? $field : 'audio';

		$types	= $this->params->get($field.'_filetypes');
		$types	= array_map('trim', explode(',', $types));

		return ($types) ? '*.'.implode('; *.', $types) : '*.*';
	}
}