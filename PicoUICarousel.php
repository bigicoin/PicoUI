<?php

/**
 * Pico UI - Carousel
 *
 * A UI plugin for displaying a carousel that contains slides with an image, title, link.
 * Typically used on an index page, or other types of menu pages.
 *
 * Note: The Carousel plugin uses another open source JS library called Slick to
 * display a carousel. This plugin simply wraps a Pico plugin around it and provides
 * an easy way to create one from Pico markdown pages.
 *
 * Because Slick itself is a jquery plugin, this Pico UI plugin also requires jquery.
 * Since jquery is commonly used, you can configure Pico UI - Carousel to load jquery
 * from a CDN, or assume it is already loaded. (if your theme template already uses it)
 *
 * The plugin can load Slick from a CDN directly, or load it locally from a location
 * you specify.
 *
 * @author  Bigi Lui
 * @link	https://github.com/bigicoin/PicoUI
 * @license http://opensource.org/licenses/MIT The MIT License
 * @version 1.0
 */
final class PicoUICarousel extends AbstractPicoPlugin
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
		$this->config['loadJquery'] = false;
		$this->config['jqueryUrl'] = '//cdn.jsdelivr.net/jquery/3.2.1/jquery.min.js';
		$this->config['slickPath'] = '//cdn.jsdelivr.net/jquery.slick/1.6.0';
		$this->config['carouselRatio'] = '2.35:1';
		$this->config['carouselText'] = '';
		// load custom config if needed
		if (isset($config['PicoUICarousel.loadJquery'])) {
			$this->config['loadJquery'] = $config['PicoUICarousel.loadJquery'];
		}
		if (isset($config['PicoUICarousel.jqueryUrl'])) {
			$this->config['jqueryUrl'] = $config['PicoUICarousel.jqueryUrl'];
		}
		if (isset($config['PicoUICarousel.slickPath'])) {
			$this->config['slickPath'] = $config['PicoUICarousel.slickPath'];
		}
		if (isset($config['PicoUICarousel.carouselRatio'])) {
			$this->config['carouselRatio'] = $config['PicoUICarousel.carouselRatio'];
		}
		if (isset($config['PicoUICarousel.cssClass.carouselText'])) {
			$this->config['carouselText'] = $config['PicoUICarousel.cssClass.carouselText'];
		}
		// some postprocessing of config
		list($cWidth, $cHeight) = explode(':', $this->config['carouselRatio']);
		$this->config['heightMultiplier'] = max(0.01, min(floatval($cHeight) / floatval($cWidth), 10)) * 100;
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
		if (strpos($content, '[ui.carousel') !== false) {
			// below is our comprehensive regex to detect for the full [ui.card] tag,
			// which includes the tag and its attributes, and the [title] and [text] subtags.
			$config = $this->config;
			$content = preg_replace_callback(
				'/\[ui\.carousel((\s+[a-z]+\=[\'\"][^\'\"]*[\'\"])*)\s*\]\s*((\[slide((\s+[a-z]+\=[\'\"][^\'\"]*[\'\"])*)\s*\][^\[]*\[\/slide\]\s*)*)\[\/ui\.carousel\s*\]/',
				function ($matches) use ($config) {
					// $matches[1] contain the list of attributes
					// $matches[3] contain the list of subtags
					preg_match_all('/\s*([a-z]+)\=[\'\"]([^\'\"]*)[\'\"]/', $matches[1], $attributes);
					// look for what we want from parent attributes
					$carousel = array('ratio' => '');
					for ($i = 0; $i < count($attributes[0]); $i++) {
						$carousel[ $attributes[1][$i] ] = $attributes[2][$i];
					}
					if (!empty($carousel['ratio'])) {
						list($cWidth, $cHeight) = explode(':', $carousel['ratio']);
						$ratio = 'padding-bottom: '.(max(0.01, min(floatval($cHeight) / floatval($cWidth), 10)) * 100).'%;';
						$spacer = 'height: '.(max(0.01, min(floatval($cHeight) / floatval($cWidth), 10)) * 100).'vw;';
					} else {
						// default
						$ratio = '';
						$spacer = '';
					}
					preg_match_all('/\[slide((\s+[a-z]+\=[\'\"][^\'\"]*[\'\"])*)\s*\]([^\[]*)\[\/slide\]\s*/', $matches[3], $rawSlides);
					$slides = array();
					for ($i = 0; $i < count($rawSlides[0]); $i++) {
						// $rawSlides[1][*] contain the list of attributes
						// $rawSlides[3][*] contain the title text
						$slides[$i] = array();
						$slides[$i]['text'] = $rawSlides[3][$i];
						preg_match_all('/\s*([a-z]+)\=[\'\"]([^\'\"]*)[\'\"]/', $rawSlides[1][$i], $slideAttributes);
						for ($k = 0; $k < count($slideAttributes[0]); $k++) {
							$slides[$i][ $slideAttributes[1][$k] ] = $slideAttributes[2][$k];
						}
					}

					$result = '<div class="pico-ui-carousel-container_internal"><div class="pico-ui-carousel_internal">';
					for ($i = 0; $i < count($slides); $i++) {
						$result .= '<div class="pico-ui-carousel-slide_internal" style="background: transparent url(\''.$slides[$i]['img'].'\') no-repeat scroll; background-size: cover;">';
						$result .= '<a href="'.$slides[$i]['href'].'" class="pico-ui-carousel-slide-content_internal" style="'.$ratio.'">';
						$result .= '<div class="pico-ui-carousel-slide-shadow_bottom_internal"><div class="pico-ui-carousel-slide-shadow_sides_internal">';
						$result .= '<div class="pico-ui-carousel-slide-text_internal '.$this->config['carouselText'].'">';
						$result .= $slides[$i]['text'];
						$result .= '</div>';
						$result .= '</div></div>';
						$result .= '</a>';
						$result .= '</div>';
					}
					$result .= '</div></div>';
					// Pico's MD parsing wants this tag on the next line for some reaasons.
					$result .= PHP_EOL.'<div class="pico-ui-carousel-spacer_internal" style="'.$spacer.'"></div>';
					return PHP_EOL.$result.PHP_EOL;
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
		$headers = PHP_EOL.'<link rel="stylesheet" href="'.$this->config['slickPath'].'/slick.css" />';
		$headers .= PHP_EOL.'<link rel="stylesheet" href="'.$this->config['slickPath'].'/slick-theme.css" />';
		// now set up card css classes
		$headers .= '
<style type="text/css">
.pico-ui-carousel-container_internal{position: absolute;width: 100%;left: 0}.pico-ui-carousel-spacer_internal{margin: 10px 0 30px;height: '.$this->config['heightMultiplier'].'vw}.pico-ui-carousel_internal{margin: 10px auto;width: 100%}.pico-ui-carousel-slide_internal{width: 100%;box-shadow: 2px 2px 1px 0px rgba(0, 0, 0, 0.2);background-color: #ccc}.pico-ui-carousel-slide-content_internal{display: block;position: relative;width: 100%;padding-bottom: '.$this->config['heightMultiplier'].'%}.pico-ui-carousel-slide-shadow_bottom_internal{position: absolute;top: 0;bottom: 0;left: 0;right: 0;background: linear-gradient(to bottom, rgba(0,0,0,0) 75%,rgba(0,0,0,0.8) 100%)}.pico-ui-carousel-slide-shadow_sides_internal{position: absolute;top: 0;bottom: 0;left: 0;right: 0;background: linear-gradient(to right, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 15%, rgba(0,0,0,0) 85%, rgba(0,0,0,0.8) 100%)}.pico-ui-carousel-slide-text_internal{position: absolute;bottom: 0;left: 0;margin: 50px;font-size: 48px;color: #fff;line-height: 100%;text-decoration: none}@media (max-width:415px){.pico-ui-carousel-slide-text_internal{font-size: 24px;margin: 5px}}@media (min-width:416px) and (max-width:639px){.pico-ui-carousel-slide-text_internal{font-size: 32px;margin: 20px}}@media (min-width:640px) and (max-width:900px){.pico-ui-carousel-slide-text_internal{font-size: 40px;margin: 30px}}.pico-ui-carousel_internal button.slick-prev{left: 10px;z-index: 99;width: 32px;height: 32px}@media (max-width:415px){.pico-ui-carousel_internal button.slick-prev{display: none}}@media (min-width:416px) and (max-width:639px){.pico-ui-carousel_internal button.slick-prev{left: 5px;width: 16px;height: 16px}}.pico-ui-carousel_internal button.slick-next{right: 10px;z-index: 99;width: 32px;height: 32px}@media (max-width:415px){.pico-ui-carousel_internal button.slick-next{display: none}}@media (min-width:416px) and (max-width:639px){.pico-ui-carousel_internal button.slick-next{right: 5px;width: 16px;height: 16px}}.pico-ui-carousel_internal button.slick-prev:before, .pico-ui-carousel_internal button.slick-next:before{font-size: 32px}@media (max-width:415px){.pico-ui-carousel_internal button.slick-prev:before, .pico-ui-carousel_internal button.slick-next:before{font-size: 0px}}@media (min-width:416px) and (max-width:639px){.pico-ui-carousel_internal button.slick-prev:before, .pico-ui-carousel_internal button.slick-next:before{font-size: 16px}}.pico-ui-carousel_internal ul.slick-dots{bottom: 10px}@media (max-width:415px){.pico-ui-carousel_internal ul.slick-dots{bottom: -10px}}@media (min-width:416px) and (max-width:639px){.pico-ui-carousel_internal ul.slick-dots{bottom: 0px}}.pico-ui-carousel_internal ul.slick-dots li button:before{color: #bbb}.pico-ui-carousel_internal ul.slick-dots li.slick-active button:before{color: #fff}
</style>
';
		return $headers;
	}

	/**
	 * Add some extra footer tags we need.
	 */
	private function buildExtraFooters() {
		$footers = '';
		// if set to true, load from jquery cdn
		if ($this->config['loadJquery'] === true) {
			$footers .= PHP_EOL.'<script src="'.$this->config['jqueryUrl'].'"></script>';
		}
		$footers .= PHP_EOL.'<script src="'.$this->config['slickPath'].'/slick.min.js"></script>';
		$footers .= '
<script type="text/javascript">
$(document).ready(function(){$(".pico-ui-carousel_internal").slick({dots: true});});
</script>
';
		return $footers;
	}
}
