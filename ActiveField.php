<?php
/**
 * This file is part of the yii2-uikit project.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 *
 * @copyright yii2-uikit (c) 2018
 * @author Eugene Zakirov (worstinme) <box@flyleaf.su>
 */

namespace worstinme\uikit;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class ActiveField extends \yii\widgets\ActiveField
{
    /**
     * {@inheritdoc}
     */
    public $options = [];

    /**
     * {@inheritdoc}
     */
    public $template = "{label}\n{beginWrapper}\n{input}\n{error}\n{hint}\n{hidden}\n{endWrapper}";

    /**
     * @var string css class suffix used for fields class when form is in grid mode
     */
    public $width;

    /**
     * @var array options for the wrapper tag, used in the `{beginWrapper}` placeholder
     */
    public $wrapperOptions = [];

    /**
     * @var array options for the icon tag, used in the {icon} && {input} placeholders
     */
    public $iconOptions = [];

    /**
     * @var array options for the container of {icon} && {input}  placeholders, used when icon is set
     */
    public $iconWrapperOptions = [];

    /**
     * @var array options for info tag used for {info} placeholder
     */
    public $infoOptions = [];

    public $errorOptions = ['class' => 'help-block'];

    /**
     * {@inheritdoc}
     */
    private $_skipLabelFor = false;

    /**
     * {@inheritdoc}
     */
    public function __construct($config = [])
    {
        $layoutConfig = $this->createLayoutConfig($config);
        $config = ArrayHelper::merge($layoutConfig, $config);
        parent::__construct($config);
    }

