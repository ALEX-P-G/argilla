<?php
/**
 * @author Sergey Glagolev <glagolev@shogo.ru>
 * @link https://github.com/shogodev/argilla/
 * @copyright Copyright &copy; 2003-2013 Shogo
 * @license http://argilla.ru/LICENSE
 * @package frontend.components.redirect
 */
class RequestRedirectComponent extends FRedirectComponent
{
  /**
   * @var array
   */
  private $request;

  /**
   * @var string
   */
  private $target;

  /**
   * @param string $url
   */
  public function __construct($url = null)
  {
    $url = isset($url) ? $url : Arr::get($_SERVER, 'REQUEST_URI');
    $this->setRequest($url);
  }

  /**
   * @param string $url
   */
  public function setRequest($url)
  {
    $this->request = parse_url($url);
    $this->request['path'] = Arr::get($this->request, 'path', '/');
  }

  /**
   * @return string
   */
  public function getRequest()
  {
    $url = $this->request;
    $url['path'] = $this->target;

    return Utils::buildUrl($url);
  }

  /**
   * @return string
   */
  public function getPath()
  {
    return $this->request['path'];
  }

  public function init()
  {
    $this->makeIndexRedirect();

    $this->criteria = new CDbCriteria();
    $this->criteria->compare('visible', 1);
    parent::init();

    Yii::app()->attachEventHandler('onBeginRequest', array($this, 'processRequest'));
    Yii::app()->attachEventHandler('onBeforeControllerAction', array($this, 'beforeControllerAction'));
  }

  public function beforeControllerAction()
  {
    if( Yii::app()->controller )
    {
      Yii::app()->controller->attachEventHandler('onBeforeRender', array($this, 'makeSlashRedirect'));
    }
  }

  /**
   * @param CEvent $event
   */
  public function processRequest($event = null)
  {
    $this->find();
    $this->findOrigin();
  }

  public function makeSlashRedirect()
  {
    if( Yii::app()->errorHandler->error )
      return;

    if( RedirectHelper::needTrailingSlash($this->request['path']) )
    {
      $this->setTarget($this->request['path'].'/');
      $this->move(RedirectHelper::TYPE_301);
    }
  }

  protected function addRedirect($base, $target, $type)
  {
    $data = array('target' => $target, 'type_id' => $type);

    if( RedirectHelper::isRegExp($base) )
      $this->redirectPatterns[$base] = $data;
    else
      $this->redirectUrls[$base] = $data;
  }

  /**
   * @param string $url
   */
  private function setTarget($url)
  {
    $this->target = $url;
  }

  private function makeIndexRedirect()
  {
    $matches = array();

    if( RedirectHelper::scriptNamePresent($this->request['path'], $matches) )
    {
      $this->setTarget($matches[1] ? $matches[1] : '/');
      $this->move(RedirectHelper::TYPE_301);
    }
  }

  private function find()
  {
    if( ($data = $this->findByKey($this->getPath())) !== null )
    {
      $this->setTarget($data['target']);
      $this->move($data['type_id']);
    }
    elseif( $data = $this->findByPattern($this->getPath()) )
    {
      $this->setTarget($data['target']);
      $this->move($data['type_id']);
    }
  }

  /**
   * Отдаем 404 на страницах, для которых настроены редиректы, чтобы не возникало дублей
   *
   * @throws CHttpException
   */
  private function findOrigin()
  {
    foreach($this->getRedirectUrls() as $base => $data)
      if( $data['target'] === $this->getPath() )
        $this->move(404);

    foreach($this->getRedirectPatterns() as $pattern => $data)
      if( @preg_match($data['target'], $this->getPath()) )
        $this->move(404);
  }

  /**
   * @param integer $type
   *
   * @throws CHttpException
   */
  private function move($type)
  {
    if( $type == RedirectHelper::TYPE_404 )
    {
      throw new CHttpException(404, RedirectHelper::getTypes()[404]);
    }
    elseif( $type == RedirectHelper::TYPE_REPLACE )
    {
      $_SERVER['REQUEST_URI'] = $this->getRequest();
    }
    else
    {
      Yii::app()->request->redirect($this->getRequest(), true, $type);
    }
  }
}