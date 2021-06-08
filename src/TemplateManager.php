<?php

class TemplateManager
{
    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $replaced = clone($tpl);
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    private function computeText($text, array $data)
    {
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();

        $quote = (isset($data['quote']) and $data['quote'] instanceof Quote) ? $data['quote'] : null;

        if ($quote)
        {
            $_quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
            $usefulObject = SiteRepository::getInstance()->getById($quote->siteId);
            $destination = DestinationRepository::getInstance()->getById($quote->destinationId);

            $text = $this->replaceTag($text, '[quote:summary_html]', Quote::renderHtml($_quoteFromRepository));
            $text = $this->replaceTag($text, '[quote:summary]', Quote::renderText($_quoteFromRepository));

            $text = $this->replaceTag($text, '[quote:destination_name]', $destination->countryName);
        }

        $destinationLink = isset($destination) ? $usefulObject->url . '/' . $destination->countryName . '/quote/' . $_quoteFromRepository->id : '';
        $text = $this->replaceTag($text, '[quote:destination_link]', $destinationLink);

        /*
         * USER
         * [user:*]
         */
        $_user  = (isset($data['user'])  and ($data['user']  instanceof User))  ? $data['user']  : $APPLICATION_CONTEXT->getCurrentUser();
        if($_user) {
            $text = $this->replaceTag($text, '[user:first_name]', ucfirst(mb_strtolower($_user->firstname)));
        }

        return $text;
    }

    /**
     *
     * @param string $text
     * @param string $tag
     * @param string $value
     * @param string $defaultValue
     * @return string
     */
    private function replaceTag($text, $tag, $value, $defaultValue = '')
    {
        if (strpos($text, $tag) === false) {
            // no need to replace
            return $text;
        }

        return str_replace($tag, $value ?: $defaultValue, $text);
    }
}
