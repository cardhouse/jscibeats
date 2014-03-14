<?php

/*--------------------------------------------------------------------------/
* @Author		KulerThemes.com http://www.kulerthemes.com
* @Copyright	Copyright (C) 2012 - 2013 KulerThemes.com. All rights reserved.
* @License		KulerThemes.com Proprietary License
/---------------------------------------------------------------------------*/

// Help load language

function __($text)
{
	// todo: write helper
	return $text;
}

class ControllerModuleKulerSitetools extends Controller {
	
	private $error = array(); 

	const MODULE_NAME = 'kuler_sitetools';
	const MODULE_PREFIX = 'kuler_sitetools_';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->language('module/' . self::MODULE_NAME);
    }
	
	public function index() {
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate())
		{
            // Show in all layouts
            $this->load->model('design/layout');
            $layouts = $this->model_design_layout->getLayouts();

            $this->request->post[self::MODULE_PREFIX . 'module'] = array();

            foreach ($layouts as $layout)
            {
                $this->request->post[self::MODULE_PREFIX . 'module'][] = array(
                    'layout_id' => $layout['layout_id'],
                    'position' => 'content_bottom',
                    'status' => $this->request->post[self::MODULE_PREFIX . 'colors_status'],
                    'sort_order' => 99999
                );
            }

            $this->load->model('module/' . self::MODULE_NAME);

            $this->model_setting_setting->editSetting(self::MODULE_NAME, array(
                self::MODULE_PREFIX . 'module' => $this->request->post[self::MODULE_PREFIX . 'module'],
                self::MODULE_PREFIX . 'colors' => $this->request->post[self::MODULE_PREFIX . 'colors'],
                self::MODULE_PREFIX . 'colors_status' => $this->request->post[self::MODULE_PREFIX . 'colors_status'],
                self::MODULE_PREFIX . 'buy_url' => $this->request->post[self::MODULE_PREFIX . 'buy_url'],
                self::MODULE_PREFIX . 'show_buy_button' => $this->request->post[self::MODULE_PREFIX . 'show_buy_button'],
                self::MODULE_PREFIX . 'show_logo' => $this->request->post[self::MODULE_PREFIX . 'show_logo'],
                self::MODULE_PREFIX . 'sample_data' => $this->request->post['sample_data'],
            ));

			$this->session->data['success'] = $this->language->get('text_success');
			
            if(isset($this->request->post['op']) && $this->request->post['op'] == 'close') {
                $this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
            } else {
                $this->redirect($this->url->link('module/kuler_sitetools', 'token=' . $this->session->data['token'], 'SSL'));
            }
		}

		// Get colors from the database
		$config_data = array(
		    self::MODULE_PREFIX . 'colors',
            self::MODULE_PREFIX . 'colors_status',
            self::MODULE_PREFIX . 'buy_url',
            self::MODULE_PREFIX . 'show_buy_button',
            self::MODULE_PREFIX . 'show_logo'
        );

        foreach ($config_data as $conf) {
			if (isset($this->request->post[$conf])) {
				$this->data[$conf] = $this->request->post[$conf];
			} else {
				$this->data[$conf] = $this->config->get($conf);
			}
		}

        // Group by color
        $dbColors = array();
        if (!empty($this->data[self::MODULE_PREFIX . 'colors']))
        {
	        foreach ($this->data[self::MODULE_PREFIX . 'colors'] as $tcolor)
	        {
	        	list($prefix, $color) = explode('-', $tcolor['body_class']);

	        	$dbColors[$color] = $tcolor['description'];
	        }
        }

        // Get colors from the option of the default theme
        $themeColors = $this->helperGetColors();

        // Generate new colors
        $newColors = array();
        $sortOrder = 1;
        $changed = count($themeColors) != count($dbColors);
        foreach ($themeColors as $color => $themeColor)
        {
        	if (!$changed)
        	{
	        	$changed = !isset($dbColors[$color]);
        	}

        	$newColors[] = array(
        		'body_class' => 'color-' . $color,
        		'description' => isset($dbColors[$color])? $dbColors[$color] : '',
        		'sort_order' => $sortOrder++
        	);
        }

        $this->data[self::MODULE_PREFIX . 'colors'] = $newColors;
        $this->data['colorChanged'] = $changed;

        // Sample Data
        $sample_data = $this->config->get(self::MODULE_PREFIX . 'sample_data');

        if (!$sample_data)
        {
            $sample_data = array(
                'status' => 0,
                'folders' => "image/data=image/data\r\ndownload/language/admin=admin\r\ndownload/language/catalog=catalog"
            );
        }

        $this->data['sample_data'] = $sample_data;

		$text_strings = array(
			'heading_title',
            'text_module',
			'text_enabled',
			'text_disabled',
            'text_theme_colors',
            'text_resolution',
            'text_position',
            'text_content_top',
            'text_content_bottom',
            'text_column_left',
            'text_column_right',
            'text_sample_data',
            'button_save',
            'button_close',
            'button_cancel',
            'button_add_resolution',
            'button_add_color',
            'button_add_module',
            'button_remove',
            'button_add_color',
            'button_build_sample_data',
            'text_success',
            'entry_body_class',
            'entry_class',
            'entry_description',
            'entry_sort_order',
            'entry_color',
            'entry_layout',
            'entry_position',
            'entry_status',
            'entry_folders'
		);
		
		foreach ($text_strings as $text) {
			$this->data[$text] = $this->language->get($text);
		}

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('module/' . self::MODULE_NAME, 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['action'] = $this->url->link('module/' . self::MODULE_NAME, 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['build_sample_data_url'] = $this->url->link('module/' . self::MODULE_NAME . '/buildsampledata', 'token=' . $this->session->data['token'], 'SSL');

		$this->template = 'module/'. self::MODULE_NAME .'.tpl';
		$this->children = array(
			'common/header',
			'common/footer',
		);

		$this->response->setOutput($this->render());
	}

    public function buildSampleData()
    {
        if ($this->request->server['REQUEST_METHOD'] == 'GET' || !$this->validate())
        {
            $this->index();
            return;
        }

        $this->load->model('module/kuler_sitetools');
        /* @var ModelModuleKulerSitetools $model */
        $model = $this->model_module_kuler_sitetools;

        try
        {
            $oc_path = dirname(DIR_APPLICATION) . DIRECTORY_SEPARATOR;
            $kst_dir = $oc_path . 'download/kst_data/';

            $result = array();

            // Create download folder
            if (!is_dir($kst_dir))
            {
                mkdir($kst_dir, 0777, true);
            }

            // Export database if zip success
            $db_file = $kst_dir . 'db.sql';
            $tables = $model->getTables();
            $exported_sql = $model->exportTables($tables);

            // Filter database
            $exported_sql = preg_replace('#^.+?[^(module/)]kuler_sitetools.+$#m', '', $exported_sql);

            file_put_contents($db_file, $exported_sql);

            // Get folder to zip
            $sample_data = $this->request->post['sample_data'];
            $folders = explode("\r\n", $sample_data['folders']);
            $sources = array(
                $db_file => 'download/kst_data/db.sql',
            );
            foreach ($folders as $folder)
            {
                if (!empty($folder))
                {
                    $folder = preg_replace('#\s#', '', $folder);
                    list($source_path, $destination_path) = explode('=', $folder);

                    $sources[$oc_path . $source_path] = $destination_path;
                }
            }

            // Zip images, downloads
            $upload_file = $kst_dir . 'sample-data.zip';

            if (is_file($upload_file))
            {
                @unlink($upload_file);
            }

            if (!$model->compress($sources, $upload_file))
            {
                throw new Exception($this->language->get('error_compression'));
            }

            // Remove the database file
            if (is_file($db_file))
            {
                @unlink($db_file);
            }
        }
        catch (Exception $e)
        {
            $this->error['sample_data'] = $e->getMessage();
        }

        $this->index();
    }

	/**
	 * Get colors from the default theme
	 * @return [array]
	 */
	private function helperGetColors()
	{
		$options = require(DIR_CATALOG . 'view/theme/' . $this->config->get('config_template') . '/includes/options.tpl');

		return isset($options['color'])? $options['color'] : array();
	}
	
	private function validate() {
		if (!$this->user->hasPermission('modify', 'module/' . self::MODULE_NAME)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}	
	}
}
?>