<?php

/**
 * Dashboard presenter.
 */
class DashboardPresenter extends SecuredPresenter
{

	public function renderDefault()
	{
		$this->template->anyVariable = 'any value';
	}

}
