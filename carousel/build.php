<?php

define('CLI', php_sapi_name() == 'cli');
define('NL', CLI ? "\n" : '<br>');
define('CD', dirname(__FILE__));
define('CONFIG_IN', CD.'/config.json');
define('CONFIG_OUT', CD.'/config.js');
define('DATA_DIR_NAME', 'data');
define('DATA_DIR', CD.'/'.DATA_DIR_NAME);
define('INDEX_TPL', CD.'/tpl.html');
define('INDEX_OUT', CD.'/index.html');
define('ERR', 'ERROR: ');
define('WARN', 'WARNING: ');

print '*** Carousel configuration builder ***'.NL;

$config = readConfig();
$data = readDataDir();

if (empty($config->dataset))
  die(ERR.'dataset array is empty');

print 'build datasets...'.NL;

$lkupIds = [];

foreach($config->dataset as $di => $dataset) {  
  $id = $dataset->id;
  $resList = [];
  $portion = [];
  $sumWeight = 0;
  $sumFiles = 0;

  if (array_key_exists($id, $lkupIds))
    die(ERR."[$id] id is already exist");

  $lkupIds[$id] = $dataset;

  foreach($dataset->source as $si => $src) {  
    if (isset($src->skip) && $src->skip)  
        continue;    

    $setName = $src->path;    

    if (!array_key_exists($setName, $data))
      die(ERR."[$id] set with name \"$setName\" not found");
    
    $files = $data[$setName];
    
    if (isset($src->min)) {
      $v = (int)$src->min;
      if (count($files) < $v)
        die(ERR."[$id:$setName] files count is not enough for minmum limit ($q)");
    }
    if (isset($src->max)) {
      $v = (int)$src->max;
      $files = array_slice($files, 0, $v);
    }
    
    if (isset($src->quantity)) {
      $v = (int)$src->quantity;
      if ($v > count($files))
        die(ERR."[$id:$setName] files count is not enough for required quantity ($v)");
      $resList = array_merge(array_slice($files, 0, $v), $resList);
    } else if (isset($src->portion)) {
      $w = (int)$src->portion;
      $portion[] = ['weight'=>$w, 'src'=>$files, 'name'=>$setName];
      $sumFiles += count($files);
      $sumWeight += $w;
    }
  }

  foreach($portion as $p) {
    $setName = $p['name'];
    $f = 1.0 * $p['weight'] / $sumWeight;
    $n = floor($sumFiles * $f + 0.5);
    $z = count($p['src']);
    if ($n > $z) {      
      print WARN."[$id:$setName] files count is not enough for required portion ($z/$n)".NL;
      $n = $z;
    } else {
      print "[$id:$setName] $n files used from $z".NL;
    }
    $resList = array_merge(array_slice($p['src'], 0, $n), $resList);
  }

  if (isset($dataset->unqique) && $dataset->unqique) {
    $resList = array_unique($resList);
  }

  if (isset($dataset->shuffle) && $dataset->shuffle) {
    shuffle($resList);
  }

  if (isset($dataset->max)) {
    $max = (int)$dataset->max;
    $resList = array_slice($resList, 0, $max);
  }

  $config->dataset[$di]->source = $resList;
}

writeConfig($config);
writeIndex(['TIMESTAMP'=>time()]);

die('finish');

// ==================================================
// functions
// ==================================================

function readConfig() {
  print 'read config '.CONFIG_IN.NL;

  if (!file_exists(CONFIG_IN))
    die(ERR.'config file not found');
  
  $str = file_get_contents(CONFIG_IN);
  $config = json_decode($str);

  return $config;
}

function writeConfig($config) {
  print 'write config '.CONFIG_OUT.NL;
  $str = json_encode($config, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
  $str = "window.CarouselConfig = ".$str.";";
  file_put_contents(CONFIG_OUT, $str);
}

function readDataDir() {
  print 'read data dir '.DATA_DIR.NL;
  
  if (!is_dir(DATA_DIR))
    die(ERR.'data dir not found');

  $res = [];

  foreach(scandir(DATA_DIR) as $str) {
    $path = DATA_DIR.'/'.$str;
    if ($str[0] == '.' || !is_dir($path))
      continue;
    $res[$str] = readFiles($path);
  }

  return $res;
}

function readFiles($dir) {  
  print 'read files list '.$dir.NL;

  $res = [];
  $allowedExt = ['jpg', 'jpeg', 'png', 'svg'];
  
  foreach(scandir($dir) as $str) {
    $path = $dir.'/'.$str;
    if ($str[0] == '.' || is_dir($path))
      continue;
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));    
    if (in_array($ext, $allowedExt)) {
      //$res[] = $path;      
      $res[] = DATA_DIR_NAME.str_replace(DATA_DIR, '', $path);
    }
  }

  print 'found '.count($res).' file(s)'.NL;
  return $res;
}

function writeIndex($replacers) {
  if (!file_exists(INDEX_TPL))
    return;

  print 'build index file '.INDEX_TPL.' ---> '.INDEX_OUT.NL;
  
  $str = file_get_contents(INDEX_TPL);

  foreach($replacers as $from => $to) {
    $p = '{'.$from.'}';
    $str = str_replace($p, $to, $str);
  }

  file_put_contents(INDEX_OUT, $str);
}