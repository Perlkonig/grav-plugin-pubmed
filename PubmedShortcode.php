<?php

namespace Grav\Plugin\Shortcodes;

use Thunder\Shortcode\Shortcode\ShortcodeInterface;
use Grav\Common\Utils;
use Symfony\Component\Yaml\Yaml;
use RocketTheme\Toolbox\File\File;
// use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;
use League\Csv\Reader;

require_once(__DIR__.'/classes/pubmed.class.php');

class PubmedShortcode extends Shortcode
{
    protected $outerEscape = null;

    public function init()
    {
        $this->shortcode->getHandlers()->add('pubmed', array($this, 'process'));
    }

    public function process(ShortcodeInterface $sc) {
        $type = $sc->getParameter('type', null);
        $ids = $sc->getParameter('ids', null);

        $pubmed = new \Grav\Plugin\PubMed();
        if ($ids !== null) {
            $pubmed->get_summary($this->grav['cache'], $ids);
            $recs = '';
            if ($type === 'summary') {
                $recs = '<pre>'.json_encode($pubmed->summary, JSON_PRETTY_PRINT).'</pre>';
            } else {
                //Build individual records
                $format = $this->config->get('plugins.pubmed.formats.' . $type);
                foreach ($pubmed->extract as $record) {
                    if (array_key_exists('error', $record)) {
                        $text = '<strong>' . htmlspecialchars($record['error']) . '</strong>';
                    } else {
                        $text = $format;
                        foreach (['uid', 'title', 'authors_short', 'authors_long', 'journal', 'volume', 'pages', 'date'] as $field) {
                            $replacement = '';
                            if ($field === 'authors_short') {
                                $authorstr = htmlspecialchars($record['authors'][0]);
                                if (count($record['authors']) > 1) {
                                    $authorstr = $authorstr . ' <span class="etal">et al.</span>';
                                }
                                $replacement = $authorstr;
                            } elseif ($field === 'authors_long') {
                                $sep = $this->config->get('plugins.pubmed.formats.author_sep', ', ');
                                $replacement = htmlspecialchars(implode($sep, $record['authors']));
                            } else {
                                $replacement = htmlspecialchars($record[$field]);
                            }
                            $text = str_replace(
                                '[' . $field . ']', 
                                '<span class="' . $field . '">' . $replacement . '</span>', 
                                $text);
                            $text = str_replace(
                                '[' . $field . ' raw]', 
                                strip_tags($replacement),
                                $text);
                        }
                    }
                    $recs = $recs . '<p>' . $text . '</p>' . "\n";
                }
            }
        }
        return $recs;
    }
}
