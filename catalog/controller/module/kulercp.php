<?php

/*--------------------------------------------------------------------------/
* @Author		KulerThemes.com http://www.kulerthemes.com
* @Copyright	Copyright (C) 2012 - 2013 KulerThemes.com. All rights reserved.
* @License		KulerThemes.com Proprietary License
/---------------------------------------------------------------------------*/

class ControllerModuleKulercp extends Controller {
    private $options;
	public function index() {
        $this->load->model('kuler/cp');
        
		$lang       = (int)$this->config->get('config_language_id');
        $seo        = $this->config->get('seo');
        $font       = $this->config->get('font');
        $color      = $this->config->get('color');
        $block      = $this->config->get('blocks');
        $optimal    = $this->config->get('optimal');

        $options    = $this->getOptions();

        // Clear all style / script when disable mode for backend list theme style / script
        if(isset($this->request->get['kuler']) && $this->request->get['kuler'] == 'clean') {
            return;
        }

        // Get theme color style
        $this->config->set('kuler_theme_color', $color['status']);

        // Get Kuler Google Analysic
        if($seo && isset($seo['status']) && $seo['status'] && $this->config->get('config_google_analytics')) {
            $this->config->set('kuler_analytics_code', html_entity_decode($this->config->get('config_google_analytics'), ENT_QUOTES, 'UTF-8'));
            $this->config->set('kuler_analytics_position', $seo['position']);
        }

        // Get current scripts
        if($optimal && isset($optimal['script_theme']) && $optimal['script_theme']) {
            $this->config->set('kuler_compress_scripts', $this->model_kuler_cp->getCompressScripts());
            $this->config->set('kuler_compress_script_type', 'theme');
        }

        // Get current styles
        if($optimal && isset($optimal['style_theme']) && $optimal['style_theme']) {
            $this->config->set('kuler_compress_styles', $this->model_kuler_cp->getCompressStyles());
            $this->config->set('kuler_compress_style_type', 'theme');
        }

        // Get font config
        if($font && isset($font['heading']['status']) && $font['heading']['status'] == 1) {
            $font['heading'] = $font['heading'] + (isset($this->options['font']['heading']) ? $this->options['font']['heading'] : array());
            $this->config->set('kuler_heading_font', $font['heading']);
        } else {
            $this->config->set('kuler_heading_font', isset($this->options['font']['heading']) ? $this->options['font']['heading'] : array());
        }

        if($font && isset($font['body']['status']) && $font['body']['status'] == 1) {
            $font['body'] = $font['body'] + (isset($this->options['font']['body']) ? $this->options['font']['body'] : array());
            $this->config->set('kuler_body_font', $font['body']);
        } else {
            $this->config->set('kuler_body_font', isset($this->options['font']['body']) ? $this->options['font']['body'] : array());
        }

        // Block payment icons
        if(isset($block['payment']['status']) && $block['payment']['status'] && $block['payment']['items']) {
            $this->config->set('kuler_payment_status', 1);

            $payment_items = $block['payment']['items'];

            if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1')))
            {
                $server = $this->config->get('config_ssl');
            } else {
                $server = $this->config->get('config_url');
            }

            foreach ($payment_items as &$payment_item)
            {
                if (!empty($payment_item['image']))
                {
                    $payment_item['link'] = '<a><img src="'. $server . $payment_item['image'] .'" alt="'. $payment_item['name'] .'" /></a>';
                }
            }

            $this->config->set('kuler_payment_items', $payment_items);
        }

        // Block count
		
		$count = 0;
		
		if($block['info']['status'] == 1) {
			$count++;
		}
		if($block['contact']['status'] == 1) {
			$count++;
		}
		if($block['twitter']['status'] == 1) {
			$count++;
		}
		if($block['facebook']['status'] == 1) {
			$count++;
		}

		// Process language

		$info = isset($block['info']) ? $block['info'] : null;
		
		if($info) {
            if (!isset($info[$lang]))
            {
                foreach ($info as $lang_info)
                {
                    if (is_array($lang_info))
                    {
                        $default = $lang_info;
                        break;
                    }
                }

                $current = $default;
            }
            else
            {
                $current = $info[$lang];
            }

            $first_info = array();
            foreach ($info as $info_index => $info_value)
            {
                if ($info_index != 'status')
                {
                    $first_info = $info_value;
                }
            }

            if (!isset($info[$lang]))
            {
                $info[$lang] = array();
            }

            foreach ($first_info as $key => $value)
            {
                if (empty($info[$lang][$key]))
                {
                    $info[$lang][$key] = $value;
                }
            }

            $info[$lang]['status'] = $info['status'];
            $info[$lang]['description'] = html_entity_decode($info[$lang]['description'], ENT_QUOTES, 'UTF-8');
            $block['info'] = $info[$lang];
		}

        // Prepare block
        $block['contact']['title'] = $this->translate($block['contact']['title'], $lang);
        $block['twitter']['title'] = $this->translate($block['twitter']['title'], $lang);
        $block['facebook']['title'] = $this->translate($block['facebook']['title'], $lang);

        $this->language->load('module/kulercp');

		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_contact'] = $this->language->get('text_contact');
		$this->data['text_sitemap'] = $this->language->get('text_sitemap');

		$this->data['block'] = $block;
		$this->data['count'] = $count;

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/kulercp.phtml')) {
			$this->template = $this->config->get('config_template') . '/template/module/kulercp.phtml';
		} else {
			$this->template = 'default/template/module/kulercp.phtml';
		}

		$this->render();

        if (!$block['info']['status'] && !$block['contact']['status'] && !$block['twitter']['status'] && !$block['facebook']['status'])
        {
            $this->output = false;
        }
	}

    public function enableCustomCss()
    {
        $design = $this->config->get('kulercp_design');

        $this->output = false;

        if ($design && isset($design['status']) && $design['status'])
        {
            $this->output = true;
        }
    }

    public function getCustomCopyright()
    {
        $blocks = $this->config->get('blocks');

        if (!is_array($blocks) || !isset($blocks['copyright']) || !$blocks['copyright']['status'])
        {
            return false;
        }

        $this->output = html_entity_decode($blocks['copyright']['content'], ENT_QUOTES, 'UTF-8');

        return true;
    }

    private function getOptions() {
        $options = array();
        $config = DIR_TEMPLATE . $this->config->get('config_template') . '/includes/options.tpl';
        if (file_exists($config)) {
			$options = include($config);
            if(is_array($options) == false) {
                return array();
            } else {
                $this->options = $options;
            }
        }
        return $options;
    }

    private function translate($texts, $language_id)
    {
        if (is_array($texts))
        {
            $first = current($texts);

            if (is_string($first))
            {
                $texts = empty($texts[$language_id]) ? $first : $texts[$language_id];
            }
            else if (is_array($texts))
            {
                if (!isset($texts[$language_id]))
                {
                    $texts[$language_id] = array();
                }

                foreach ($first as $key => $value)
                {
                    if (empty($texts[$language_id][$key]))
                    {
                        $texts[$language_id][$key] = $value;
                    }
                }
            }
        }

        return $texts;
    }
}

?>