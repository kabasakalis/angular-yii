<?php $this->pageTitle=Yii::app()->name;  ?>

<h1>Welcome to <i><?php echo CHtml::encode(Yii::app()->name); ?></i></h1>
<p>You may change the content of this page by modifying the following two files:</p>
<ul>
	<li>Views file: <?php echo __FILE__; ?></li>
	<li>Layout file: <?php echo $this->getLayoutFile('main'); ?></li>
</ul>

<p>For more details on how to further develop this application, please read
the <a href="http://www.yiiframework.com/doc/">documentation</a>.
Feel free to ask in the <a href="http://www.yiiframework.com/forum/">forum</a>
