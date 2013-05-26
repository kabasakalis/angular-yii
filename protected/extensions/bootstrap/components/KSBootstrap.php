<?php
/**
 *  class KSBootstrap
 *  @author: spiros kabasakalis <kabasakalis@gmail.com>
 * Date: 11/18/12
 * Time: 9:36 AM
 * Get the assets folder out from protected and into the webroot,so we don't have to publish it.
 * This is my personal preference,I hate "publishing assets".:P
 * And support Bootswatch themes (http://bootswatch.com/)
 */

class KSBootstrap extends Bootstrap
{

    /**
   	 * Registers the  default Bootstrap CSS from yiibooster_assets or a bootswatch theme,
     * based on parameter setting in config/main
   	 */
   	public function registerCoreCss()
   	{
      (app()->params['bootswatch_theme']=='default')?
      $this->registerAssetCss('bootstrap' . (!YII_DEBUG ? '.min' : '') . '.css') :
      $this->registerAssetCss('bootswatch/'.app()->params['bootswatch_theme'].'.min.css');
   	}

    /**
   	 * Returns the URL to the assets folder.Override to avoid publishing.
   	 * @return string the URL
   	 */
   	public function getAssetsUrl()
   	{
   		if (isset($this->_assetsUrl))
   			return $this->_assetsUrl;
   		else
   		{
            $assetsUrl=bu().'/yiibooster_assets';
   			return $this->_assetsUrl = $assetsUrl;
   		}
   	}

}
