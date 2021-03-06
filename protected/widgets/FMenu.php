<?php
/**
 * @author Sergey Glagolev <glagolev@shogo.ru>
 * @link https://github.com/shogodev/argilla/
 * @copyright Copyright &copy; 2003-2014 Shogo
 * @license http://argilla.ru/LICENSE
 * @package frontend.widgets
 */

Yii::import('zii.widgets.CMenu');

class FMenu extends CMenu
{
  public $route;

  public $onlyActiveItems = false;

  public function init()
  {
    $this->htmlOptions['id'] = $this->getId();

    $this->activeSelected();
  }

  protected function isItemActive($item, $route)
  {
    if( is_array($route) )
    {
      $params = array_slice($route, 1);
      $route  = $route[0];
    }
    else
      $params = $_GET;

    if( isset($item['url']) && is_array($item['url']) && !strcasecmp(trim($item['url'][0], '/'), $route) )
    {
      if( isset($item['url']['#']) )
        unset($item['url']['#']);
      if( count($item['url']) > 1 )
      {
        $url = $item['url'];

        foreach(array_splice($url, 1) as $name => $value)
        {
          if( !isset($params[$name]) || $params[$name] != $value )
            return false;
        }
      }
      return true;
    }

    return false;
  }

  protected function renderMenuRecursive($items)
  {
    if( $this->onlyActiveItems )
    {
      foreach($items as $i => $item)
        if( !$item['active'] )
          $items[$i]['items'] = array();
    }

    parent::renderMenuRecursive($items);
  }

  protected function clearNoActiveItems(&$items)
  {
    foreach($items as $i => $item)
    {
      if( isset($item['items']) )
      {
        $this->clearNoActiveItems($items[$i]['items']);
      }

      if( isset($item['active']) && $item['active'] == false )
      {
        unset($items[$i]['active']);
      }
    }
  }

  protected function activeSelected()
  {
    $routes = !empty($this->controller->activeUrl) ? $this->controller->activeUrl : $this->controller->route;

  if( !is_array($routes) || !is_array(Arr::reset($routes)) )
      $routes = array($routes);

    foreach($routes as $route)
    {
      $this->clearNoActiveItems($this->items);
      $this->items = $this->normalizeItems($this->items, $route, $hasActiveChild);
    }
  }
}