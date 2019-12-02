<?php
/* For licensing terms, see /license.txt */

namespace Chamilo\SettingsBundle\Twig;

use Chamilo\SettingsBundle\Templating\Helper\SettingsHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Sylius settings extension for Twig.
 *
 * @author Paweł Jędrzejewski <pawel@sylius.org>
 */
class SettingsExtension extends AbstractExtension
{
    /**
     * @var SettingsHelper
     */
    private $helper;

    /**
     * @param SettingsHelper $helper
     */
    public function __construct($helper)
    {
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
             new TwigFunction('chamilo_settings_all', [$this, 'getSettings']),
             new TwigFunction('chamilo_settings_get', [$this, 'getSettingsParameter']),
             new TwigFunction('chamilo_settings_has', [$this, 'hasSettingsParameter']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
             //new \Twig_SimpleFunction('chamilo_settings_all', array($this, 'getSettings')),
             new TwigFilter('get_setting', [$this, 'getSettingsParameter']),
             new TwigFilter('api_get_setting', [$this, 'getSettingsParameter']),
             //new \Twig_SimpleFunction('chamilo_settings_has', [$this, 'hasSettingsParameter']),
        ];
    }

    /**
     * Load settings from given namespace.
     *
     * @param string $namespace
     *
     * @return array
     */
    public function getSettings($namespace)
    {
        return $this->helper->getSettings($namespace);
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function getSettingsParameter($name)
    {
        return $this->helper->getSettingsParameter($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'chamilo_settings';
    }
}
