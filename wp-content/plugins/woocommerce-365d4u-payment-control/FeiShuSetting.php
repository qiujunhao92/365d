<?php
if (!class_exists('FeiShuSetting')) {
    class FeiShuSetting
    {
        /**
         * @return array
         */
        protected function getDefaultFeiShuSetting(): array
        {
            return [
                'prod' => [
                    'key' => 'custom_prod_feishu_',
                    'title' => 'FeiShu Production Http ',
                    'desc' => 'Production FeiShu Request ',
                    'req' => [
                        'url' => [
                            'value' => 'https://b5s1ydl53m.feishu.cn/ae/api/v1/automation/namespaces/package_93ad83__c/events/http/automation_3490478d412',
                            'type' => 'textarea',
                        ],
                        'auth' => [
                            'value' => '0478d412739',
                            'type' => 'text',
                        ]

                    ]
                ],
                'dev' => [
                    'key' => 'custom_dev_feishu_',
                    'title' => 'FeiShu Development Http ',
                    'desc' => 'Development FeiShu Request ',
                    'ctrl_type' => 'text',
                    'req' => [
                        'url' => [
                            'value' => 'https://b5s1ydl53m-dev8.aedev.feishuapp.cn/ae/api/v1/automation/namespaces/package_93ad83__c/events/http/automation_3490478d412',
                            'type' => 'textarea',
                        ],
                        'auth' => [
                            'value' => '478d4127394',
                            'type' => 'text',
                        ]
                    ]
                ]
            ];
        }

        /**
         * Add Setting
         *
         * @param $settings
         * @return mixed
         */
        public function addSetting($settings)
        {
            $settings[] = array(
                'title' => __('Enable FeiShu Request After Paid', 'woocommerce'),
                'desc' => __('Enable  Send FeiShu Http Request After Paid', 'woocommerce'),
                'id' => 'custom_enable_feishu',
                'type' => 'checkbox',
                'default' => 'no',
            );
            $settings[] = array(
                'title' => __('FeiShu Development mode', 'woocommerce'),
                'desc' => __('If yes,set to development mode,else set to production mode', 'woocommerce'),
                'id' => 'custom_feishu_mode',
                'type' => 'checkbox',
                'default' => 'no',
            );
            //后台展示默认配置
            $defaultFeiShuSetting = $this->getDefaultFeiShuSetting();
            foreach($defaultFeiShuSetting as $mainKey => $feiShuSetting) {
                foreach ($feiShuSetting['req'] as $reqKey => $reqDefaultVal) {
                    $settingKey = $feiShuSetting['key'] . $reqKey;
                    $feiShuTitle =  $feiShuSetting['title'] . ucfirst($reqKey);
                    $feiShuDesc =  $feiShuSetting['desc'] . ucfirst($reqKey);
                    $settings[] = array(
                        'title' => $feiShuTitle,
                        'desc' => $feiShuDesc,
                        'id' => $settingKey,
                        'type' =>  $reqDefaultVal['type'],
                        'default' => $reqDefaultVal['value'],
                    );
                }
            }
            return $settings;
        }

        /**
         * UPdate Feishu OPtion
         *
         * @return void
         */
        public function updateFeiShuOptions()
        {
            //production
            $enabledFeiShuHttp = isset($_POST['custom_enable_feishu']) ? 'yes' : 'no';
            $enabledFeiShuProd = isset($_POST['custom_feishu_mode']) ? 'yes' : 'no';
            update_option('custom_enable_feishu', $enabledFeiShuHttp);
            update_option('custom_feishu_mode', $enabledFeiShuProd);

            $defaultFeiShuSetting = $this->getDefaultFeiShuSetting();
            foreach($defaultFeiShuSetting as $mainKey => $feiShuSetting) {
                $baseKey = $feiShuSetting['key'];
                foreach ($feiShuSetting['req'] as $reqKey => $reqDefaultVal) {
                    $settingKey = $baseKey . $reqKey;
                    //根据post内容更新配置飞书配置
                    $postFeiShuVal = $_POST[$settingKey] ?? $reqDefaultVal['value'];
                    update_option($settingKey, $postFeiShuVal);
                }
            }
        }

        /**
         * Get FeiShu Url Auth
         *
         * @return array
         */
        public function getFeiShuUrlAuth(): array
        {
            $enabled = get_option('custom_enable_feishu') ?? '';
            $feiShuUrl = get_option('custom_prod_feishu_url') ?? '';
            $feiShuAuth = get_option('custom_prod_feishu_auth') ?? '';
            if (get_option('custom_feishu_mode') == 'yes') {
                $feiShuUrl = get_option('custom_dev_feishu_url');
                $feiShuAuth = get_option('custom_dev_feishu_auth');
            }
            return [$enabled, $feiShuUrl, $feiShuAuth];
        }
    }
}