<?php

use Automattic\WooCommerce\Blocks\Utils\BlockTemplateUtils;

if (!class_exists('Custom365dTemplateLoader')) {
    class Custom365dTemplateLoader
    {
        protected function replaceTemplateBySlugIn($templates, $template_type, $query, $source, $target)
        {
            $slugs = $query['slug__in'] ?? [];
            if (!empty($slugs)) {
                foreach ($slugs as $i => $slug) {
                    $slugs[$i] = str_replace($source, $target, $slug);
                }
                $templates = BlockTemplateUtils::get_block_templates_from_db($slugs, $template_type);
            }
            return $templates;
        }

        public function cus_update_product_content($templates, $query, $template_type)
        {
            $sourceTemplateList = [];
            $replaceTemplate = '' ;
            if (is_product_category('custom-all')) {
                $sourceTemplateList = ['taxonomy-product_cat', 'archive-product'];
                $replaceTemplate = 'product-cat-custom';
            } elseif (is_product_category('standard-all')) {
                $sourceTemplateList = ['taxonomy-product_cat', 'archive-product'];
                $replaceTemplate = 'product-cat-standard';
            } else {
                $checkStandard = checkCurrentIsStandardDetail();
                if ($checkStandard) {
                    $sourceTemplateList = ['single-product'];
                    $replaceTemplate = 'single-product-standard';
                } elseif (wp_is_mobile() && is_singular('product')) {
                    $sourceTemplateList = ['single-product'];
                    $replaceTemplate = 'single-product-mobile';
                }
            }
            if (!empty($sourceTemplateList) && !empty($replaceTemplate)) {
                foreach ($sourceTemplateList as $source) {
                    $templates = $this->replaceTemplateBySlugIn($templates, $template_type, $query,$source, $replaceTemplate);
                }
                if (!empty($templates)) {
                    foreach ($templates as $objTemplate) {
                        if (isset($objTemplate->slug)) {
                            $objTemplate->slug = $replaceTemplate;
                        }
                    }
                }
            }
            return $this->updateTemplate($templates, $query);
        }

        public function cus_365d4u_update_templatePart($block_content, $parsed_block, $obj)
        {
            if (is_array($parsed_block) && isset($parsed_block['attrs']["slug"])) {
                $slugin = $parsed_block['attrs']["slug"];
                if (in_array($slugin, ['header', 'footer'])) {
                    //only header footer need update
                    $fileContent = $this->getFileContentBySlugName('part', $slugin);
                    if (!empty($fileContent)) {
                        $block_content = $fileContent;
                    }
                }
            }
            return $block_content;
        }

        private function getFileContentBySlugName($childDir, $slug_in)
        {
            $fileDir = dirname(plugin_dir_path(__FILE__), 3) . '/templates-html' ;
            if (!empty($childDir)) {
                $fileDir .= '/' . $childDir;
            }
            $htmlFile = $fileDir . '/' . $slug_in . '.html';
            $content = '';
            if (file_exists($htmlFile)) {
                try{
                    $content = file_get_contents($htmlFile);
                    // 正则表达式匹配 <link rel="stylesheet" href="css/xxx.css"> 和 <script src="../js/xxx.js">
                    $pattern = '/<(link\s+rel="stylesheet"\s+href="|script\s+src=")([^"]*)"/i';
                    // 使用回调函数进行路径替换
                    $content = preg_replace_callback($pattern, function ($matches) use ($childDir) {
                        $attribute = $matches[1]; // link 或 script 部分
                        $path = $matches[2];     // href 或 src 的路径

                        // 判断是否为绝对路径（以 http://, https:// 或 / 开头）
                        if (strpos($path, 'http://') === 0
                            || strpos($path, 'https://') === 0
                            || strpos($path, '/') === 0
                        ) {
                            return $matches[0]; // 不替换，返回原内容
                        }
                        // 如果路径以 ../ 开头，考虑 childDir
                        if (!empty($childDir)) {
                            if (strpos($path, '../') === 0) {
                                // 计算新的路径
                                $adjustedPath = str_replace('../', '', $path);
                                $newPath = "/templates-html/{$adjustedPath}";
                            } else{
                                $adjustedPath = str_replace('./', '', $path);
                                $newPath = "/templates-html/{$childDir}/{$adjustedPath}";
                            }
                        } else {
                            if (strpos($path, '../') === 0) {
                                $adjustedPath = str_replace('../', '', $path);
                                $newPath = "/{$adjustedPath}";
                            } else{
                                $adjustedPath = str_replace('./', '', $path);
                                $newPath = "/templates-html/{$childDir}/{$adjustedPath}";
                            }
                        }
                        // 返回替换后的标签
                        return '<' . $attribute . $newPath . '"';
                    }, $content);
                } catch (\Exception $ex) {
                    error_log('Fail to read ' . $htmlFile . ', Error:' . $ex->getMessage());
                }
            }
            return $content;
        }

        /**
         * Update Slugin Template
         *
         * @param $query_result
         * @param $query
         * @return mixed
         */
        private function updateTemplate($query_result, $query)
        {
            $slugs = $query['slug__in'] ?? [];
            if ($query_result && !empty($slugs)) {
                foreach ($query_result as $objTemplate) {
                    if (isset($objTemplate->slug)) {
                        $fileContent = $this->getFileContentBySlugName('', $objTemplate->slug);
                        if (!empty($fileContent)) {
                            $objTemplate->content = $fileContent;
                        }
                    }
                }
            }
            return $query_result;
        }

        public function cus_update_template_content($query_result, $query, $template_type)
        {
            return $this->updateTemplate($query_result, $query);
        }
    }

    $templateLoader = new Custom365dTemplateLoader();
    add_filter('render_block_core/template-part',  array($templateLoader,'cus_365d4u_update_templatePart'), 10, 3);
    add_action( 'pre_get_block_templates', array( $templateLoader, 'cus_update_product_content' ), 10, 3 );
    add_action( 'get_block_templates', array( $templateLoader, 'cus_update_template_content' ), 10, 3 );
}