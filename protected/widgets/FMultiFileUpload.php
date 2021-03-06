<?php
/**
 * @author Sergey Glagolev <glagolev@shogo.ru>
 * @link https://github.com/shogodev/argilla/
 * @copyright Copyright &copy; 2003-2014 Shogo
 * @license http://argilla.ru/LICENSE
 * @package frontend.widgets
 */
class FMultiFileUpload extends CMultiFileUpload
{
  public $form;

  public function init()
  {
    $modelName = get_class($this->model).rand(0, 1000);

    if( !isset($this->accept) )
      $this->accept = implode('|', $this->model->fileTypes);

    if( !isset($this->duplicate) )
      $this->duplicate = 'Данный файл уже добавлен!';

    if( !isset($this->denied) )
      $this->denied = 'Вы не можете добавлять файлы данного типа';

    $this->options = CMap::mergeArray(array(
                                        'max'  => $this->model->maxFiles,
                                        'list' => '#'.$modelName.'_file_wrap_list'
                                      ), $this->options);

    if( !isset($this->htmlOptions['size']) )
      $this->htmlOptions['size'] = 1;
  }
}