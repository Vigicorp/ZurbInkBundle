<?php

/*
 * This file is part of the zurb-ink-bundle package.
 *
 * (c) Marco Polichetti <gremo1982@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gremo\ZurbInkBundle\Twig;

use Gremo\ZurbInkBundle\Service\CssContainer;
use Gremo\ZurbInkBundle\Service\InlineCss;
use Symfony\Component\Config\FileLocatorInterface;
use Twig_Extension;
use Twig_Extension_GlobalsInterface;
use Twig_SimpleFunction;

class InlineCssExtension extends Twig_Extension implements Twig_Extension_GlobalsInterface
{
    protected $inlineCss;
    protected $cssContainer;
    protected $fileLocator;

    public function __construct(InlineCss $inlineCss, CssContainer $cssContainer, FileLocatorInterface $fileLocator)
    {
        $this->inlineCss = $inlineCss;
        $this->cssContainer = $cssContainer;
        $this->fileLocator = $fileLocator;
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobals()
    {
        return array(
            "zurb_ink_styles" => $this->cssContainer,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('includeStyles', array($this, 'includeStyles')),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return array(
            new InlineCssTokenParser(),
        );
    }

    /**
     * @param mixed $styles
     * @return string
     */
    public function includeStyles($styles)
    {
        if (is_string($styles)) {
            $styles = array($styles);
        }

        $style = null;
        foreach ($styles as $styleFile) {
            $path = $this->fileLocator->locate($styleFile);
            $style .= file_get_contents($path).PHP_EOL.PHP_EOL;
        }

        return (string) $style;
    }

    /**
     * Inlines all collected CSS into the passed HTML and returns the inlined HTML
     *
     * @param string $html
     * @return string
     */
    public function inlineStyles($html)
    {
        $css = null;
        foreach ($this->cssContainer as $pathname) {
            $css .= file_get_contents($this->fileLocator->locate($pathname)).PHP_EOL.PHP_EOL;
        }

        $this->inlineCss->setHtml($html);
        $this->inlineCss->setCss($css);

        return $this->inlineCss->convert();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return "zurb_ink.inlinecss";
    }
}
