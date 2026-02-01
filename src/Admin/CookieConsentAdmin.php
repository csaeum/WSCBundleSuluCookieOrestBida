<?php

declare(strict_types=1);

namespace WSC\SuluCookieConsentBundle\Admin;

use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use WSC\SuluCookieConsentBundle\Entity\Cookie;
use WSC\SuluCookieConsentBundle\Entity\CookieCategory;

class CookieConsentAdmin extends Admin
{
    public const SECURITY_CONTEXT = 'sulu.cookie_consent';

    public const COOKIE_CATEGORY_LIST_VIEW = 'wsc_cookie_consent.cookie_categories_list';
    public const COOKIE_CATEGORY_ADD_FORM_VIEW = 'wsc_cookie_consent.cookie_category_add_form';
    public const COOKIE_CATEGORY_EDIT_FORM_VIEW = 'wsc_cookie_consent.cookie_category_edit_form';

    public const COOKIE_LIST_VIEW = 'wsc_cookie_consent.cookies_list';
    public const COOKIE_ADD_FORM_VIEW = 'wsc_cookie_consent.cookie_add_form';
    public const COOKIE_EDIT_FORM_VIEW = 'wsc_cookie_consent.cookie_edit_form';

    public function __construct(
        private ViewBuilderFactoryInterface $viewBuilderFactory,
        private SecurityCheckerInterface $securityChecker
    ) {
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if (!$this->securityChecker->hasPermission(static::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            return;
        }

        $cookieConsentItem = new NavigationItem('wsc_cookie_consent.cookie_consent');
        $cookieConsentItem->setPosition(50);
        $cookieConsentItem->setIcon('su-cookie');

        $categoriesItem = new NavigationItem('wsc_cookie_consent.cookie_categories');
        $categoriesItem->setPosition(10);
        $categoriesItem->setView(static::COOKIE_CATEGORY_LIST_VIEW);
        $cookieConsentItem->addChild($categoriesItem);

        $cookiesItem = new NavigationItem('wsc_cookie_consent.cookies');
        $cookiesItem->setPosition(20);
        $cookiesItem->setView(static::COOKIE_LIST_VIEW);
        $cookieConsentItem->addChild($cookiesItem);

        $navigationItemCollection->add($cookieConsentItem);
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        if (!$this->securityChecker->hasPermission(static::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            return;
        }

        $locales = ['de', 'en'];

        // Cookie Categories List
        $categoryListView = $this->viewBuilderFactory->createListViewBuilder(
            static::COOKIE_CATEGORY_LIST_VIEW,
            '/cookie-categories/:locale'
        )
            ->setResourceKey(CookieCategory::RESOURCE_KEY)
            ->setListKey(CookieCategory::LIST_KEY)
            ->setTitle('wsc_cookie_consent.cookie_categories')
            ->addListAdapters(['table'])
            ->addLocales($locales)
            ->setDefaultLocale('de')
            ->setAddView(static::COOKIE_CATEGORY_ADD_FORM_VIEW)
            ->setEditView(static::COOKIE_CATEGORY_EDIT_FORM_VIEW)
            ->addToolbarActions([
                new ToolbarAction('sulu_admin.add'),
                new ToolbarAction('sulu_admin.delete'),
            ]);
        $viewCollection->add($categoryListView);

        // Cookie Category Add Form
        $categoryAddFormView = $this->viewBuilderFactory->createResourceTabViewBuilder(
            static::COOKIE_CATEGORY_ADD_FORM_VIEW,
            '/cookie-categories/:locale/add'
        )
            ->setResourceKey(CookieCategory::RESOURCE_KEY)
            ->addLocales($locales)
            ->setBackView(static::COOKIE_CATEGORY_LIST_VIEW);
        $viewCollection->add($categoryAddFormView);

        $categoryAddDetailsFormView = $this->viewBuilderFactory->createFormViewBuilder(
            static::COOKIE_CATEGORY_ADD_FORM_VIEW . '.details',
            '/details'
        )
            ->setResourceKey(CookieCategory::RESOURCE_KEY)
            ->setFormKey(CookieCategory::FORM_KEY)
            ->setTabTitle('sulu_admin.details')
            ->setEditView(static::COOKIE_CATEGORY_EDIT_FORM_VIEW)
            ->addToolbarActions([
                new ToolbarAction('sulu_admin.save'),
            ])
            ->setParent(static::COOKIE_CATEGORY_ADD_FORM_VIEW);
        $viewCollection->add($categoryAddDetailsFormView);

        // Cookie Category Edit Form
        $categoryEditFormView = $this->viewBuilderFactory->createResourceTabViewBuilder(
            static::COOKIE_CATEGORY_EDIT_FORM_VIEW,
            '/cookie-categories/:locale/:id'
        )
            ->setResourceKey(CookieCategory::RESOURCE_KEY)
            ->addLocales($locales)
            ->setBackView(static::COOKIE_CATEGORY_LIST_VIEW);
        $viewCollection->add($categoryEditFormView);

        $categoryEditDetailsFormView = $this->viewBuilderFactory->createFormViewBuilder(
            static::COOKIE_CATEGORY_EDIT_FORM_VIEW . '.details',
            '/details'
        )
            ->setResourceKey(CookieCategory::RESOURCE_KEY)
            ->setFormKey(CookieCategory::FORM_KEY)
            ->setTabTitle('sulu_admin.details')
            ->addToolbarActions([
                new ToolbarAction('sulu_admin.save'),
                new ToolbarAction('sulu_admin.delete'),
            ])
            ->setParent(static::COOKIE_CATEGORY_EDIT_FORM_VIEW);
        $viewCollection->add($categoryEditDetailsFormView);

        // Cookies List
        $cookieListView = $this->viewBuilderFactory->createListViewBuilder(
            static::COOKIE_LIST_VIEW,
            '/cookies/:locale'
        )
            ->setResourceKey(Cookie::RESOURCE_KEY)
            ->setListKey(Cookie::LIST_KEY)
            ->setTitle('wsc_cookie_consent.cookies')
            ->addListAdapters(['table'])
            ->addLocales($locales)
            ->setDefaultLocale('de')
            ->setAddView(static::COOKIE_ADD_FORM_VIEW)
            ->setEditView(static::COOKIE_EDIT_FORM_VIEW)
            ->addToolbarActions([
                new ToolbarAction('sulu_admin.add'),
                new ToolbarAction('sulu_admin.delete'),
            ]);
        $viewCollection->add($cookieListView);

        // Cookie Add Form
        $cookieAddFormView = $this->viewBuilderFactory->createResourceTabViewBuilder(
            static::COOKIE_ADD_FORM_VIEW,
            '/cookies/:locale/add'
        )
            ->setResourceKey(Cookie::RESOURCE_KEY)
            ->addLocales($locales)
            ->setBackView(static::COOKIE_LIST_VIEW);
        $viewCollection->add($cookieAddFormView);

        $cookieAddDetailsFormView = $this->viewBuilderFactory->createFormViewBuilder(
            static::COOKIE_ADD_FORM_VIEW . '.details',
            '/details'
        )
            ->setResourceKey(Cookie::RESOURCE_KEY)
            ->setFormKey(Cookie::FORM_KEY)
            ->setTabTitle('sulu_admin.details')
            ->setEditView(static::COOKIE_EDIT_FORM_VIEW)
            ->addToolbarActions([
                new ToolbarAction('sulu_admin.save'),
            ])
            ->setParent(static::COOKIE_ADD_FORM_VIEW);
        $viewCollection->add($cookieAddDetailsFormView);

        // Cookie Edit Form
        $cookieEditFormView = $this->viewBuilderFactory->createResourceTabViewBuilder(
            static::COOKIE_EDIT_FORM_VIEW,
            '/cookies/:locale/:id'
        )
            ->setResourceKey(Cookie::RESOURCE_KEY)
            ->addLocales($locales)
            ->setBackView(static::COOKIE_LIST_VIEW);
        $viewCollection->add($cookieEditFormView);

        $cookieEditDetailsFormView = $this->viewBuilderFactory->createFormViewBuilder(
            static::COOKIE_EDIT_FORM_VIEW . '.details',
            '/details'
        )
            ->setResourceKey(Cookie::RESOURCE_KEY)
            ->setFormKey(Cookie::FORM_KEY)
            ->setTabTitle('sulu_admin.details')
            ->addToolbarActions([
                new ToolbarAction('sulu_admin.save'),
                new ToolbarAction('sulu_admin.delete'),
            ])
            ->setParent(static::COOKIE_EDIT_FORM_VIEW);
        $viewCollection->add($cookieEditDetailsFormView);
    }

    /**
     * @return array<string, array<string, bool>>
     */
    public function getSecurityContexts(): array
    {
        return [
            self::SULU_ADMIN_SECURITY_SYSTEM => [
                'Cookie Consent' => [
                    static::SECURITY_CONTEXT => [
                        PermissionTypes::VIEW,
                        PermissionTypes::ADD,
                        PermissionTypes::EDIT,
                        PermissionTypes::DELETE,
                    ],
                ],
            ],
        ];
    }
}
