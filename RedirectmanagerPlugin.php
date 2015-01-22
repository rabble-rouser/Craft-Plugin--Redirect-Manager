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
			'redirectmanager\/(?P<redirectId>\d+)' => 'redirectmanager/_edit'
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
}
