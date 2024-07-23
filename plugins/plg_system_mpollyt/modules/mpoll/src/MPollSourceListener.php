<?php

use YOOtheme\Builder\Source;
use YOOtheme\Builder\BuilderConfig;

class MPollSourceListener
{
    /**
     * @param Source $source
     */
    public static function initSource($source)
    {
	    $source->objectType('MPollResultsType', MPollResultsType::config());

	    $source->queryType(MPollResultsQueryType::config());
    }

	public static function initCustomizer(BuilderConfig $config) {
		
	}
}
