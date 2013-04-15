<?php
/**
 * @author Sergey Glagolev <glagolev@shogo.ru>
 * @link https://github.com/shogodev/argilla/
 * @copyright Copyright &copy; 2003-2013 Shogo
 * @license http://argilla.ru/LICENSE
 * @package backend.components
 */
class BApplication extends CWebApplication
{
  const CLASS_PREFIX = 'B';

  /**
   * Удаление префикса класса
   *
   * @param string $className
   *
   * @return string mixed
   */
  public static function cutClassPrefix($className)
  {
    return preg_replace('/^'.BApplication::CLASS_PREFIX.'([A-Z])(.*)/', '$1$2', $className);
  }

  /**
   * Получение id контроллера из BModule::$controllerMap
   * если он не находится, то возвращается обычный ID контроллера
   *
   * @return string
   */
  public static function getMappedControllerId()
  {
    $controllerMap = Yii::app()->controller->module->controllerMap;
    $controllerClass = get_class(Yii::app()->controller);
    $mappedId = array_search($controllerClass, $controllerMap);
    return $mappedId ?: Yii::app()->controller->id;
  }

  public function getFrontendPath()
  {
    return str_replace("/backend/protected", "", $this->getBasePath()).'/';
  }

  public function getFrontendUrl()
  {
    return $this->request->getHostInfo().'/';
  }

  /**
   * Устанавливаем окружение.
   * Задаем модуль, контроллер и экшен, в контексте которого выполняется код.
   *
   * Yii::app()->setUnitEnvironment('BInfo', 'BInfo', 'update', array('id' => '2'));
   *
   * @param string $module_name
   * @param string $controller_name
   * @param string $action
   * @param array  $params
   */
  public function setUnitEnvironment($module_name, $controller_name, $action = 'index', $params = array())
  {
    $module     = $module_name.'Module';
    $controller = $controller_name.'Controller';

    $controller = new $controller(strtolower($controller_name), new $module(strtolower($module_name), null));
    $controller->setAction(new CInlineAction($controller, $action));

    Yii::app()->setController($controller);
    $_GET = CMap::mergeArray($_GET, $params);
  }

  /**
   * Инициализируем модули
   *
   * Функция используется в тестах, чтобы не инклудить модели
   */
  public function initModules()
  {
    $modules = Yii::app()->getModules();
    foreach($modules as $id => $module)
    {
      Yii::import($module['class']);

      $className = ucfirst($id).'Module';
      $class     = new $className($id, null);
    }
  }

  /**
   * Завершаем приложение
   * Если приложение запущено с тестовым конфигом, то возвращаем управление в вызываемый код
   *
   * @param integer  $status
   * @param bool $exit
   */
  public function end($status = 0, $exit = true)
  {
    if( Yii::app()->params['mode'] === 'test' )
    {
      Yii::app()->user->setFlash('end', array('status' => $status, 'exit' => $exit));
      return;
    }
    else
    {
      parent::end($status, $exit);
    }
  }

  public function registerAjaxUpdateError()
  {
    Yii::app()->clientScript->registerScript('ajaxUpdateError', '
      function ajaxUpdateError(xhr, err)
      {
        if( xhr.status === 401 )
          assigner.open(
            "'.Yii::app()->controller->createUrl("/base/index", array('popup' => true)).'",
            {width : "800", height : "400", left : "50%", top : "50%", marginTop : "-250px", marginLeft: "-400px"}
          );
        else
        {
          if( !err )
          {
            if( xhr.status && !/^\s*$/.test(xhr.status) )
              err = "Error " + xhr.status;
            else
              err = "Error";
            if( xhr.responseText && !/^\s*$/.test(xhr.responseText) )
              err = err + ": " + xhr.responseText;
          }

          alert(err);
        }
      }'
    );
  }

  protected function init()
  {
    /**
     * Подгружаем модули в приложение
     */
    foreach(glob(dirname(__FILE__).'/../modules/*', GLOB_ONLYDIR) as $moduleDirectory)
      if( preg_match("/\w+/", basename($moduleDirectory)) )
        $this->setModules(array(basename($moduleDirectory) => array('autoloaded' => true)));

    $this->params->project = preg_replace("/^www./", '', Yii::app()->request->serverName);

    return parent::init();
  }
}