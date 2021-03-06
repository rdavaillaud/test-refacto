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
        $applicationContext = ApplicationContext::getInstance();
        $dataSource = array();

        // Quote [quote:*]
        $quote = (isset($data['quote']) and $data['quote'] instanceof Quote) ? $data['quote'] : null;
        if ($quote) {
            $dataSource += $quote->toTemplateDataSource('quote');
        }

        // User [user:*]
        $_user = (isset($data['user']) and ($data['user'] instanceof User)) ? $data['user'] : $applicationContext->getCurrentUser(
        );
        if ($_user) {
            $dataSource += $_user->toTemplateDataSource('user');
        }

        foreach ($dataSource as $tag => $value) {
            $text = $this->replaceTag($text, $tag, $value);
        }
        $text = $this->cleanTags($text, array('quote', 'user'));

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

    private function cleanTags($text, $tagPrefixes)
    {
        $text = mb_ereg_replace('\[(' . implode('|', $tagPrefixes) . '):[^]]+\]', '', $text, 'j');
        if ($text === false) {
            throw new Exception('Error on cleaning tags');
        }

        return $text;
    }
}
