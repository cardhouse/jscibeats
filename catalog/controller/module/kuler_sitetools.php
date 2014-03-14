<?php

/*--------------------------------------------------------------------------/
* @Author		KulerThemes.com http://www.kulerthemes.com
* @Copyright	Copyright (C) 2012 - 2013 KulerThemes.com. All rights reserved.
* @License		KulerThemes.com Proprietary License
/---------------------------------------------------------------------------*/

class ControllerModuleKulersitetools extends Controller {
    const MODULE_NAME = 'kuler_sitetools';
    const MODULE_PREFIX = 'kuler_sitetools_';

    const SESSION_DOWNLOAD_KEY = 'kst_download_ids';
    const DOWNLOAD_TIMEOUT = 900; // 15'
    const DOWNLOAD_NAME = 'sample-data.zip';

    private $_name = self::MODULE_NAME;

	protected function index() {
        if (isset($this->request->get['hide_st']) && $this->request->get['hide_st'] == 1)
        {
            setcookie('kst_force_close', 1);
            return;
        }

        if (isset($this->request->cookie['kst_force_close']) && $this->request->cookie['kst_force_close'])
        {
            return;
        }

		$this->language->load('module/' . self::MODULE_NAME);

      	$this->data['heading_title'] = $this->language->get('heading_title');
        $text_strings = array(
            'text_theme_colors',
            'text_resolutions'
        );
        foreach ($text_strings as $text) {
            $this->data[$text] = $this->language->get($text);
        }

        $this->data['enableSiteTools'] = !isset($this->request->get['enable_sitetools']) || $this->request->get['enable_sitetools'];

        $colors = $this->config->get(self::MODULE_PREFIX . 'colors');
        $colors = $colors !== null? $colors : array();


        // Sort colors by sort_order
        $sortOrder = array();
        foreach ($colors as $key => $value)
        {
            $sortOrder[$key] = $value['sort_order'];
        }
        array_multisort($sortOrder, SORT_ASC, $colors);

        if (!empty($colors))
        {
            $colors[0]['active'] = true;
        }

        $this->data['colors'] = $colors;
        $this->data['buyUrl'] = $this->config->get(self::MODULE_PREFIX . 'buy_url');
        $this->data['show_logo'] = $this->config->get(self::MODULE_PREFIX . 'show_logo');
        $this->data['show_buy_button'] = $this->config->get(self::MODULE_PREFIX . 'show_buy_button');

        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1')))
        {
            $this->data['base'] = $this->config->get('config_ssl');
        } else {
            $this->data['base'] = $this->config->get('config_url');
        }

        $this->document->addStyle('catalog/view/kulercore/css/kuler_sitetools.css');

        $this->template = $this->getViewFile('template/module/'. self::MODULE_NAME .'.tpl');
		$this->render();
	}

    private function getViewFile($path)
    {
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/' . $path))
        {
            $newPath = $this->config->get('config_template') . '/' . $path;
        }
        else
        {
            $newPath = 'default/' . $path;
        }

        return $newPath;
    }

    public function getSampleDataUrl()
    {
        $json = array(
            'status' => 0
        );

        // Only status enabled & the sample data is exist
        $sample_data = $this->config->get(self::MODULE_PREFIX . 'sample_data');
        $upload_file = DIR_DOWNLOAD . 'kst_data' . DIRECTORY_SEPARATOR . 'sample-data.zip';

        if (!$sample_data || !$sample_data['status'] || !file_exists($upload_file))
        {
            $json['status'] = 0;
            $json['message'] = 'The sample data is not available!';
        }
        else
        {
            if (isset($this->request->get['did']))
            {
                $did = $this->request->get['did'];

                // Insert the download id into the session
                if (empty($_SESSION[self::SESSION_DOWNLOAD_KEY]))
                {
                    $_SESSION[self::SESSION_DOWNLOAD_KEY] = array();
                }

                $_SESSION[self::SESSION_DOWNLOAD_KEY][$did] = time();

                // Generate download id by the download id
                $download_url = HTTP_SERVER . 'download/kst_data/sample-data.zip';

                // Prepare success response
                $json = array(
                    'status' => 1,
                    'download_url' => $download_url
                );
            }
        }

        $this->response->setOutput($_GET['callback']."(".json_encode($json).");");
    }
}
?>