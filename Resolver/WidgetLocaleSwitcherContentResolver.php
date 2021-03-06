<?php

namespace Victoire\Widget\LocaleSwitcherBundle\Resolver;

use Doctrine\ORM\EntityManager;
use Victoire\Bundle\CoreBundle\Helper\CurrentViewHelper;
use Victoire\Bundle\I18nBundle\Resolver\LocaleResolver;
use Victoire\Bundle\PageBundle\Helper\PageHelper;
use Victoire\Bundle\WidgetBundle\Model\Widget;
use Victoire\Bundle\WidgetBundle\Resolver\BaseWidgetContentResolver;

/**
 * CRUD operations on WidgetLocaleSwitcher Widget.
 *
 * The widget view has two parameters: widget and content
 *
 * widget: The widget to display, use the widget as you wish to render the view
 * content: This variable is computed in this WidgetManager, you can set whatever you want in it and display it in the show view
 *
 * The content variable depends of the mode: static/businessEntity/entity/query
 *
 * The content is given depending of the mode by the methods:
 *  getWidgetStaticContent
 *  getWidgetBusinessEntityContent
 *  getWidgetEntityContent
 *  getWidgetQueryContent
 *
 * So, you can use the widget or the content in the show.html.twig view.
 * If you want to do some computation, use the content and do it the 4 previous methods.
 *
 * If you just want to use the widget and not the content, remove the method that throws the exceptions.
 *
 * By default, the methods throws Exception to notice the developer that he should implements it owns logic for the widget
 */
class WidgetLocaleSwitcherContentResolver extends BaseWidgetContentResolver
{
    protected $locales;
    protected $em;
    protected $pageHelper;
    protected $currentViewHelper;
    protected $localeResolver;
    protected $localePattern;

    public function __construct($locales, EntityManager $em, PageHelper $pageHelper, CurrentViewHelper $currentViewHelper, LocaleResolver $localeResolver, $localePattern)
    {
        $this->locales = $locales;
        $this->em = $em;
        $this->pageHelper = $pageHelper;
        $this->currentViewHelper = $currentViewHelper;
        $this->localeResolver = $localeResolver;
        $this->localePattern = $localePattern;
    }

    /**
     * Get the static content of the widget.
     *
     * @param Widget $widget
     *
     * @return string The static content
     */
    public function getWidgetStaticContent(Widget $widget)
    {
        $widgetParams = parent::getWidgetStaticContent($widget);

        $currentView = $this->currentViewHelper->getCurrentView();
        $i18n = $this->currentViewHelper->getCurrentView()->getI18n();
        unset($this->locales[$currentView->getLocale()]);

        $translations = [];
        foreach ($this->locales as $locale) {
            //get the homepage if the page doesn't exists in the given locale
            if (null === $page = $i18n->getTranslation($locale)) {
                $page = $this->em->getRepository('VictoirePageBundle:BasePage')->findOneByHomepage($locale);
            }

            //build page parameters to build a link in front
            $pageParameters = [
                'linkType'      => 'viewReference',
                'viewReference' => $this->pageHelper->getViewReferenceByView($page),
                'target'        => '_parent',
            ];

            $translations[$locale] = $pageParameters;
        }
        $widgetParams['translations'] = $translations;

        return $widgetParams;
    }

    /**
     * Get the business entity content.
     *
     * @param Widget $widget
     *
     * @return string
     */
    public function getWidgetBusinessEntityContent(Widget $widget)
    {
        return parent::getWidgetBusinessEntityContent($widget);
    }

    /**
     * Get the content of the widget by the entity linked to it.
     *
     * @param Widget $widget
     *
     * @return string
     */
    public function getWidgetEntityContent(Widget $widget)
    {
        return parent::getWidgetEntityContent($widget);
    }

    /**
     * Get the content of the widget for the query mode.
     *
     * @param Widget $widget
     *
     * @throws \Exception
     */
    public function getWidgetQueryContent(Widget $widget)
    {
        return parent::getWidgetQueryContent($widget);
    }
}
