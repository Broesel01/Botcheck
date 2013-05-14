<?php
namespace wcf\acp\form;

use wcf\data\botcheck\BotcheckQuestionAction;
use wcf\data\botcheck\BotcheckQuestionEditor;
use wcf\data\package\PackageCache;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the question add form.
 * 
 * @author		Markus Bartz <roul@codingcorner.info>
 * @copyright	2013 Markus Bartz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		info.codingcorner.wcf.user.botcheck
 * @subpackage	acp.form
 * @category	Community Framework
 */
class BotcheckQuestionAddForm extends AbstractForm {
	/**
	 * @see	wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.user.botcheck.question.add';
	
	/**
	 * @see	wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.user.botcheck.canManageQuestions');
	
	/**
	 * question value
	 * @var	string
	 */
	public $question = '';
	
	/**
	 * answers value
	 * @var	string
	 */
	public $answers = '';
	
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		I18nHandler::getInstance()->register('question');
		I18nHandler::getInstance()->register('answers');
	}
	
	/**
	 * @see	wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		I18nHandler::getInstance()->readValues();
		
		if (I18nHandler::getInstance()->isPlainValue('question')) $this->question = I18nHandler::getInstance()->getValue('question');
		if (I18nHandler::getInstance()->isPlainValue('answers')) $this->answers = I18nHandler::getInstance()->getValue('answers');
	}
	
	/**
	 * @see	wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		// validate question
		if (!I18nHandler::getInstance()->validateValue('question')) {
			if (I18nHandler::getInstance()->isPlainValue('question')) {
				throw new UserInputException('question');
			}
			else {
				throw new UserInputException('question', 'multilingual');
			}
		}
		
		// validate answers
		if (!I18nHandler::getInstance()->validateValue('answers')) {
			if (I18nHandler::getInstance()->isPlainValue('answers')) {
				throw new UserInputException('answers');
			}
			else {
				throw new UserInputException('answers', 'multilingual');
			}
		}
	}
	
	/**
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// save question
		$this->objectAction = new BotcheckQuestionAction(array(), 'create', array('data' => array(
			'question' => $this->question,
			'answers' => $this->answers,
		)));
		$this->objectAction->executeAction();
		
		$returnValues = $this->objectAction->getReturnValues();
		$questionID = $returnValues['returnValues']->questionID;
		$updateValues = array();
		if (!I18nHandler::getInstance()->isPlainValue('question')) {
			I18nHandler::getInstance()->save('question', 'wcf.acp.botcheck.question'.$questionID, 'wcf.acp.botcheck', PackageCache::getInstance()->getPackageID('info.codingcorner.wcf.user.botcheck'));
			
			// prepare question update
			$updateValues['question'] = 'wcf.acp.botcheck.question'.$questionID;
		}
		
		if (!I18nHandler::getInstance()->isPlainValue('answers')) {
			I18nHandler::getInstance()->save('answers', 'wcf.acp.botcheck.answers'.$questionID, 'wcf.acp.botcheck', PackageCache::getInstance()->getPackageID('info.codingcorner.wcf.user.botcheck'));
			
			// prepare answers update
			$updateValues['answers'] = 'wcf.acp.botcheck.answers'.$questionID;
		}

		// update if needed
		if (count($updateValues)) {
			$questionEditor = new BotcheckQuestionEditor($returnValues['returnValues']);
			$questionEditor->update($updateValues);
		}
		
		$this->saved();
		
		// reset values
		$this->question = $this->answers = '';
		
		I18nHandler::getInstance()->reset();
		
		// show success
		WCF::getTPL()->assign(array(
			'success' => true
		));
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'add',
			'question' => $this->question,
			'answers' => $this->answers,
		));
	}
}
