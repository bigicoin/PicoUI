<?php

/**
 * Pico UI - Card
 *
 * A UI plugin for displaying card-style boxes. Typically used on an index page,
 * or other types of menu pages.
 *
 * Note: The Card module is meant to work with Bootstrap's CSS classes for proper
 * positioning. By default, it assumes you are already loading Bootstrap CSS/JS
 * in your theme. If you are not, set config 'PicoUICard.loadBootstrap' to true,
 * and the plugin will load Bootstrap from the official CDN.
 *
 * @author  Bigi Lui
 * @link	https://github.com/bigicoin/PicoUI
 * @license http://opensource.org/licenses/MIT The MIT License
 * @version 1.0
 */
final class PicoUICard extends AbstractPicoPlugin
{
	/**
	 * This plugin is enabled by default?
	 *
	 * @see AbstractPicoPlugin::$enabled
	 * @var boolean
	 */
	protected $enabled = false;

	/**
	 * This plugin depends on ...
	 *
	 * @see AbstractPicoPlugin::$dependsOn
	 * @var string[]
	 */
	protected $dependsOn = array();

	/**
	 * Stored config
	 */
	protected $config = array();

	/**
	 * Triggered after Pico has read its configuration
	 *
	 * @see    Pico::getConfig()
	 * @param  array &$config array of config variables
	 * @return void
	 */
	public function onConfigLoaded(array &$config)
	{
		$this->config['loadBootstrap'] = false;
		$this->config['cardPosition'] = 'col-md-4 col-sm-6 col-xs-6';
		$this->config['cardTitle'] = '';
		$this->config['cardText'] = '';
		// load custom config if needed
		if (isset($config['PicoUICard.loadBootstrap'])) {
			$this->config['loadBootstrap'] = $config['PicoUICard.loadBootstrap'];
		}
		if (isset($config['PicoUICard.cssClass.cardPosition'])) {
			$this->config['cardPosition'] = $config['PicoUICard.cssClass.cardPosition'];
		}
		if (isset($config['PicoUICard.cssClass.cardTitle'])) {
			$this->config['cardTitle'] = $config['PicoUICard.cssClass.cardTitle'];
		}
		if (isset($config['PicoUICard.cssClass.cardText'])) {
			$this->config['cardText'] = $config['PicoUICard.cssClass.cardText'];
		}
	}

	/**
	 * Triggered after Pico has prepared the raw file contents for parsing
	 *
	 * @see	Pico::parseFileContent()
	 * @see	DummyPlugin::onContentParsed()
	 * @param  string &$content prepared file contents for parsing
	 * @return void
	 */
	public function onContentPrepared(&$content)
	{
		// we only do any processing at all if the page contains our tag, so we save time
		if (strpos($content, '[ui.card') !== false) {
			// below is our comprehensive regex to detect for the full [ui.card] tag,
			// which includes the tag and its attributes, and the [title] and [text] subtags.
			$config = $this->config;
			$content = preg_replace_callback(
				'/\[ui\.card((\s+[a-z]+\=[\'\"][^\'\"]*[\'\"])*)\s*\]\s*((\[[a-z]+\][^\[]*\[\/[a-z]+\]\s*)*)\[\/ui\.card\s*\]/',
				function ($matches) use ($config) {
					// $matches[1] contain the list of attributes
					// $matches[3] contain the list of subtags
					preg_match_all('/\s*([a-z]+)\=[\'\"]([^\'\"]*)[\'\"]/', $matches[1], $attributes);
					preg_match_all('/\[([a-z]+)\]([^\[]*)\[\/([a-z]+)\]\s*/', $matches[3], $subtags);
					// look for what we want from attributes and subtags
					$uicard = array('href' => '', 'img' => '', 'title' => '', 'text' => '');
					for ($i = 0; $i < count($attributes[0]); $i++) {
						$uicard[ $attributes[1][$i] ] = $attributes[2][$i];
					}
					for ($i = 0; $i < count($subtags[0]); $i++) {
						if ($subtags[1][$i] != $subtags[3][$i]) {
							continue; // closing subtag must match in order to be valid
						}
						$uicard[ $subtags[1][$i] ] = $subtags[2][$i];
					}
					$result = '<!--OPEN_PICO_UI_CARD--><div class="row">';
					$result .= '<a class="pico-ui-card-container_internal '.$config['cardPosition'].'" href="'.$uicard['href'].'">';
					$result .= '<div class="pico-ui-card-background_internal" style="background: transparent url(\''.$uicard['img'].'\') no-repeat scroll; background-size: cover;">';
					$result .= '<div class="pico-ui-card-gradient_internal">';
					$result .= '<div class="pico-ui-card-title_internal '.$config['cardTitle'].'">';
					$result .= $uicard['title'];
					$result .= '</div>';
					$result .= '</div>';
					$result .= '</div>';
					$result .= '<span class="pico-ui-card-text_internal '.$config['cardText'].'">';
					$result .= $uicard['text'];
					$result .= '</span>';
					$result .= '</a>';
					$result .= '</div><!--CLOSE_PICO_UI_CARD-->';
					return $result;
				},
				$content);
			// now properly combine continous ui cards into same row div
			$content = preg_replace('/\<\/div\>\<\!\-\-CLOSE_PICO_UI_CARD\-\-\>\s*\<\!\-\-OPEN_PICO_UI_CARD\-\-\>\<div class\=\"row\"\>/', "\n\n", $content);
			// now remove the extra comment tags that acted as our search string
			$content = preg_replace('/\<\!\-\-(OPEN|CLOSE)_PICO_UI_CARD\-\-\>/', '', $content);
		}
	}

	/**
	 * Triggered after Pico has rendered the page
	 *
	 * @param  string &$output contents which will be sent to the user
	 * @return void
	 */
	public function onPageRendered(&$output)
	{
		// regular pages
		$output = str_replace('</head>', ($this->buildExtraHeaders() . '</head>'), $output);
	}

	/**
	 * Add some extra header tags for our styling.
	 */
	private function buildExtraHeaders() {
		$headers = '';
		// if set to true, load from bootstrap cdn
		if ($this->config['loadBootstrap'] === true) {
			$headers .= PHP_EOL.'<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous" />';
			$headers .= PHP_EOL.'<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>';
		}
		// now set up card css classes
		$headers .= '
<style type="text/css">
a.pico-ui-card-container_internal { display: block; padding: 10px; }
.pico-ui-card-background_internal { width: 100%; padding-bottom: 56.25%; border-radius: 5px; box-shadow: 2px 2px 1px 0px rgba(0,0,0,0.2); position: relative; }
.pico-ui-card-gradient_internal { border-radius: 5px; position: absolute; top: 0; bottom: 0; left: 0; right: 0; background: linear-gradient(to bottom, rgba(0,0,0,0) 75%,rgba(0,0,0,0.8) 100%); }
.pico-ui-card-title_internal { position: absolute; bottom: 5px; left: 10px; font-size: 24px; color: #fff; }
@media screen and (max-width:320px) { .pico-ui-card-title_internal { font-size: 16px; } }
@media screen and (min-width:321px) and (max-width:639px) { .pico-ui-card-title_internal { font-size: 20px; } }
.pico-ui-card-text_internal { display: block; line-height: 100%; padding: 10px; font-size: 18px; color: #000; text-decoration: none; }
@media screen and (max-width:320px) { .pico-ui-card-text_internal { font-size: 14px; } }
@media screen and (min-width:321px) and (max-width:639px) { .pico-ui-card-text_internal { font-size: 16px; } }
</style>
';
		return $headers;
	}
}
