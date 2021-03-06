<?php

require_once __DIR__ . '/../src/Entity/TemplateDataSourceInterface.php';
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

    public function data()
    {
        $faker = \Faker\Factory::create();
        $destinationId = $faker->randomNumber();
        $expectedDestination = DestinationRepository::getInstance()->getById($destinationId);
        $user = new User($faker->randomNumber(), $faker->firstName, $faker->lastName, $faker->email);
        $expectedDate = $faker->date();


        return [
            "quote but no user" => [
                [
                    'quote' => new Quote($faker->randomNumber(), $faker->randomNumber(), $destinationId, $expectedDate)
                ],
                $expectedDestination->countryName,
                ApplicationContext::getInstance()->getCurrentUser(),
                $expectedDate
            ],
            "quote and user" => [
                [
                    'quote' => new Quote($faker->randomNumber(), $faker->randomNumber(), $destinationId, $expectedDate),
                    'user' => $user
                ],
                $expectedDestination->countryName,
                $user,
                $expectedDate
            ],
            "no quote and no user" => [
                [
                ],
                '',
                ApplicationContext::getInstance()->getCurrentUser(),
                ''
            ],
        ];
    }

    /**
     * @dataProvider data
     * @test
     */
    public function testItFillTheTemplateWithoutDestinationLink($arguments, $expectedDestination, $expectedUser, $expectedDate)
    {
        $template = new Template(
            1,
            'Votre livraison ?? [quote:destination_name]',
            "
Bonjour [user:first_name],

Merci de nous avoir contact?? pour votre devis du [quote:date] de la livraison ?? [quote:destination_name].

Bien cordialement,

L'??quipe Dummy.com
");
        $templateManager = new TemplateManager();

        $message = $templateManager->getTemplateComputed(
            $template,
            $arguments
        );

        $this->assertEquals('Votre livraison ?? ' . $expectedDestination, $message->subject);
        $this->assertEquals("
Bonjour " . $expectedUser->firstname . ",

Merci de nous avoir contact?? pour votre devis du " . $expectedDate . " de la livraison ?? " . $expectedDestination . ".

Bien cordialement,

L'??quipe Dummy.com
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
            'Votre livraison ?? [quote:destination_name]',
            "
Bonjour [user:first_name],

Merci de nous avoir contact?? pour votre livraison ?? [quote:destination_name].

Vous pouvez voir les d??tails de votre devis ?? cette adresse: [quote:destination_link]

Bien cordialement,

L'??quipe Dummy.com
");
        $templateManager = new TemplateManager();

        $message = $templateManager->getTemplateComputed(
            $template,
            [
                'quote' => $quote
            ]
        );

        $this->assertEquals('Votre livraison ?? ' . $expectedDestination->countryName, $message->subject);
        $this->assertEquals("
Bonjour " . $expectedUser->firstname . ",

Merci de nous avoir contact?? pour votre livraison ?? " . $expectedDestination->countryName . ".

Vous pouvez voir les d??tails de votre devis ?? cette adresse: " . $usefulObject->url . '/' . $expectedDestination->countryName . '/quote/' . $quote->id . "

Bien cordialement,

L'??quipe Dummy.com
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
