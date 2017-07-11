<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class PubmedPlugin
 * @package Grav\Plugin
 */
class PubmedPlugin extends Plugin
{
    private $pubmed = null;

    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            return;
        }

        // Enable the main event we are interested in
        $this->enable([
            'onPageInitialized' => ['onPageInitialized', 0],
        ]);
    }

    public function onPageInitialized()
    {
        if ($this->isAdmin()) {
            $this->active = false;
            return;
        }

        $defaults = (array) $this->config->get('plugins.pubmed');
        /** @var Page $page */
        $page = $this->grav['page'];
        if (isset($page->header()->pubmed)) {
            $this->config->set('plugins.pubmed', array_merge($defaults, $page->header()->pubmed));
        }
        if ($this->config->get('plugins.pubmed.active')) {
            $this->enable([
                'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
                'onMarkdownInitialized' => ['onMarkdownInitialized', 0],
            ]);
        }
    }

    public function onTwigSiteVariables()
    {
        $this->grav['assets']
            ->add('theme://assets/pubmed.css');
    }

    /**
     * Do some work for this event, full details of events can be found
     * on the learn site: http://learn.getgrav.org/plugins/event-hooks
     *
     * @param Event $e
     */ 
    public function onMarkdownInitialized(Event $e)
    {
        require_once(__DIR__.'/classes/pubmed.class.php');
        $markdown = $e['markdown'];
        $markdown->addBlockType('[', 'PubMed', true, false);
        $this->pubmed = new PubMed();

        $markdown->blockPubMed = function($Line) {
            if (preg_match('/\[pubmed\>(short|long|summary)\:(\d+(\,\d+)*?)\]/', $Line['text'],  $matches)) {
                $cmd = $matches[1];
                $ids = $matches[2];

                //Fetch the data
                $this->pubmed->get_summary($this->grav['cache'], $ids);
                $recs = '';
                if ($cmd === 'summary') {
                    /*
                    $rec = array (
                        'element' => array (
                            'name' => 'pre',
                            'text' => $pubmed->response,
                        ),
                    );
                    array_push($recs, $rec);
                    */
                    $recs = '<pre>'.$this->pubmed->response.'</pre>';
                } else {
                    //Build individual records
                    $format = $this->config->get('plugins.pubmed.formats.' . $cmd);
                    foreach ($this->pubmed->extract as $record) {
                        if (array_key_exists('error', $record)) {
                            $text = '<strong>' . htmlspecialchars($record['error']) . '</strong>';
                        } else {
                            $text = $format;
                            $text = str_replace("\n", '<br />', $text);
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
                        /*
                        $rec = array (
                            'element' => array (
                                'name'=> 'p',
                                'text' => $text,
                            ),
                        );
                        array_push($recs, $rec);
                        */
                    }
                }

                //return '<div class="pubmed">'.$recs.'</div>';
                $Block = array (
                    'element' => array (
                        'name' => 'div',
                        'attributes' => array (
                            'class' => 'pubmed',
                        ),
                        'text' => $recs,
                    ),
                );

                return $Block;
            }
        };

        


    }
}
