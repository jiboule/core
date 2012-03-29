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

class Widget_Media extends \Fieldset_Field {

	protected $options = array(
		'mode' => 'image',
		'inputFileThumb' => array(),
	);

    public function __construct($name, $label = '', array $attributes = array(), array $rules = array(), \Fuel\Core\Fieldset $fieldset = null) {

		//$attributes['type']   = 'hidden';
		$attributes['class'] = (isset($attributes['class']) ? $attributes['class'] : '').' media';

		if (empty($attributes['id'])) {
			$attributes['id'] = uniqid('media_');
		}
		$this->options = \Arr::merge($this->options, array(
			'inputFileThumb' => array(
				'title' => __('Image from the media library'),
			)
		));
		if (!empty($attributes['widget_options'])) {
			$this->options = \Arr::merge($this->options, $attributes['widget_options']);
		}
		unset($attributes['widget_options']);

        parent::__construct($name, $label, $attributes, $rules, $fieldset);
    }

    /**
     * How to display the field
     * @return type
     */
    public function build() {
		$this->fieldset()->append($this->js_init());
		$media_id = $this->get_value();
		if (!empty($media_id)) {
			$media = \Nos\Model_Media_Media::find($media_id);
			if (!empty($media)) {
				$this->options['inputFileThumb']['file'] = $media->get_public_path_resized(64, 64);
			}
		}
		$this->set_attribute('data-media-options', htmlspecialchars(\Format::forge()->to_json($this->options)));
        return (string) parent::build();
    }

	public function js_init() {
		$id = $this->get_attribute('id');
		return <<<JS
<script type="text/javascript">
require(['jquery-nos'], function ($) {
	$(function() {
		$(':input#$id').each(function() {
			$.nos.media($(this), $(this).data('media-options'));
		});
	});
});
</script>
JS;
	}
}
