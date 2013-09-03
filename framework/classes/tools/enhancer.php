<?php
/**
 * NOVIUS OS - Web OS for digital communication
 *
 * @copyright  2011 Novius
 * @license    GNU Affero General Public License v3 or (at your option) any later version
 *             http://www.gnu.org/licenses/agpl-3.0.html
 * @link http://www.novius-os.org
 */

namespace Nos;

class Tools_Enhancer
{
    /**
     * Get an array of URLs matching to enhancer name, an item and additional parameters.
     *
     * @param string $enhancer_name The enhancer name
     * @param Orm\Model $item A Model item.
     * @param array $params Array of additional parameters for build URL.
     * @return array Array of URLs matching to enhancer name, an item and additional parameters.
     * @throws \RuntimeException If the enhancer is not a URL enhancer
     */
    public static function url_item($enhancer_name, $item, $params = array())
    {
        $params['item'] = $item;
        return static::_urls($enhancer_name, $params, true);
    }


    /**
     * Get an array of URLs matching to enhancer name and additional parameters.
     *
     * @param string $enhancer_name The enhancer name
     * @param array $params Array of additional parameters for build URL.
     * @return array Array of URLs matching to enhancer name and additional params.
     * @throws \RuntimeException If the enhancer is not a URL enhancer
     */
    public static function urls($enhancer_name, $params = array())
    {
        return static::_urls($enhancer_name, $params);
    }

    protected static function _urls($enhancer_name, $params, $key_has_url_enhanced = false)
    {
        $urlEnhanced = call_user_func(static::getEnhancerCallback($enhancer_name, 'getUrlEnhanced'), $params);

        $urlPath = \Arr::get($params, 'urlPath', false);
        $preview = \Arr::get($params, 'preview', false);

        // Now fetch all the possible URLS
        $urls = array();
        if ($urlPath === false) {

            $item = \Arr::get($params, 'item', false);
            $context = \Arr::get($params, 'context', false);
            if ($item && !$context) {
                try {
                    $context = $item->get_context();
                } catch (\Exception $e) {
                }
            }

            $urls = \Arr::merge($urls, static::getAllUrlsPrefixes($enhancer_name, $context, $urlEnhanced, $preview, $key_has_url_enhanced));
        } else {
            $urls[] = $urlPath.$urlEnhanced.($preview ? '?_preview=1' : '');
        }

        return $urls;
    }

    protected static function getEnhancerCallback($enhancer_name, $function_name)
    {
        // Check if this enhancer exists
        $enhancer = Config_Data::get('enhancers.'.$enhancer_name, array());
        if (empty($enhancer)) {
            logger(\Fuel::L_WARNING, 'The urlEnhancer "'.$enhancer_name.'" don\'t exist anymore.', __METHOD__);
            return array();
        }
        if (empty($enhancer['urlEnhancer'])) {
            throw new \RuntimeException('This enhancer "'.$enhancer_name.'" is not an urlEnhancer.');
        }

        $urlEnhancer = $enhancer['urlEnhancer'];

        // Calculate the classname of the enhancer's Controller
        // eg. noviusos_blog would match to Nos\Blog\Controller_Front
        $parts = explode('/', $urlEnhancer);
        $application_name = $parts[0];
        // Replace the application name with 'controller'
        $parts[0] = 'controller';
        // Remove the action
        array_pop($parts);
        // We're left with the fuel Controller classname!
        $controller_name = implode('_', $parts);
        $controller_name = \Inflector::words_to_upper($controller_name);

        // Check if the application exists
        $namespace = Config_Data::get('app_namespaces.'.$application_name, '');
        if (empty($namespace)) {
            return array();
        }

        return method_exists($namespace.'\\'.$controller_name, $function_name) ? array($namespace.'\\'.$controller_name, $function_name) : null;
    }

    protected static function getAllUrlsPrefixes($enhancer_name, $context, $urlEnhanced, $preview, $key_has_url_enhanced = false)
    {
        // Check if any page contains this enhancer
        $page_enhanced = Config_Data::get('page_enhanced.'.$enhancer_name, array());
        if (empty($page_enhanced)) {
            return array();
        }
        $urls = array();
        // This files contains all the urlPath for the pages containing an URL enhancer
        $url_enhanced = Config_Data::get('url_enhanced', array());
        foreach ($page_enhanced as $page_id => $page_params) {
            if (is_array($page_params['published'])) {
                $now = \Date::forge()->format('mysql');
                $published = (empty($page_params['published']['start']) ||
                    $page_params['published']['start'] < $now) &&
                    (empty($page_params['published']['end']) ||
                        $now < $page_params['published']['end']);
            } else {
                $published = $page_params['published'] == true;
            }
            if ((!$context || $page_params['context'] == $context) && ($preview || $published)) {
                $url_params = \Arr::get($url_enhanced, $page_id, false);
                if ($url_params) {
                    if (empty($urlEnhanced) && !empty($url_params['url'])) {
                        $url_params['url'] = substr($url_params['url'], 0, -1).'.html';
                    }
                    $urls[$page_id.($key_has_url_enhanced ? '::'.$urlEnhanced : '')] =
                        Tools_Url::context($url_params['context']).
                            $url_params['url'].$urlEnhanced.($preview ? '?_preview=1' : '');
                }
            }
        }

        return $urls;
    }

    /**
     * Get first URL matching to enhancer name and additional parameters.
     *
     * @param string $enhancer_name The enhancer name
     * @param array $params Array of additional parameters for build URL.
     * @return string URL matching to enhancer name and additional params, null if no match.
     * @throws \RuntimeException If the enhancer is not a URL enhancer
     */
    public static function url($enhancer_name, $params = array())
    {
        $urls = static::urls($enhancer_name, $params);
        return reset($urls) ?: null;
    }

    public static function cachedUrls($enhancer_name, $item)
    {
        $urls = static::url_item($enhancer_name, $item);
        $cb = static::getEnhancerCallback($enhancer_name, 'getCachedUrls');
        if ($cb !== null) {
            $cachedUrls = call_user_func($cb, $item);
            $context = $item->get_context();
            foreach ($cachedUrls as $url) {
                $urls = array_merge($urls, static::getAllUrlsPrefixes($enhancer_name, $context, $url, false, false));
            }
        }
        return $urls;
    }
}
