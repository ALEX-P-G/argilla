<?php
/**
 * @author Alexey Tatarivov <tatarinov@shogo.ru>
 * @link https://github.com/shogodev/argilla/
 * @copyright Copyright &copy; 2003-2014 Shogo
 * @license http://argilla.ru/LICENSE
 * @package backend.models.behaviors
 *
 * Поведение позволяет использовать JToggleColumn как radioButton, при выборе нового значения сбрасывает прежнее
 *
 * пример:
 * <pre>
 *  'topRadioToggleBehavior' => array(
 *    'class' => 'RadioToggleBehavior',
 *     'conditionAttribute' => 'section_id',
 *     'toggleAttribute' => 'top'
 *   );
 * </pre>
 *
 * @property BActiveRecord $owner
 */
class RadioToggleBehavior extends CModelBehavior
{
  public $conditionAttribute;

  public $toggleAttribute;

  public function events()
  {
    return array(
      'onAfterConstruct' => 'afterConstruct',
      'onBeforeSave' => 'beforeSave',
    );
  }

  public function beforeSave()
  {
    if( $this->owner->{$this->toggleAttribute} == 1 )
    {
      $criteria = new CDbCriteria();
      $criteria->compare($this->conditionAttribute, $this->owner->{$this->toggleAttribute});
      $this->owner->updateAll(array($this->toggleAttribute => 0), $criteria);
    }
  }

  public function afterConstruct()
  {
    if( empty($this->conditionAttribute) )
     throw new CHttpException('500', "В классе ".get_class($this->owner)." неуказан параметр conditionAttribute для поведения RadioToggleBehavior");

    if( empty($this->toggleAttribute) )
      throw new CHttpException('500', "В классе ".get_class($this->owner)." неуказан параметр toggleAttribute для поведения RadioToggleBehavior");
  }
} 