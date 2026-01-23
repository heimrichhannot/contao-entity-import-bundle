<?php

namespace HeimrichHannot\EntityImportBundle\Widget;

use Contao\Widget;

class HyperlinkWidget extends Widget
{
    protected $blnSubmitInput = false;

    public function generate(): string
    {
        $url = '#';

        if (isset($this->arrConfiguration['url'])) {
            if (is_string($this->arrConfiguration['url'])) {
                $url = $this->arrConfiguration['url'];
            } elseif (is_array($this->arrConfiguration['url'])) {
                $callback = $this->arrConfiguration['url'];
                $url = $callback[0]::{$callback[1]}($this->objDca, $this);
            } elseif (is_callable($this->arrConfiguration['url'])) {
                $url = ($this->arrConfiguration['url'])($this->objDca, $this);
            }
        }

        $text = $this->arrConfiguration['text'] ?? 'Link';
        $linkClass = $this->arrConfiguration['linkClass'] ?? '';
        $target = $this->arrConfiguration['target'] ?? '';

        $html = '<a href="' . htmlspecialchars($url) . '"';

        if ($target) {
            $html .= ' target="' . htmlspecialchars($target) . '"';
        }

        if ($linkClass) {
            $html .= ' class="' . htmlspecialchars($linkClass) . '"';
        }

        $html .= '>' . htmlspecialchars($text) . '</a>';

        return $html;
    }
}
