# Akt for php

Akt is a *small* and *flexible* automatization tool written in php with *php syntax*.

## Yet another automatization tool?

Yep, you're absolutely right. Another automatization tool.  
There are a lot of such scripts written in different languages for different platforms.  
And here is another one...  

## Let's look for existing solutions

I have tried some of similar tools:  
_Ant_ - great, powerful, popular, but java and xml
_Phing_ - ant clone in php, therefore we have the same xml syntax  
_Make_ - no comments  
_Rake_ - this is in ruby...  
_Capistrano_ - this is in ruby...  
_Fabric_ - this is in python...  

## And what we have in php with php syntax?

I don't realy know. Yeah, i have reviewed pake, phake, weploy, phpdeploy and more more more  
Good tools, good job guys. But this is not what I realy need.  

So this is my attempt to make perfect php automatization tool... :)  
_Let's go!_

## Aktfile sample script

    $buildDir = '/home/build/production/';
    $devDir = '/home/dev/site/';

	function task_default()
	{
	    depends('clean', 'copySource');

	    task('minify', array('path' => $buildDir));

	    $production = new Connection('ssh', array(
	        'host' => 'production.com',
	        'username' => 'root'
	    ));

	    $production->sync('/home/site/', $buildDir);
	}

	function task_clean()
	{
	    dir::recreate($buildDir);
	}

	function task_copySource()
	{
	    $source = new Fileset($devDir);
	    $source->include('**');
	    $source->exclude('**/.git/**');
	    $source->copy($buildDir);
	}

	class MinifyTask extends Akt_Task
	{
	    public function execute()
	    {
	        // Minify operations here...
	        $phpFiles = new Fileset($buildDir);
	        $phpFiles->include('**/*.php');
	        task('strip_php_comments', $phpFiles);
	    }
	}
