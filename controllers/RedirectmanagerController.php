<?php

namespace Craft;

class RedirectmanagerController extends BaseController
{
	public function actionSaveRedirect()
	{
		$this->requirePostRequest();

		if ($id = craft()->request->getPost('redirectId')) {
			$model = craft()->redirectmanager_redirect->getRedirectById($id);
		} else {
			$model = craft()->redirectmanager_redirect->newRedirect($id);
		}

		$attributes = craft()->request->getPost('redirectRecord');
		$model->setAttributes($attributes);

		if (craft()->redirectmanager_redirect->saveRedirect($model)) {
			craft()->userSession->setNotice(Craft::t('Redirect saved.'));
			return $this->redirectToPostedUrl(array('redirectId' => $model->getAttribute('id')));
		} else {
			craft()->userSession->setError(Craft::t("Couldn't save redirect."));
			craft()->urlManager->setRouteVariables(array('redirectId' => $model->getAttribute('id')));
		}
	}

	public function actionDeleteRedirect()
	{

		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$id = craft()->request->getRequiredPost('id');
		craft()->redirectmanager_redirect->deleteRedirectById($id);

		$this->returnJson(array('success' => true));
	}

	public function actionDeleteAll()
	{
		craft()->redirectmanager_redirect->deleteAll();
	}
	public function actionImport()
	{

		$this->requirePostRequest();
		$file = craft()->request->getPost('Data');
		$extension = craft()->request->getPost('Ext');
		$matchType = craft()->request->getPost('MatchType');
		$redirectType = craft()->request->getPost('RedirectType');
		$redirectTime = craft()->request->getPost('RedirectTime');

		$redirectsProcessed = craft()->redirectmanager_import->import($file, $extension, $matchType, $redirectType, $redirectTime);

		if($redirectsProcessed)
		{
			craft()->userSession->setNotice(Craft::t('Redirects saved.'));
		}
		else {
			craft()->userSession->setError(Craft::t("Couldn't save at least one of the redirects."));
		}

		$this->redirectToPostedUrl();

	}

	public function actionTest()
	{
		$file = craft()->path->tempPath . 'URLReport.csv';
		$fileContents = IOHelper::getFileContents($file);
		$model = craft()->redirectmanager_import->test($fileContents);
		$this->returnJson($model);
	}

}
