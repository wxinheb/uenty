<?php


namespace aabc\bootstrap;

use aabc\base\InvalidConfigException;
use aabc\helpers\ArrayHelper;


class Carousel extends Widget
{
    
    public $controls = ['&lsaquo;', '&rsaquo;'];
    
    public $showIndicators = true;
    
    public $items = [];


    
    public function init()
    {
        parent::init();
        Html::addCssClass($this->options, ['widget' => 'carousel']);
    }

    
    public function run()
    {
        $this->registerPlugin('carousel');
        return implode("\n", [
            Html::beginTag('div', $this->options),
            $this->renderIndicators(),
            $this->renderItems(),
            $this->renderControls(),
            Html::endTag('div')
        ]) . "\n";
    }

    
    public function renderIndicators()
    {
        if ($this->showIndicators === false) {
            return '';
        }
        $indicators = [];
        for ($i = 0, $count = count($this->items); $i < $count; $i++) {
            $options = ['data-target' => '#' . $this->options['id'], 'data-slide-to' => $i];
            if ($i === 0) {
                Html::addCssClass($options, 'active');
            }
            $indicators[] = Html::tag('li', '', $options);
        }

        return Html::tag('ol', implode("\n", $indicators), ['class' => 'carousel-indicators']);
    }

    
    public function renderItems()
    {
        $items = [];
        for ($i = 0, $count = count($this->items); $i < $count; $i++) {
            $items[] = $this->renderItem($this->items[$i], $i);
        }

        return Html::tag('div', implode("\n", $items), ['class' => 'carousel-inner']);
    }

    
    public function renderItem($item, $index)
    {
        if (is_string($item)) {
            $content = $item;
            $caption = null;
            $options = [];
        } elseif (isset($item['content'])) {
            $content = $item['content'];
            $caption = ArrayHelper::getValue($item, 'caption');
            if ($caption !== null) {
                $caption = Html::tag('div', $caption, ['class' => 'carousel-caption']);
            }
            $options = ArrayHelper::getValue($item, 'options', []);
        } else {
            throw new InvalidConfigException('The "content" option is required.');
        }

        Html::addCssClass($options, ['widget' => 'item']);
        if ($index === 0) {
            Html::addCssClass($options, 'active');
        }

        return Html::tag('div', $content . "\n" . $caption, $options);
    }

    
    public function renderControls()
    {
        if (isset($this->controls[0], $this->controls[1])) {
            return Html::a($this->controls[0], '#' . $this->options['id'], [
                'class' => 'left carousel-control',
                'data-slide' => 'prev',
            ]) . "\n"
            . Html::a($this->controls[1], '#' . $this->options['id'], [
                'class' => 'right carousel-control',
                'data-slide' => 'next',
            ]);
        } elseif ($this->controls === false) {
            return '';
        } else {
            throw new InvalidConfigException('The "controls" property must be either false or an array of two elements.');
        }
    }
}