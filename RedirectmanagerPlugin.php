<?php

namespace Craft;

class RedirectmanagerPlugin extends BasePlugin
{
	public function getName()
	{
		return Craft::t('Redirect Manager');
	}

	public function getVersion()
	{
		return 'Beta';
	}

	public function getDeveloper()
	{
		return 'Roi Kingon';
	}

	public function getDeveloperUrl()
	{
		return 'http://www.roikingon.com';
	}

	public function hasCpSection()
	{
		return true;
	}

	public function registerCpRoutes()
	{
		return array(
			'redirectmanager\/new' => 'redirectmanager/_edit',
			'redirectmanager\/(?P<redirectId>\d+)' => 'redirectmanager/_edit',
			'redirectmanager\/import' => 'redirectmanager/_import',
		);
	}
	public function onAfterInstall()
	{
		$redirects = array(
			array('uri' => '#^bad(.*)$#', 'location' => 'good$1', 'type' => "302")
		);

		foreach ($redirects as $redirect) {
			craft()->db->createCommand()->insert('redirectmanager', $redirect);
		}
	}

	//TODO: get event handler to work again. add regex matching for check
	/**
	 * call parent init, call pre404 redirects, listen for entry slug changes
	 */
	public function init()
	{
		parent::init();
		$redirectTime = 'pre404';
		craft()->redirectmanager_redirect->redirect($redirectTime);

		craft()->redirectmanager_redirect->onEntrySlugChange();
	}
}