    /**
     * @param array $instanceConfig the configuration passed to this instance's constructor
     * @return array the layout specific default configuration for this instance
     */
    protected function createLayoutConfig($instanceConfig)
    {
        $config = [
            'options'=>[
                //
            ],
            'labelOptions' => [
                'class' => 'uk-form-label'
            ],
            'wrapperOptions' => [
                'class' => 'uk-form-controls',
            ],
            'hintOptions' => [
                'class' => 'uk-form-controls-text',
            ],
            'iconOptions'=>[
                'flip' => false, 'uikit' => true,
            ],
            'infoOptions'=>[
                'class' => 'uk-margin-small-left',
                'tag' => 'span',
                'uk-icon' => 'question',
            ],
        ];

        if (isset($instanceConfig['options'])) {
            $config['options'] = ArrayHelper::merge($config['options'], $instanceConfig['options']);
        }

        if ($instanceConfig['form']->grid) {
            Html::addCssClass($config['options'], 'uk-grid-margin');
            if (isset($instanceConfig['width'])) {
                $classes = explode(" ", $instanceConfig['width']);
                foreach ($classes as $class) {
                    Html::addCssClass($config['options'], 'uk-width-' . $class);
                }
            }
        } else {
            Html::addCssClass($config['options'], 'uk-margin');
            if (isset($instanceConfig['width'])) {
                $classes = explode(" ", $instanceConfig['width']);
                foreach ($classes as $class) {
                    Html::addCssClass($config['inputOptions'], 'uk-width-' . $class);
                }
            }
        }

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    public function render($content = null)
    {
        if ($content === null) {

            if (!isset($this->parts['{input}'])) {
                $this->textInput();
            }
            if (!isset($this->parts['{error}'])) {
                $this->error();
            }
            if (!isset($this->parts['{hint}'])) {
                $this->hint(null);
            }

            if (strpos($this->template,'{label}') !== false) {
                if (strpos($this->template,'{info}') === false) {
                    $this->template = str_replace('{label}','{beginLabel}{labelTitle}{info}{endLabel}', $this->template);
                } else {
                    $this->template = str_replace('{label}','{beginLabel}{labelTitle}{endLabel}', $this->template);
                }
            }

            if (!isset($this->parts['{beginWrapper}'])) {
                $options = $this->wrapperOptions;
                $tag = ArrayHelper::remove($options, 'tag', 'div');
                $this->parts['{beginWrapper}'] = Html::beginTag($tag, $options);
                $this->parts['{endWrapper}'] = Html::endTag($tag);
            }

            if (!isset($this->parts['{hidden}'])) {
                $this->parts['{hidden}'] = '';
            }

            if (!empty($this->parts['{icon}'])) {
                if (strpos($this->template, '{icon}') === false) {
                    Html::addCssClass($this->iconWrapperOptions,'uk-position-relative');
                    $this->template = str_replace("{input}", Html::tag('div', '{icon}{input}', $this->iconWrapperOptions), $this->template);
                }
            }

            if (!isset($this->parts['{info}'])) {
                $this->parts['{info}'] = '';
            }

            if (!isset($this->parts['{beginLabel}'])) {
                $this->label();
            }

            $content = strtr($this->template, $this->parts);

        } elseif (!is_string($content)) {
            $content = call_user_func($content, $this);
        }

        return $this->begin() . "\n" . $content . "\n" . $this->end();
    }

    /**
     * {@inheritdoc}
     */
    public function label($label = null, $options = [])
    {
        if ($label === false) {
            $this->parts['{label}'] = '';
            $this->parts['{beginLabel}'] = '';
            $this->parts['{endLabel}'] = '';
            $this->parts['{labelTitle}'] = '';
            return $this;
        }

        $options = array_merge($this->labelOptions, $options);

        if ($label === null) {
            $attribute = Html::getAttributeName($this->attribute);
            $label = Html::encode($this->model->getAttributeLabel($attribute));
        }

        if ($this->_skipLabelFor) {
            $options['for'] = null;
        }

        $this->parts['{beginLabel}'] = Html::beginTag('label', $options);
        $this->parts['{endLabel}'] = Html::endTag('label');
        $this->parts['{labelTitle}'] = $label;

        return $this;
    }

    /**
     * Generates an icon tag for [[attribute]].
     * @param null|string|false $icon the icon to use.
     * If `false`, the generated field will not contain the icon part.
     * Note that this will NOT be [[Html::encode()|encoded]].
     * @param null|array $options the tag options in terms of name-value pairs. It will be merged with [[iconOptions]].
     * The options will be rendered as the attributes of the resulting tag. The values will be HTML-encoded
     * @return $this the field object itself.
     */
    public function icon($icon, $options = [])
    {
        if ($icon === false) {
            $this->parts['{icon}'] = '';
            return $this;
        }

        $options = array_merge($this->iconOptions, $options);

        if ($icon !== null) {

            $uikit = ArrayHelper::remove($options, 'uikit', true);
            $tag = ArrayHelper::remove($options, 'tag', 'span');

            Html::addCssClass($options, 'uk-form-icon');
            if (ArrayHelper::remove($options, 'flip', false)) {
                Html::addCssClass($options, 'uk-form-icon-flip');
            }

            if ($uikit) {
                IconAsset::register($this->form->view);

                $options['uk-icon'] = $icon;
                $this->parts['{icon}'] = Html::tag($tag, '', $options);

            } else {
                $this->parts['{icon}'] = Html::tag($tag, $icon, $options);
            }
        }

        return $this;
    }

    /**
     * Generates an info tag in the label for [[attribute]].
     * @param string $info the info to use.
     * @param null|array $options the tag options in terms of name-value pairs. It will be merged with [[infoOptions]].
     * The options will be rendered as the attributes of the resulting tag. The values will be HTML-encoded
     * @return $this the field object itself.
     */
    public function info($info, $options = [])
    {
        $options = array_merge($this->infoOptions, $options);

        if (isset($options['uk-icon'])) {
            IconAsset::register($this->form->view);
        }
        $tag = ArrayHelper::remove($options, 'tag', 'span');
        $options['uk-tooltip'] = 'title: ' . Html::encode($info);
        $this->parts['{info}'] = Html::tag($tag, '', $options);

        return $this;
    }

    /**
     * Renders something to the {hidden} position of template
     * @param string $hidden
     */
    public function hidden($hidden) {
        if (!empty($hidden)) {
            $this->parts['{hidden}'] = $hidden;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function textInput($options = [])
    {
        Html::addCssClass($this->inputOptions,'uk-input');
        return parent::textInput($options);
    }
    /**
     * {@inheritdoc}
     */
    public function passwordInput($options = [])
    {
        Html::addCssClass($this->inputOptions,'uk-input');
        return parent::passwordInput($options);
    }

    /**
     * {@inheritdoc}
     */
    public function textarea($options = [])
    {
        Html::addCssClass($this->inputOptions,'uk-textarea');
        return parent::textarea($options);
    }

    /**
     * {@inheritdoc}
     */
    public function checkbox($options = [], $enclosedByLabel = true)
    {
        Html::addCssClass($options,'uk-checkbox');
        return parent::checkbox($options, $enclosedByLabel);
    }

    /**
     * {@inheritdoc}
     */
    public function dropDownList($items, $options = [])
    {
        Html::addCssClass($this->inputOptions,'uk-select');
        return parent::dropDownList($items, $options);
    }

}
