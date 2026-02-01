<?php

declare(strict_types=1);

namespace WSC\SuluCookieConsentBundle\Controller\Website;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WSC\SuluCookieConsentBundle\Service\CookieConsentConfigProvider;

class CookieConsentController extends AbstractController
{
    public function __construct(
        private CookieConsentConfigProvider $configProvider
    ) {
    }

    public function configAction(Request $request): Response
    {
        $locale = $request->getLocale() ?: 'de';

        $config = $this->configProvider->getFullConfig($locale);

        $response = new JsonResponse($config);
        $response->setMaxAge(300); // Cache for 5 minutes
        $response->setPublic();

        return $response;
    }
}
