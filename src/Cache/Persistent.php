<?php
 
 namespace glx\Cache;
 
 use glx\Cache\E\NotAvailable;

 require_once 'I/Persistent.php';
 
 class Persistent implements I\Persistent
 {
    private static array $implementations;
    /** @var I\Persistent */
    private I\Persistent $implementation;
  
    public function __construct(array $options = [])
    {
      $implementation = $options['storage'] ?? 'apcu';
      $implementation = self::$implementations[$implementation];
      if($options['fallback'])
        $fallback = $options['fallback'];
      if(!$implementation)
        throw new \glx\Exception('Can`t load cache implementation');
      else
       try { $this->implementation = new $implementation(); }
       catch(NotAvailable $e)
        {
         if(isset($fallback))
           foreach($fallback as $storage)
             try { $this->implementation = new self::$implementations[$storage]; }
             catch(NotAvailable $e) { continue; }
         if(!$this->implementation)
           throw $e;
        }
    }
 
    public function get(string $key)
    {
      return $this->implementation->get($key);
    }
 
    public function store(string $key, $value, int $lifetime = 0): void
    {
      $this->implementation->store($key, $value, $lifetime);
    }
  
    public function delete(string $key, bool $search = false): void
    {
      $this->implementation->delete($key, $search);
    }
 
    public static function register(string $label, string $class): void
    {
      self::$implementations[$label] = $class;
    }
 }
 
 foreach(new \DirectoryIterator(__DIR__.'/Persistent') as $factory)
  {
   if(in_array($factory->getFilename(), ['.', '..'])) continue;
   include_once $factory->getPathname();
  }
 