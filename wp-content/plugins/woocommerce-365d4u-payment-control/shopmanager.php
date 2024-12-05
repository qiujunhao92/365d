<?php
function remove_menus() {
    global $menu;
    global $submenu;
    // 更改主菜单项的名称
    $menu[5][0] = 'Blogs';

    // 如果需要，也可以更改子菜单项的名称
    $submenu['edit.php'][5][0] = 'All Blogs';
    $submenu['edit.php'][10][0] = 'Add Blog';

    if (current_user_can('shop_manager') && !current_user_can('administrator')) {
        $arrAllMenu = array_column($menu, 2, 0);
        $arrMenuToKeep = [
            'Dashboard',
            'Blogs',
            'Media',
            'Pages',
            'Comments',
            'WooCommerce',
            'Products',
            'Collapse menu',
            'Instagram Feed',
            'Judge.me',
            'Forminator'
        ];
        foreach ($arrAllMenu as $key => $value) {
            if (!in_array($key, $arrMenuToKeep)) {
                remove_menu_page($value);
            }
        }
        $arrSubMenuToRemove = [
                 'index.php'   => [
                      'update_core',
                     'update-core.php',
                      'admin.php?page=monsterinsights_reports',
                      'monsterinsights-getting-started'
                 ],
                 'woocommerce' => [
                                           'eh-bulk-edit-product-attr',
                                           'checkout_form_designer',
                                           'wc-settings',
                                           'wc-status',
                                           'wc-admin&path=/customers',
                                           'wc-admin&path=/extensions'
                                 ],
               'cff-top'  => [  //Instagram
                   'cff-oembeds-manager',  //'oEmbeds'
                    'cff-top',  //Face Book Feed
                    'cff-support', //Support
                    'cff-about-us',
                    'https://smashballoon.com/custom-facebook-feed/?utm_campaign=facebook-free&utm_source=menu-link&utm_medium=upgrade-link&utm_content=UpgradeToPro', //upgrade
                    'admin.php?page=cff-top&tab=more', //more
                    'admin.php?page=cff-top&tab=more'
                 ]
       ];
        //remove sub menu
        foreach ($arrSubMenuToRemove as $mainMain =>  $childMenuToRemove) {
            foreach ($childMenuToRemove as $subMenu) {
                //remove sub menu
                remove_submenu_page($mainMain, $subMenu);
            }
        }
    }
}
function change_post_object_label() {
    global $wp_post_types;
    $labels = &$wp_post_types['post']->labels;
    $labels->name = 'Blogs';
    $labels->singular_name = 'Blog';
    $labels->add_new = 'Add Blog';
    $labels->add_new_item = 'Add New Blog';
    $labels->edit_item = 'Edit Blog';
    $labels->new_item = 'New Blog';
    $labels->view_item = 'View Blog';
    $labels->search_items = 'Search Blogs';
    $labels->not_found = 'No Blogs found';
    $labels->not_found_in_trash = 'No Blogs found in Trash';
    $labels->all_items = 'All Blogs';
    $labels->menu_name = 'Blogs';
    $labels->name_admin_bar = 'Blog';
}
add_action('admin_menu', 'remove_menus', 100000);
add_action('init', 'change_post_object_label');