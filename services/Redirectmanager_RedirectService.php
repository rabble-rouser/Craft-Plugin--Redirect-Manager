<?php

namespace Craft;

class Redirectmanager_RedirectService extends BaseApplicationComponent
{
	protected $redirectRecord;
	private $oldURL = '';

	/**
	* constructor
	* @param RedirectmanagerRecord $redirectRecord
	*/
	public function __construct($redirectRecord = null)
	{
		$this->redirectRecord = $redirectRecord;
		if (is_null($this->redirectRecord)) {
			$this->redirectRecord = RedirectmanagerRecord::model();
		}
	}

	/**
	* Redirect the request to a new location
	 * @param string $redirectTime
	*/
	public function redirect($redirectTime)
	{
		// redirects only take place out of the CP
		if(craft()->request->isSiteRequest()){
			$path = craft()->request->getPath();
			if( $location = $this->processRedirect($path, $redirectTime) )
			{
				header("Location: ".$location['url'], true, $location['type']);
				exit();
			}
		}
	}

	/**
	* process the redirect of a given uri.
	* @param string $uri, the uri to check
	 * @param string $redirectTime
	* @return the location to redirect to, or false
	*/
	public function processRedirect($uri, $redirectTime)
	{
		$records = $this->getAllRedirects();

		foreach($records as $record)
		{
			$record = $record->attributes;
			if($redirectTime === $record['redirectTime']) {

				// trim to tolerate whitespace in user entry
				$record['uri'] = trim($record['uri']);
				$regexMatch = false;

				// Regex / wildcard match
				if (preg_match("/^#(.+)#$/", $record['uri'], $matches)) {
					// no-op: all set to use the regex
					$regexMatch = true;
				} elseif (strpos($record['uri'], "*")) {
					// not necessary to replace / with \/ here, but no harm to it either
					$record['uri'] = "#^" . str_replace(array("*", "/"), array("(.*)", "\/"), $record['uri']) . '#';
					$regexMatch = true;
				}

				if ($regexMatch) {
					if (preg_match($record['uri'], $uri)) {
						$redirectLocation = preg_replace($record['uri'], $record['location'], $uri);
						break;
					}
				} else {
					// Standard match
					if ($record['uri'] == $uri) {
						$redirectLocation = $record['location'];
						break;
					}
				}
			}
		}
		return (isset($redirectLocation)) ? array("url" => ( strpos($record['location'], "http") === 0 ) ? $redirectLocation : UrlHelper::getSiteUrl($redirectLocation), "type" => $record['type']) : false;
	}

	/**
	* create a new redirect
	* @param array $attributes
	* @return redirectmanagerModel $model
	*/
	public function newRedirect($attributes = array())
	{
		$model = new RedirectmanagerModel();
		$model->setAttributes($attributes);

		return $model;
	}

	/**
	* gets every redirect
	* @return an array of records
	*/
	public function getAllRedirects()
	{
		$records = $this->redirectRecord->findAll(array('order'=>'id'));
		return RedirectmanagerModel::populateModels($records, 'id');
	}

	/**
	* get a redirect based on an id
	* @param int $id
	* return mixed record of the redirect, or false if it is not found
	*/
	public function getRedirectById($id)
	{
		if ($record = $this->redirectRecord->findByPk($id)) {
			return RedirectmanagerModel::populateModel($record);
		}
	}

	/**
	* save a redirect
	* @param RedirectmanagerModel $model
	* @return bool true if save is successful, false otherwise.
	*/
	public function saveRedirect(RedirectmanagerModel &$model)
	{
		if ($id = $model->getAttribute('id')) {
			if (null === ($record = $this->redirectRecord->findByPk($id)))
			{
				throw new Exception(Craft::t('Can\'t find a redirect with ID "{id}"', array('id' => $id)));
			}
		}
		else {
			$record = $this->redirectRecord->create();
		}

		$record->setAttributes($model->getAttributes());
		if ($record->save()) {
			// update id on model (for new records)
			$model->setAttribute('id', $record->getAttribute('id'));

			return true;
		}
		else {
			$model->addErrors($record->getErrors());

			return false;
		}
	}

	/**
	* delete a redirect based on id
	* @param int $id
	* @return bool true if successful, false otherwise.
	*/
	public function deleteRedirectById($id)
	{
		return $this->redirectRecord->deleteByPk($id);
	}

	/**
	* delete every redirect
	*/
	public function deleteAll()
	{
		$this->redirectRecord->deleteAll();
	}

	/**
	* process a regex match
	* @param str $uriToMatch
	* @param str $uri
	*/
	private function _processRegexMatch($uriToMatch, $uri)
	{
		preg_match("/^#(.+)#$/", $uriToMatch, $matches);
		// return ($matches[1] == $uri) ;
	}

	private function _processWildcardMatch($val)
	{

	}

	protected function getOldURL()
	{
		return $this->oldURL;
	}

	protected function setOldURL($url)
	{
		$this->oldURL = $url;
	}

	/**
	 * Listen for a save entry event, and check to see if the entry url has changed. If it has changed
	 * and the entry was listed as a redirect, update the redirect with the new url.
	 */
	public function onEntrySlugChange()
	{
		craft()->on('entries.beforeSaveEntry', function(Event $event){
			//if the entry is not a new one, then save the old url
			if(!$event->params['isNewEntry']){
				$this->setOldURL($event->params['entry']->getUrl());
			}
		});

		craft()->on('entries.saveEntry', function(Event $event){
			$newURL = $event->params['entry']->getUrl();

			//check if the entry is not new and if the URL has changed
			if(!$event->params['isNewEntry'] and $newURL !== $this->getOldURL())
			{
				//check to see if the redirect is in the db
				$redirects = $this->getAllRedirects();
				foreach($redirects as $redirect)
				{
					//if redirect exists, then update that redirects uri
					if($redirect['uri'] === $this->getOldURL())
					{
						$id = $redirect['id'];
						$model = $this->getRedirectById($id);
						$model->setAttribute('uri', $newURL);
						$this->saveRedirect($model);
						break;
					}

				}
				//reset the oldURL
				$this->oldURL = '';
			}
		});
	}


	

}
