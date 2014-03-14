<?php
class ModelModuleKulerSlides extends Model
{
    public function getCategories($conditions = array())
    {
        $this->load->model('catalog/category');
        $categories = $this->model_catalog_category->getCategories(0);

        // Filter categories by store
        $category_ids = array();
        if (isset($conditions['store_id']))
        {
            $query = $this->db->query('
                SELECT *
                FROM '. DB_PREFIX .'category_to_store
                WHERE store_id = '. intval($conditions['store_id']) .'
            ');

            foreach ($query->rows as $store_category)
            {
                $category_ids[] = $store_category['category_id'];
            }
        }

        foreach ($categories as $category_index => $category)
        {
            if (!in_array($category['category_id'], $category_ids))
            {
                unset($categories[$category_index]);
            }
        }

        return $categories;
    }

    public function getProducts($conditions = array(), $fetch_options = array())
    {
        $join = '';
        $where = '';
        $limitClause = '';

        if (!empty($conditions['filter_name']))
        {
            $where .= " AND LCASE(pd.name) LIKE '" . $this->db->escape(utf8_strtolower($conditions['filter_name'])) . "%'";
        }

        if (!empty($conditions['store_id']))
        {
            $join = '
                INNER JOIN '. DB_PREFIX .'product_to_store ps
                    ON (p.product_id = ps.product_id AND ps.store_id = '. intval($conditions['store_id']) .')
            ';
        }

        if (isset($fetch_options['start']))
        {
            $limitClause .= 'LIMIT ' . intval($fetch_options['start']) . ',' . intval($fetch_options['limit']);
        }

        $query = $this->db->query("
            SELECT *
            FROM " . DB_PREFIX . "product p
            LEFT JOIN " . DB_PREFIX . "product_description pd
                ON (p.product_id = pd.product_id)
            ". $join ."
            WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
                ". $where ."
            ORDER BY pd.name ASC
            ". $limitClause ."
        ");

        return $query->rows;
    }
}