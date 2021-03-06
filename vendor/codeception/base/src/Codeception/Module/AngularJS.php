<?php
namespace Codeception\Module;

use Codeception\Step;
use Codeception\TestInterface;
use Facebook\WebDriver\WebDriverBy;


class AngularJS extends WebDriver
{
    protected $insideApplication = true;

    protected $defaultAngularConfig = [
        'script_timeout' => 5,
        'el' => 'body',
    ];

    protected $waitForAngular = <<<EOF
  var rootSelector = arguments[0];
  var callback = arguments[1];
  var el = document.querySelector(rootSelector);

  try {
    if (window.getAngularTestability) {
      window.getAngularTestability(el).whenStable(callback);
      return;
    }
    if (!window.angular) {
      throw new Error('window.angular is undefined.  This could be either ' +
          'because this is a non-angular page or because your test involves ' +
          'client-side navigation, which can interfere with Protractor\'s ' +
          'bootstrapping.  See http://git.io/v4gXM for details');
    }
    if (angular.getTestability) {
    angular.getTestability(el).whenStable(callback);
    } else {
      if (!angular.element(el).injector()) {
        throw new Error('root element (' + rootSelector + ') has no injector.' +
           ' this may mean it is not inside ng-app.');
      }
      angular.element(el).injector().get('\$browser').
          notifyWhenNoOutstandingRequests(callback);
    }
  } catch (err) {
    callback(err.message);
  }
EOF;


    public function _setConfig($config)
    {
        parent::_setConfig(array_merge($this->defaultAngularConfig, $config));
    }

    public function _before(TestInterface $test)
    {
        parent::_before($test);
        $this->webDriver->manage()->timeouts()->setScriptTimeout($this->config['script_timeout']);
    }

    
    public function amInsideAngularApp()
    {
        $this->insideApplication = true;
    }

    
    public function amOutsideAngularApp()
    {
        $this->insideApplication = false;
    }

    public function _afterStep(Step $step)
    {
        if (!$this->insideApplication) {
            return;
        }
        $actions = [
            'amOnPage',
            'click',
            'fillField',
            'selectOption',
            'checkOption',
            'uncheckOption',
            'unselectOption',
            'doubleClick',
            'appendField',
            'clickWithRightButton',
            'dragAndDrop'
        ];
        if (in_array($step->getAction(), $actions)) {
            $this->webDriver->executeAsyncScript($this->waitForAngular, [$this->config['el']]);
        }
    }

    protected function getStrictLocator(array $by)
    {
        $type = key($by);
        $value = $by[$type];
        if ($type === 'model') {
            return WebDriverBy::cssSelector(sprintf('[ng-model="%s"]', $value));
        }
        return parent::getStrictLocator($by);
    }
}
