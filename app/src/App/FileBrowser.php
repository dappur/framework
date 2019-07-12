<?php

namespace Dappur\App;

/**
 * Copied from https://github.com/vakata/jstree-php-demos/blob/master/filebrowser/index.php
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */

class FileBrowser
{
    protected $base = null;

    protected function real($path)
    {
        $temp = realpath($path);
        if (!$temp) {
            throw new Exception('Path does not exist: ' . $path);
        }
        if ($this->base && strlen($this->base)) {
            if (strpos($temp, $this->base) !== 0) {
                throw new Exception('Path is not inside base ('.$this->base.'): ' . $temp);
            }
        }
        return $temp;
    }

    protected function path($pathId)
    {
        $pathId = str_replace('/', DIRECTORY_SEPARATOR, $pathId);
        $pathId = trim($pathId, DIRECTORY_SEPARATOR);
        $pathId = $this->real($this->base . DIRECTORY_SEPARATOR . $pathId);
        return $pathId;
    }

    protected function pathId($path)
    {
        $path = $this->real($path);
        $path = substr($path, strlen($this->base));
        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
        $path = trim($path, '/');
        return strlen($path) ? $path : '/';
    }

    public function __construct($base)
    {
        $this->base = $this->real($base);
        if (!$this->base) {
            throw new Exception('Base directory does not exist');
        }
    }

    public function lst($pathId, $withRoot = 0)
    {
        $dir = $this->path($pathId);
        $lst = @scandir($dir);
        if (!$lst) {
            throw new Exception('Could not list path: ' . $dir);
        }
        $res = array();
        foreach ($lst as $item) {
            if ($item == '.' || $item == '..' || $item === null || $item == '.gitkeep' || $item == '.gitignore') {
                continue;
            }
            $tmp = preg_match('([^ a-zа-я-_0-9.]+)ui', $item);
            if ($tmp === false || $tmp === 1) {
                continue;
            }
            if (is_dir($dir . DIRECTORY_SEPARATOR . $item)) {
                $res[] = array(
                    'text' => $item,
                    'children' => true,
                    'id' => $this->pathId($dir . DIRECTORY_SEPARATOR . $item),
                    'icon' => 'folder'
                );
            }
            if (!is_dir($dir . DIRECTORY_SEPARATOR . $item)) {
                $res[] = array(
                    'text' => $item,
                    'children' => false,
                    'id' => $this->pathId($dir . DIRECTORY_SEPARATOR . $item),
                    'type' => 'file',
                    'icon' => 'file file-'.substr($item, strrpos($item, '.') + 1)
                );
            }
        }
        if ($withRoot && $this->pathId($dir) === '/') {
            $res = array(array(
                'text' => basename($this->base),
                'children' => $res,
                'id' => '/',
                'icon'=>'folder',
                'state' => array('opened' => true, 'disabled' => true)
            ));
        }
        return $res;
    }

    public function data($pathId)
    {
        if (strpos($pathId, ":")) {
            $pathId = array_map(array($this, 'id'), explode(':', $pathId));
            return array('type'=>'multiple', 'content'=> 'Multiple selected: ' . implode(' ', $pathId));
        }
        $dir = $this->path($pathId);
        if (is_dir($dir)) {
            return array('type'=>'folder', 'content'=> $pathId);
        }
        if (is_file($dir)) {
            $ext = strpos($dir, '.') !== false ? substr($dir, strrpos($dir, '.') + 1) : '';
            $dat = array('type' => $ext, 'content' => '');
            switch ($ext) {
                case 'txt':
                case 'text':
                case 'md':
                case 'js':
                case 'json':
                case 'css':
                case 'html':
                case 'htm':
                case 'xml':
                case 'c':
                case 'cpp':
                case 'h':
                case 'sql':
                case 'log':
                case 'py':
                case 'rb':
                case 'htaccess':
                case 'php':
                    $dat['content'] = file_get_contents($dir);
                    break;
                case 'jpg':
                case 'jpeg':
                case 'gif':
                case 'png':
                case 'bmp':
                    $dat['content'] = 'data:'.finfo_file(finfo_open(FILEINFO_MIME_TYPE), $dir).
                        ';base64,'.base64_encode(file_get_contents($dir));
                    break;
                default:
                    $dat['content'] = 'File not recognized: '.$this->pathId($dir);
                    break;
            }
            return $dat;
        }
        throw new Exception('Not a valid selection: ' . $dir);
    }

    public function create($pathId, $name, $mkdir = 0)
    {
        $dir = $this->path($pathId);
        if (preg_match('([^ a-zа-я-_0-9.]+)ui', $name) || !strlen($name)) {
            throw new Exception('Invalid name: ' . $name);
        }
        if ($mkdir) {
            mkdir($dir . DIRECTORY_SEPARATOR . $name);
        }
        if (!$mkdir) {
            file_put_contents($dir . DIRECTORY_SEPARATOR . $name, '');
        }
        return array('id' => $this->pathId($dir . DIRECTORY_SEPARATOR . $name));
    }

    public function rename($pathId, $name)
    {
        $dir = $this->path($pathId);
        if ($dir === $this->base) {
            throw new Exception('Cannot rename root');
        }
        if (preg_match('([^ a-zа-я-_0-9.]+)ui', $name) || !strlen($name)) {
            throw new Exception('Invalid name: ' . $name);
        }
        $new = explode(DIRECTORY_SEPARATOR, $dir);
        array_pop($new);
        array_push($new, $name);
        $new = implode(DIRECTORY_SEPARATOR, $new);
        if ($dir !== $new) {
            if (is_file($new) || is_dir($new)) {
                throw new Exception('Path already exists: ' . $new);
            }
            rename($dir, $new);
        }
        return array('id' => $this->pathId($new));
    }

    public function remove($pathId)
    {
        $dir = $this->path($pathId);
        if ($dir === $this->base) {
            throw new Exception('Cannot remove root');
        }
        if (is_dir($dir)) {
            foreach (array_diff(scandir($dir), array(".", "..")) as $f) {
                $this->remove($this->pathId($dir . DIRECTORY_SEPARATOR . $f));
            }
            rmdir($dir);
        }
        if (is_file($dir)) {
            unlink($dir);
        }
        return array('status' => 'OK');
    }

    public function move($pathId, $par)
    {
        $dir = $this->path($pathId);
        $par = $this->path($par);
        $new = explode(DIRECTORY_SEPARATOR, $dir);
        $new = array_pop($new);
        $new = $par . DIRECTORY_SEPARATOR . $new;
        rename($dir, $new);
        return array('id' => $this->pathId($new));
    }
    
    public function copy($pathId, $par)
    {
        $dir = $this->path($pathId);
        $par = $this->path($par);
        $new = explode(DIRECTORY_SEPARATOR, $dir);
        $new = array_pop($new);
        $new = $par . DIRECTORY_SEPARATOR . $new;
        if (is_file($new) || is_dir($new)) {
            throw new Exception('Path already exists: ' . $new);
        }

        if (is_dir($dir)) {
            mkdir($new);
            foreach (array_diff(scandir($dir), array(".", "..")) as $f) {
                $this->copy($this->pathId($dir . DIRECTORY_SEPARATOR . $f), $this->pathId($new));
            }
        }
        if (is_file($dir)) {
            copy($dir, $new);
        }
        return array('id' => $this->pathId($new));
    }
}
