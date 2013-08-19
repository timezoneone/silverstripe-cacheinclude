<?php

use Heyday\CacheInclude\CacheInclude;
use Heyday\CacheInclude\Processors\ViewableDataProcessor;
use Heyday\CacheInclude\KeyCreators\SilverStripeController;
use Heyday\CacheInclude\KeyCreators\KeyCreatorInterface;

/**
 * Class CacheIncludeExtension
 */
class CacheIncludeExtension extends Extension
{
    /**
     * @var Heyday\CacheInclude\CacheInclude
     */
    protected $cache;
    /**
     * @var Heyday\CacheInclude\Processors\ViewableDataProcessor
     */
    protected $processor;
    /**
     * @var array
     */
    private static $run = array();

    /**
     * @param CacheInclude          $cache
     * @param ViewableDataProcessor $processor
     * @param KeyCreatorInterface   $keyCreator
     */
    public function __construct(
        CacheInclude $cache,
        ViewableDataProcessor $processor,
        KeyCreatorInterface $keyCreator = null
    ) {
        $this->cache = $cache;
        $this->keyCreator = $keyCreator ?: new SilverStripeController($this->getController());
        $this->processor = $processor;
        parent::__construct();
    }
    /**
     * @return Controller
     */
    protected function getController()
    {
        $controller = $this->owner;
        if (!($controller instanceof Controller) || !($controller->getRequest() instanceof SS_HTTPRequest)) {
            $controller = Controller::curr();
        }

        return $controller;
    }
    /**
     * @param $name
     * @param $template
     * @return mixed|null
     */
    public function CacheIncludePartial($name, $template)
    {
        $controller = $this->getController();

        return $this->cache->process(
            $name,
            function () use ($template, $controller) {
                return $controller->renderWith(new SSViewer_FromString($template));
            },
            $this->keyCreator
        );
    }
    /**
     * @param $name
     * @return mixed
     */
    public function CacheInclude($name)
    {
        return $this->cache->process(
            $name,
            $this->processor->setContext($this->owner),
            $this->keyCreator
        );
    }
    /**
     * Remove invalid caches
     */
    public function onAfterWrite()
    {
        $this->onChange();
    }
    /**
     * Remove invalid caches
     */
    public function onAfterDelete()
    {
        $this->onChange();
    }
    /**
     * Remove invalid caches
     */
    public function onChange()
    {
        if (!isset(self::$run[$this->owner->ClassName])) {

            self::$run[$this->owner->ClassName] = true;

            $names = array();

            foreach ($this->cache->getConfig() as $name => $config) {

                if (isset($config['contains']) && is_array($config['contains'])) {

                    foreach ($config['contains'] as $class) {

                        if ($this->owner instanceof $class) {

                            $names[] = $name;

                            break;

                        }

                    }

                }

            }

            if (count($names) > 0) {

                foreach ($names as $name) {

                    $this->cache->flushByName($name);

                }

            }

        }
    }
    /**
     *
     */
    public function extraStatics()
    {

    }
}
