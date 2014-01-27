<?php
namespace Craft;

class DatabaseImportExportPlugin extends BasePlugin
{
	function getName()
	{
		return Craft::t('Database Import/Export');
	}

	function getVersion()
	{
		return '0.1.0';
	}

	function getDeveloper()
	{
		return 'Rebel Courage';
	}

	function getDeveloperUrl()
	{
		return 'http://rebelcourage.com';
	}

	function hasCpSection()
	{
		return true;
	}
}
