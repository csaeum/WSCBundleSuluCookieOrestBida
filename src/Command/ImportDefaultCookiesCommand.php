<?php

declare(strict_types=1);

namespace WSC\SuluCookieConsentBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use WSC\SuluCookieConsentBundle\Entity\Cookie;
use WSC\SuluCookieConsentBundle\Entity\CookieCategory;
use WSC\SuluCookieConsentBundle\Entity\CookieItem;
use WSC\SuluCookieConsentBundle\Repository\CookieCategoryRepository;
use WSC\SuluCookieConsentBundle\Repository\CookieItemRepository;
use WSC\SuluCookieConsentBundle\Repository\CookieRepository;

#[AsCommand(
    name: 'wsc:cookie-consent:import-defaults',
    description: 'Imports the default cookie categories and cookies'
)]
class ImportDefaultCookiesCommand extends Command
{
    public function __construct(
        private CookieCategoryRepository $categoryRepository,
        private CookieRepository $cookieRepository,
        private CookieItemRepository $cookieItemRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force update of existing entries')
            ->addOption('file', null, InputOption::VALUE_REQUIRED, 'Path to custom JSON file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $file = $input->getOption('file');
        if (!$file) {
            // Use the bundled default file
            $file = __DIR__ . '/../Resources/data/default-cookies.json';
        }

        if (!file_exists($file)) {
            $io->error('File not found: ' . $file);
            return Command::FAILURE;
        }

        $content = file_get_contents($file);
        $data = json_decode($content, true);

        if (!$data) {
            $io->error('Invalid JSON file');
            return Command::FAILURE;
        }

        $force = $input->getOption('force');
        $stats = [
            'categoriesCreated' => 0,
            'categoriesUpdated' => 0,
            'categoriesSkipped' => 0,
            'cookiesCreated' => 0,
            'cookiesUpdated' => 0,
            'cookiesSkipped' => 0,
            'cookieItemsCreated' => 0,
            'cookieItemsUpdated' => 0,
        ];

        $io->section('Importing Cookie Categories');

        if (isset($data['categories']) && is_array($data['categories'])) {
            foreach ($data['categories'] as $categoryData) {
                $result = $this->importCategory($categoryData, $force);
                $stats[$result]++;
                $io->writeln(sprintf(
                    '  %s: %s (%s)',
                    $result === 'categoriesSkipped' ? '[SKIP]' : ($result === 'categoriesCreated' ? '[NEW]' : '[UPD]'),
                    $categoryData['technicalName'],
                    str_replace('categories', '', $result)
                ));
            }
        }

        $this->entityManager->flush();

        $io->section('Importing Cookies');

        if (isset($data['cookies']) && is_array($data['cookies'])) {
            foreach ($data['cookies'] as $cookieData) {
                $result = $this->importCookie($cookieData, $force, $stats);
                $stats[$result]++;
                $io->writeln(sprintf(
                    '  %s: %s (%s)',
                    $result === 'cookiesSkipped' ? '[SKIP]' : ($result === 'cookiesCreated' ? '[NEW]' : '[UPD]'),
                    $cookieData['technicalName'],
                    str_replace('cookies', '', $result)
                ));
            }
        }

        $this->entityManager->flush();

        $io->success([
            'Import completed!',
            sprintf('Categories: %d created, %d updated, %d skipped',
                $stats['categoriesCreated'],
                $stats['categoriesUpdated'],
                $stats['categoriesSkipped']
            ),
            sprintf('Cookies: %d created, %d updated, %d skipped',
                $stats['cookiesCreated'],
                $stats['cookiesUpdated'],
                $stats['cookiesSkipped']
            ),
            sprintf('Cookie Items: %d created, %d updated',
                $stats['cookieItemsCreated'],
                $stats['cookieItemsUpdated']
            ),
        ]);

        return Command::SUCCESS;
    }

    private function importCategory(array $data, bool $force): string
    {
        $category = $this->categoryRepository->findByTechnicalName($data['technicalName']);

        if ($category && !$force) {
            return 'categoriesSkipped';
        }

        $isNew = false;
        if (!$category) {
            $category = new CookieCategory();
            $category->setTechnicalName($data['technicalName']);
            $this->entityManager->persist($category);
            $isNew = true;
        }

        $category->setEnabled($data['enabled'] ?? false);
        $category->setReadOnly($data['readOnly'] ?? false);
        $category->setPosition($data['position'] ?? 0);
        $category->setActive($data['active'] ?? true);

        if (isset($data['translations']) && is_array($data['translations'])) {
            foreach ($data['translations'] as $locale => $translationData) {
                $category->setLocale($locale);
                $category->setName($translationData['name'] ?? null);
                $category->setDescription($translationData['description'] ?? null);
            }
        }

        return $isNew ? 'categoriesCreated' : 'categoriesUpdated';
    }

    private function importCookie(array $data, bool $force, array &$stats): string
    {
        $cookie = $this->cookieRepository->findByTechnicalName($data['technicalName']);

        if ($cookie && !$force) {
            return 'cookiesSkipped';
        }

        $isNew = false;
        if (!$cookie) {
            $cookie = new Cookie();
            $cookie->setTechnicalName($data['technicalName']);
            $this->entityManager->persist($cookie);
            $isNew = true;
        }

        if (isset($data['categoryTechnicalName'])) {
            $category = $this->categoryRepository->findByTechnicalName($data['categoryTechnicalName']);
            if ($category) {
                $cookie->setCategory($category);
            }
        }

        $cookie->setProvider($data['provider'] ?? null);
        $cookie->setScriptUrl($data['scriptUrl'] ?? null);
        $cookie->setLegalBasis($data['legalBasis'] ?? Cookie::LEGAL_BASIS_CONSENT);
        $cookie->setProcessingLocation($data['processingLocation'] ?? Cookie::PROCESSING_LOCATION_EU);
        $cookie->setPosition($data['position'] ?? 0);
        $cookie->setActive($data['active'] ?? true);

        if (isset($data['translations']) && is_array($data['translations'])) {
            foreach ($data['translations'] as $locale => $translationData) {
                $cookie->setLocale($locale);
                $cookie->setName($translationData['name'] ?? null);
                $cookie->setDescription($translationData['description'] ?? null);
                $cookie->setPrivacyPolicyUrl($translationData['privacyPolicyUrl'] ?? null);
                $cookie->setDataCollected($translationData['dataCollected'] ?? null);
                $cookie->setDataPurpose($translationData['dataPurpose'] ?? null);
            }
        }

        if (isset($data['cookieItems']) && is_array($data['cookieItems'])) {
            foreach ($data['cookieItems'] as $itemData) {
                $result = $this->importCookieItem($cookie, $itemData, $force);
                $stats[$result]++;
            }
        }

        return $isNew ? 'cookiesCreated' : 'cookiesUpdated';
    }

    private function importCookieItem(Cookie $cookie, array $data, bool $force): string
    {
        $item = null;
        $isNew = false;

        if ($cookie->getId()) {
            $item = $this->cookieItemRepository->findByNameAndCookie($data['name'], $cookie->getId());
        }

        if ($item && !$force) {
            return 'cookieItemsUpdated';
        }

        if (!$item) {
            $item = new CookieItem();
            $item->setName($data['name']);
            $item->setCookie($cookie);
            $cookie->addCookieItem($item);
            $this->entityManager->persist($item);
            $isNew = true;
        }

        $item->setLifetime($data['lifetime'] ?? null);
        $item->setPosition($data['position'] ?? 0);
        $item->setActive($data['active'] ?? true);

        if (isset($data['translations']) && is_array($data['translations'])) {
            foreach ($data['translations'] as $locale => $translationData) {
                $item->setLocale($locale);
                $item->setDescription($translationData['description'] ?? null);
            }
        }

        return $isNew ? 'cookieItemsCreated' : 'cookieItemsUpdated';
    }
}
