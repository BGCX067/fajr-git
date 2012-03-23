#!/usr/bin/php
<?php
use fajr\config\FajrConfigLoader;

// register our autoloader
require_once (__DIR__ . '/../libfajr/src/libfajr.php');
Loader::register();
Loader::searchForClasses(__DIR__ . '/../src', true);
Loader::searchForClasses(__DIR__ . '/../libfajr/src', true);

if (!FajrConfigLoader::isConfigured()) {
  echo 'Chyba: Fajr nie je nakonfigurovany'. "\n";
  return;
}

$config = FajrConfigLoader::getConfiguration();

if (!$config->get('Template.Cache')) {
  echo 'Info: Template cache je vypnuta, nema zmysel ju mazat'. "\n";
  return;
}

function clearDirectory($path) {
  foreach (new DirectoryIterator($path) as $fileInfo) {
    if (!$fileInfo->isDot() && ($fileInfo->isFile() || $fileInfo->isDir())) {
      if ($fileInfo->isDir()) {
        clearDirectory($fileInfo->getPathname());
        rmdir($fileInfo->getPathname());
      }
      else {
        unlink($fileInfo->getPathname());
      }
    }
  }
}

$path = $config->getDirectory('Template.Cache.Path');
echo 'Info: Template cache je ' . $path . "\n";

clearDirectory($path);