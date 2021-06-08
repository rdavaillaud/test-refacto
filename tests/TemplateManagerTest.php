<?php

require_once __DIR__ . '/../src/Entity/Destination.php';
require_once __DIR__ . '/../src/Entity/Quote.php';
require_once __DIR__ . '/../src/Entity/Site.php';
require_once __DIR__ . '/../src/Entity/Template.php';
require_once __DIR__ . '/../src/Entity/User.php';
require_once __DIR__ . '/../src/Helper/SingletonTrait.php';
require_once __DIR__ . '/../src/Context/ApplicationContext.php';
require_once __DIR__ . '/../src/Repository/Repository.php';
require_once __DIR__ . '/../src/Repository/DestinationRepository.php';
require_once __DIR__ . '/../src/Repository/QuoteRepository.php';
require_once __DIR__ . '/../src/Repository/SiteRepository.php';
require_once __DIR__ . '/../src/TemplateManager.php';

class TemplateManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Init the mocks
     */
    public function setUp()
    {
    }

    /**
     * Closes the mocks
     */
    public function tearDown()
    {
    }

    /**
     * @test
     */
    public function testItFillTheTemplateWithoutDestinationLink()
    {
        $faker = \Faker\Factory::create();

        $destinationId                  = $faker->randomNumber();
        $expectedDestination = DestinationRepository::getInstance()->getById($destinationId);
        $expectedUser        = ApplicationContext::getInstance()->getCurrentUser();


        $quote = new Quote($faker->randomNumber(), $faker->randomNumber(), $destinationId, $faker->date());
        $usefulObject = SiteRepository::getInstance()->getById($quote->siteId);

        $template = new Template(
            1,
            'Votre livraison à [quote:destination_name]',
            "
Bonjour [user:first_name],

Merci de nous avoir contacté pour votre livraison à [quote:destination_name].

Bien cordialement,

L'équipe Dummy.com
");
        $templateManager = new TemplateManager();

        $message = $templateManager->getTemplateComputed(
            $template,
            [
                'quote' => $quote
            ]
        );

        $this->assertEquals('Votre livraison à ' . $expectedDestination->countryName, $message->subject);
        $this->assertEquals("
Bonjour " . $expectedUser->firstname . ",

Merci de nous avoir contacté pour votre livraison à " . $expectedDestination->countryName . ".

Bien cordialement,

L'équipe Dummy.com
", $message->content);
    }

    /**
     * @test
     */
    public function testItFillTheTemplateWithDestinationLink()
    {
        $faker = \Faker\Factory::create();

        $destinationId                  = $faker->randomNumber();
        $expectedDestination = DestinationRepository::getInstance()->getById($destinationId);
        $expectedUser        = ApplicationContext::getInstance()->getCurrentUser();


        $quote = new Quote($faker->randomNumber(), $faker->randomNumber(), $destinationId, $faker->date());
        $usefulObject = SiteRepository::getInstance()->getById($quote->siteId);

        $template = new Template(
            1,
            'Votre livraison à [quote:destination_name]',
            "
Bonjour [user:first_name],

Merci de nous avoir contacté pour votre livraison à [quote:destination_name].

Vous pouvez voir les détails de votre devis à cette adresse: [quote:destination_link]

Bien cordialement,

L'équipe Dummy.com
");
        $templateManager = new TemplateManager();

        $message = $templateManager->getTemplateComputed(
            $template,
            [
                'quote' => $quote
            ]
        );

        $this->assertEquals('Votre livraison à ' . $expectedDestination->countryName, $message->subject);
        $this->assertEquals("
Bonjour " . $expectedUser->firstname . ",

Merci de nous avoir contacté pour votre livraison à " . $expectedDestination->countryName . ".

Vous pouvez voir les détails de votre devis à cette adresse: " . $usefulObject->url . '/' . $expectedDestination->countryName . '/quote/' . $quote->id . "

Bien cordialement,

L'équipe Dummy.com
", $message->content);
    }

    /**
     * @test
     */
    public function testItShowsQuoteSummaryHtml()
    {
        $faker = \Faker\Factory::create();

        $destinationId                  = $faker->randomNumber();
        $expectedDestination = DestinationRepository::getInstance()->getById($destinationId);
        $expectedUser        = ApplicationContext::getInstance()->getCurrentUser();


        $quote = new Quote($faker->randomNumber(), $faker->randomNumber(), $destinationId, $faker->date());
        $expectedSummary = Quote::renderHtml($quote);

        $template = new Template(
            1,
            'subject',
            "
[quote:summary_html]
");
        $templateManager = new TemplateManager();

        $message = $templateManager->getTemplateComputed(
            $template,
            [
                'quote' => $quote
            ]
        );


        $this->assertEquals("
" . $expectedSummary . "
", $message->content);

    }

    /**
     * @test
     */
    public function testItShowsQuoteSummary()
    {
        $faker = \Faker\Factory::create();

        $destinationId                  = $faker->randomNumber();
        $expectedDestination = DestinationRepository::getInstance()->getById($destinationId);
        $expectedUser        = ApplicationContext::getInstance()->getCurrentUser();


        $quote = new Quote($faker->randomNumber(), $faker->randomNumber(), $destinationId, $faker->date());
        $expectedSummary = Quote::renderText($quote);

        $template = new Template(
            1,
            'subject',
            "
[quote:summary]
");
        $templateManager = new TemplateManager();

        $message = $templateManager->getTemplateComputed(
            $template,
            [
                'quote' => $quote
            ]
        );


        $this->assertEquals("
" . $expectedSummary . "
", $message->content);

    }
}
