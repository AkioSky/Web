<?php
/*------------------------------------------------------------------------
# Daycounts Version download custom param/field
# ------------------------------------------------------------------------
# author    Jeremy Magne
# copyright Copyright (C) 2010 Daycounts.com. All Rights Reserved.
# Websites: http://www.daycounts.com
# Technical Support: http://www.daycounts.com/en/contact/
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
-------------------------------------------------------------------------*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.cache.cache');
jimport('joomla.application.helper');
jimport('joomla.filesystem.file');
jimport('joomla.html.parameter.element');

if(!class_exists('DaycountsVersionDownloader')) {
	class DaycountsVersionDownloader {
		
		public static function manageUpdateServer($versioncat=0,$downloadcode='') {
			
			if (version_compare(JVERSION,'1.6.0','ge')) {
				jimport('joomla.updater.update');

				$ext_id = JRequest::getVar('extension_id');
				//echo $extension_id;
				//echo '<br/>'.$versioncat;
				if ($ext_id && $versioncat) {
					$update_url = 'http://www.daycounts.com/index.php?option=com_versions&catid='.$versioncat.'&download_code='.$downloadcode.'&task=updateserver.xml';

					$db = JFactory::getDBO();
					//Check if the update site is already defined
					$db->setQuery("SELECT update_site_id FROM #__update_sites_extensions WHERE extension_id='".$ext_id."'");
					$updatesiteid = $db->loadResult();
					$updater = JUpdater::getInstance();
					
					//if ($updatesiteid && $downloadcode) {
					if ($updatesiteid) {
						//If found update row ans valid download code
						$db->setQuery("UPDATE #__update_sites SET location = '".$update_url."' WHERE update_site_id = '".$updatesiteid."'");
						$db->query();
						//$updater->findUpdates($ext_id);
					//} else if ($updatesiteid && !$downloadcode) {
					//	//Delete updater row
					//	$db->setQuery("DELETE FROM #__update_sites WHERE update_site_id = '".$updatesiteid."'");
					//	$db->query();
					//	$db->setQuery("DELETE FROM #__update_sites_extensions WHERE update_site_id = '".$updatesiteid."'");
					//	$db->query();
					} else if (!$updatesiteid && $downloadcode) {
						//Insert update row
						$db->setQuery("INSERT INTO #__update_sites (name,type,location,enabled) VALUES ('Daycounts plugin updater','extension','".$update_url."',1)");
						$db->query();
						$updatesiteid = $db->insertid();
						if ($updatesiteid) {
							$db->setQuery("INSERT INTO #__update_sites_extensions (update_site_id,extension_id) VALUES (".$updatesiteid.",".$ext_id.")");
							$db->query();
							//$updater->findUpdates($ext_id);
						}
					}
				}
			}
		}
	}
}

class JFormFieldVersionDownload extends JFormField {

	public function getInput()	{
		$versioncat = $this->element['versioncat']->data();

		DaycountsVersionDownloader::manageUpdateServer($this->value);
		
		$msg = '<input type="text" id="'.$this->id.'" style="width:250px;" name="'.$this->name.'" value="'.$this->value.'">';
		return $msg;

	}	
	

}
