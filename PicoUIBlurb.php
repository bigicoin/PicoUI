<?php

/**
 * Pico UI - Blurb
 *
 * A UI plugin for displaying a short blurb with image on the left or right.
 * Typically used on an index page, or other types of menu pages.
 *
 * Note: The Blurb module is meant to work with Bootstrap's CSS classes for proper
 * positioning. By default, it assumes you are already loading Bootstrap CSS/JS
 * in your theme. If you are not, set config 'PicoUIBlurb.loadBootstrap' to true,
 * and the plugin will load Bootstrap from the official CDN.
 *
 * @author  Bigi Lui
 * @link	https://github.com/bigicoin/PicoUI
 * @license http://opensource.org/licenses/MIT The MIT License
 * @version 1.0
 */
final class PicoUIBlurb extends AbstractPicoPlugin
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
		$this->config['blurbTitle'] = '';
		$this->config['blurbText'] = '';
		$this->config['blurbMoreLink'] = '';
		// load custom config if needed
		if (isset($config['PicoUIBlurb.loadBootstrap'])) {
			$this->config['loadBootstrap'] = $config['PicoUIBlurb.loadBootstrap'];
		}
		if (isset($config['PicoUIBlurb.cssClass.blurbTitle'])) {
			$this->config['blurbTitle'] = $config['PicoUIBlurb.cssClass.blurbTitle'];
		}
		if (isset($config['PicoUIBlurb.cssClass.blurbText'])) {
			$this->config['blurbText'] = $config['PicoUIBlurb.cssClass.blurbText'];
		}
		if (isset($config['PicoUIBlurb.cssClass.blurbMoreLink'])) {
			$this->config['blurbMoreLink'] = $config['PicoUIBlurb.cssClass.blurbMoreLink'];
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
		if (strpos($content, '[ui.blurb') !== false) {
			// below is our comprehensive regex to detect for the full [ui.blurb] tag,
			// which includes the tag and its attributes, and the [title] and [text] subtags.
			$config = $this->config;
			$content = preg_replace_callback(
				'/\[ui\.blurb((\s+[a-z]+\=[\'\"][^\'\"]*[\'\"])*)\s*\]\s*((\[[a-z]+\][^\[]*\[\/[a-z]+\]\s*)*)\[\/ui\.blurb\s*\]/',
				function ($matches) use ($config) {
					// $matches[1] contain the list of attributes
					// $matches[3] contain the list of subtags
					preg_match_all('/\s*([a-z]+)\=[\'\"]([^\'\"]*)[\'\"]/', $matches[1], $attributes);
					preg_match_all('/\[([a-z]+)\]([^\[]*)\[\/([a-z]+)\]\s*/', $matches[3], $subtags);
					// look for what we want from attributes and subtags
					$uiblurb = array('href' => '', 'img' => '', 'imgpos' => '', 'title' => '', 'text' => '', 'more' => '');
					for ($i = 0; $i < count($attributes[0]); $i++) {
						$uiblurb[ $attributes[1][$i] ] = $attributes[2][$i];
					}
					for ($i = 0; $i < count($subtags[0]); $i++) {
						if ($subtags[1][$i] != $subtags[3][$i]) {
							continue; // closing subtag must match in order to be valid
						}
						$uiblurb[ $subtags[1][$i] ] = $subtags[2][$i];
					}
					$result = '<div class="row pico-ui-blurb-container_internal">';
					if ($uiblurb['imgpos'] != 'right') {
						$result .= '<div class="col-md-4 col-sm-4">';
						$result .= '<a href="'.$uiblurb['href'].'"><img src="'.$uiblurb['img'].'" class="pico-ui-blurb-image_internal" /></a>';
						$result .= '</div>';
					}
					$result .= '<div class="col-md-8 col-sm-8">';
					$result .= '<a href="'.$uiblurb['href'].'" class="pico-ui-blurb-title_internal '.$config['blurbTitle'].'">';
					$result .= $uiblurb['title'];
					$result .= '</a>';
					$result .= '<a href="'.$uiblurb['href'].'" class="pico-ui-blurb-text_internal '.$config['blurbText'].'">';
					$result .= $uiblurb['text'];
					$result .= '</a>';
					$result .= '<a href="'.$uiblurb['href'].'" class="pico-ui-blurb-more_internal '.$config['blurbMoreLink'].'">';
					$result .= $uiblurb['more'];
					$result .= '</a>';
					$result .= '</div>';
					if ($uiblurb['imgpos'] == 'right') {
						$result .= '<div class="col-md-4 col-sm-4">';
						$result .= '<a href="'.$uiblurb['href'].'"><img src="'.$uiblurb['img'].'" class="pico-ui-blurb-image_internal" /></a>';
						$result .= '</div>';
					}
					$result .= '</div>';
					return $result;
				},
				$content);
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
		// add css to end of <head>
		$output = str_replace('</head>', ($this->buildExtraHeaders() . '</head>'), $output);
		// add js to end of <body>
		$output = str_replace('</body>', ($this->buildExtraFooters() . '</body>'), $output);
	}

	/**
	 * Add some extra header tags for our styling.
	 */
	private function buildExtraHeaders() {
		$headers = '';
		// if set to true, load from bootstrap cdn
		if ($this->config['loadBootstrap'] === true) {
			$headers .= PHP_EOL.'<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous" />';
		}
		// now set up blurb css classes
		$headers .= '
<style type="text/css">
div.pico-ui-blurb-container_internal { margin: 10px 0; }
.pico-ui-blurb-image_internal { width: 100%; border-radius: 3px; box-shadow: 1px 1px 1px 0px rgba(0,0,0,0.2); }
a.pico-ui-blurb-title_internal { display: block; font-size: 24px; line-height: 100%; color: #000; text-decoration: none; margin: 0 0 10px; }
@media (max-width:320px) { a.pico-ui-blurb-title_internal { font-size: 16px; } }
@media (min-width:321px) and (max-width:639px) { a.pico-ui-blurb-title_internal { font-size: 20px; } }
a.pico-ui-blurb-text_internal { display: block; line-height: 100%; font-size: 18px; color: #000; text-decoration: none; margin: 0 0 10px; }
@media (max-width:320px) { a.pico-ui-blurb-text_internal { font-size: 14px; } }
@media (min-width:321px) and (max-width:639px) { a.pico-ui-blurb-text_internal { font-size: 16px; } }
a.pico-ui-blurb-more_internal { display: block; line-height: 100%; font-size: 18px; }
@media (max-width:320px) { a.pico-ui-blurb-more_internal { font-size: 14px; } }
@media (min-width:321px) and (max-width:639px) { a.pico-ui-blurb-more_internal { font-size: 16px; } }
</style>
';
		return $headers;
	}

	/**
	 * Add some extra footer tags we need.
	 */
	private function buildExtraFooters() {
		$footers = '';
		// if set to true, load from bootstrap cdn
		if ($this->config['loadBootstrap'] === true) {
			$footers .= PHP_EOL.'<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>'.PHP_EOL;
		}
		return $footers;
	}
}