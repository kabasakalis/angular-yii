<?php
/**
 * KTbToggleColumn.php class file
 *
 * ${DESCRIPTION}
 *
 * Date: 3/9/13
 * Time: 10:01 AM
 *
 * @author: Spiros Kabasakalis <kabasakalis@gmail.com>
 * @copyright Copyright &copy; Spiros Kabasakalis 2013
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package
 */

Yii::import('bootstrap.widgets.TbToggleColumn');
class KTbToggleColumn extends TbToggleColumn
{

public $icon_filename_checked='tick.png';
public $icon_filename_unchecked='cross.png';

    public $hint_checked='on';
    public $hint_unchecked='off';


 public $iconFolder='/css/icons/';
/**
   	 * Renders the data cell content.
   	 * This method renders the view, update and toggle buttons in the data cell.
   	 * @param integer $row the row number (zero-based)
   	 * @param mixed $data the data associated with the row
   	 */
   	protected function renderDataCellContent($row, $data)
   	{
   		$checked = CHtml::value($data, $this->name);
   		$button = $this->button;
   		$button['icon'] = $checked ? $this->checkedIcon : $this->uncheckedIcon;
   		$button['url'] = isset($button['url']) ? $this->evaluateExpression($button['url'], array('data' => $data, 'row' => $row)) : '#';

   		if(!$this->displayText)
   		{
            $icon= CHtml::image($checked?Yii::app()->baseUrl.$this->iconFolder.$this->icon_filename_checked:
                                                              Yii::app()->baseUrl.$this->iconFolder.$this->icon_filename_unchecked,
                                                               $checked?$this->checkedButtonLabel:$this->uncheckedButtonLabel);
   			//$button['htmlOptions']['title'] = $checked ? $this->checkedButtonLabel : $this->uncheckedButtonLabel;
   			//$button['htmlOptions']['rel'] = 'tooltip';
               $button['htmlOptions']['data-hint'] =$checked ? $this->hint_checked: $this->hint_unchecked;
               $button['htmlOptions']['class'] = $button['htmlOptions']['class'].' hint--bottom';
   			echo CHtml::link($icon, $button['url'], $button['htmlOptions']);
   		}
   		else
   		{
   			$button['label'] = $checked ? $this->checkedButtonLabel : $this->uncheckedButtonLabel;
   			$button['class'] = 'bootstrap.widgets.TbButton';
   			$widget = Yii::createComponent($button);
   			$widget->init();
   			$widget->run();
   		}
   	}




}

