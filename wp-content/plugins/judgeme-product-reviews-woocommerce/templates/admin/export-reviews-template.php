<?php
  $token = get_option( 'judgeme_shop_token' );
if ( ! empty( $token ) ):
	?>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
  </head>
  <div class= "judgeme__woocommerce-plugin-container">
    <div class= "home-page-container">
      <div class= "home-page__header-container">
        <div class= "home-page__header-welcome-container">
          <img class= "home-page__jdgm-image" src="<?php echo JGM_PLUGIN_URL.'assets/images/jdgm-logo.png'; ?>"></image>
          <div class= "home-page__jdgm-intro-container">
            <h1 class= "home-page__title">Welcome to Judge.me!</h1>
            <p class= "home-page__jdgm-intro-text">Judge.me powers the product reviews for your WooCommerce store. You can manage your reviews and settings directly in our app.</p>
            <a class= "home-page__open-jdgm-btn" href= "<?php echo $url; ?>" target="_blank">Get started! Open Judge.me now</a>
          </div>
        </div>
        <div class= "home-page__header-jdgm-info-container">
          <div class= "home-page__header-contact-us-container">
            <img class= "home-page__header-jdgm-chat-logo" src= "<?php echo JGM_PLUGIN_URL.'assets/images/icon-helpdesk.jpg'; ?>">
            <span class= "home-page__header-faq-text-container">
              <p class= "home-page__header-faq-text">Check out our <a target= "_blank" href= "https://support.judge.me/support/solutions/articles/44001038088-faq">FAQ</a> and <a href= "https://support.judge.me/support/solutions" target= "_blank"> Knowledge Base </a>. Learn about our latest features in our <a href= "https://blog.judge.me/" target= "_blank">blog</a>, ask a question on our <a href= "https://support.judge.me/support/discussions" target= "_blank">forum</a>, or get in touch via <a href="mailto:support@judge.me">email</a> or <a href= "https://support.judge.me/support/home" target= "_blank"> chat</a>.</p>
              <p>Have a cool idea? Tell us your suggestion at <a href= "https://feedback.judge.me/" target= "_blank"> feature upvote</a>.</p>
            </span>
          </div>

          <div class= "home-page__header-rate-us-container">
            <img src= "<?php echo JGM_PLUGIN_URL.'assets/images/stars.png'; ?>">
            <p class= "home-page__header-rate-us-text">If you find Judge.me useful, help us by rating our <a href= "https://wordpress.org/support/plugin/judgeme-product-reviews-woocommerce/reviews/#new-post" target= "_blank"> WP plugin</a>. Your support helps us grow and develop more new features quickly. </p>
            <a href= "https://wordpress.org/support/plugin/judgeme-product-reviews-woocommerce/reviews/#new-post"class= "home-page__header-rate-us-button" target= "_blank"> Rate us </a>
          </div>
        </div>
      </div>
      <p class="home-page_terms-and-policy">By using this service, I confirm that I have read and agree to the <a href="https://judge.me/terms">Terms of Service</a> and <a href="https://judge.me/privacy">Privacy Policy</a>.</p>
      <div class= "home-page__nav-container">
        <ul class="nav">
          <li class="nav-item">
            <a class="nav-link active home-page__general-tab" data-toggle="tab" href="#home-page__general-tab" autofocus>General</a>
          </li>
          <li class="nav-item">
            <a class="nav-link home-page__faq-tab" data-toggle="tab" href="#home-page__faq-tab">FAQs</a>
          </li>
          <li class="nav-item">
            <a class="nav-link home-page__advanced-tab" data-toggle="tab" href="#home-page__advanced-tab">Advanced</a>
          </li>
          <li class="nav-item">
            <a class="nav-link home-page__customer-support-tab" data-toggle="tab" href="#home-page__tab-for-customer-support">CS</a>
          </li>
        </ul>
      </div>

      <div class= "tab-content">
        <div id="home-page__general-tab" class="tab-pane show active">
          <div class= "home-page__general-container">
            <div class= "home-page__quick-installation">
              <h3 class= "home-page__quick-installation-title"> Judge.me Widgets: Quick Installation </h3>
              <div class= "home-page__quick-installation-widget-info">
                <p> The <a target= "_blank" href="https://judge.me/settings?jump_to=review+widget+installation">Review Widget</a> displays customer reviews for a specific product and the <a target= "_blank" href= "https://judge.me/settings?jump_to=preview+badge+installation"> Preview Badge</a> displays the average rating (number of stars) for a product. </p>
                <p> Simply enable the widget you'd like to install and we will create it on your live page. You can visit our help desk for <a target= "_blank" href= "https://support.judge.me/support/home">more information about widgets</a> and
                  detailed instructions. Need additional help to customize the position? Write to us at <a href= "mailto:support@judge.me">support@judge.me</a> and we'll be on it! </p>
              </div>

              <div class= "home-page__quick-installation-widgets-container">
                <?php
                  $hide_widget = get_option('judgeme_option_hide_widget');
                  $hide_preview_badge_collection = get_option('judgeme_option_hide_preview_badge_collection');
                  $hide_preview_badge_single = get_option('judgeme_option_hide_preview_badge_single');
                ?>

                <div class= "home-page__quick-installation-widgets-inner-container">
                  <div class= "home-page__quick-installation-review-widget">
                    <h4 class= "home-page__quick-installation-review-widget-title">Review Widget on Product page</h4>
                    <img src= "<?php echo JGM_PLUGIN_URL.'assets/images/review-widget.png'; ?>">
                    <div class= "home-page__quick-installation-review-widget-toggle">
                      <label class="switch" for="review-widget-toggle">
                        <input <?php if(!$hide_widget) echo "checked" ?> type="checkbox" id="review-widget-toggle" data-type="judgeme_option_hide_widget" class= "home-page__quick-installation-review-widget-toggle-switch widget-toggle" />
                        <div class="slider round"></div>
                      </label>
                      <span class= "home-page__quick-installation-review-widget-install-uninstall-text-toggle">Installed</span>
                    </div>
                  </div>

                  <div class= "home-page__quick-installation-collection-page">
                    <h4 class= "home-page__quick-installation-collection-page-title">Preview Badge on Collection page</h4>
                    <img src= "<?php echo JGM_PLUGIN_URL.'assets/images/collection-page.png'; ?>">
                    <div class= "home-page__quick-installation-collection-page-badge-toggle">
                      <label class="switch" for="collection-page-badge-toggle">
                        <input <?php if(!$hide_preview_badge_collection) echo "checked" ?> type="checkbox" id="collection-page-badge-toggle" data-type="judgeme_option_hide_preview_badge_collection" class= "home-page__quick-installation-collection-page-badge-toggle-switch widget-toggle" />
                        <div class="slider round"></div>
                      </label>
                      <span class= "home-page__quick-installation-collection-page-badge-install-uninstall-text-toggle">Installed</span>
                    </div>
                  </div>

                  <div class= "home-page__quick-installation-product-page">
                    <h4 class= "home-page__quick-installation-product-page-title">Preview Badge on Product page</h4>
                    <img src= "<?php echo JGM_PLUGIN_URL.'assets/images/product-page.png'; ?>">
                    <div class= "home-page__quick-installation-product-page-badge-toggle">
                      <label class="switch" for="product-page-badge-toggle">
                        <input <?php if(!$hide_preview_badge_single) echo "checked" ?> type="checkbox" id="product-page-badge-toggle" data-type="judgeme_option_hide_preview_badge_single" class= "home-page__quick-installation-product-page-badge-toggle-switch widget-toggle" />
                        <div class="slider round"></div>
                      </label>
                      <span class= "home-page__quick-installation-product-page-install-uninstall-text-toggle">Installed</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class= "home-page__install-widget-yourself">
              <div class= "home-page__install-widget-install-widget-yourself-link-container">
                <a class= "home-page__install-widget-install-widget-yourself-link">DIY: Install the widgets by yourself and choose their specific position <span class= "caret"></span></a>

              </div>
              <div class= "home-page__install-widget-yourself-installation-guide-container">
                <p class= "home-page__install-widget-yourself-installation-title">Install the Review Widget</p>
                <p>
                  You can use the code <code>[jgm-review-widget]</code> by adding this code snippet to your product page.
                </p>
                <p>
                  To show the review widget for a specific product, please use:
                </p>
                <p class= "home-page__install-widget-yourself-installation-guide-example">
                  <code id= "home-page__install-widget-yourself-review-widget-code" value= "[jgm-review-widget id= /insert product id/]"> [jgm-review-widget id= /insert product id/]</code>
                  <i class= "far fa-clone fa-lg home-page__install-widget-yourself-copy-and-paste-icon" data-clipboard-target= "#home-page__install-widget-yourself-review-widget-code"></i>
                </p>
                <p>
                  For example: [jgm-review-widget id= 342] to show the review widget of the product with id 342.
                </p>
                <hr class= "home-page__divider">

                <p class= "home-page__install-widget-yourself-installation-title">Install the Preview Badge</p>
                <p>
                  You can use the code <code> [jgm-preview-badge] </code> in the product page.
                </p>
                <p>
                  To show star rating for a specific product, please use:
                </p>
                <p class= "home-page__install-widget-yourself-installation-guide-example">
                  <code id= "home-page__install-widget-yourself-preview-badge-code" value= "[jgm-preview-badge id= /insert product id/]"> [jgm-preview-badge id= /insert product id/]</code>
                  <i class="far fa-clone fa-lg home-page__install-widget-yourself-copy-and-paste-icon" data-clipboard-target= "#home-page__install-widget-yourself-preview-badge-code"></i>
                </p>
                <p>
                  For example: [jgm-preview-badge id= 342] to show the star rating of the product with id 342.
                </p>
                <hr class= "home-page__divider">

                <p class= "home-page__install-widget-yourself-installation-title">Install the Review Carousal</p>
                <p>
                  Insert this shortcode anywhere on your pages:
                </p>
                <p class= "home-page__install-widget-yourself-installation-guide-example">
                  <code id= "home-page__install-widget-yourself-carousal-code" value= "[jgm-featured-carousel title= 'Let customers speak for us' all-reviews-page='#']"> [jgm-featured-carousel title= 'Let customers speak for us' all-reviews-page='#'] </code>
                  <i class="far fa-clone fa-lg home-page__install-widget-yourself-copy-and-paste-icon" data-clipboard-target= "#home-page__install-widget-yourself-carousal-code"></i>
                </p>
                <p>
                  You can customize the title field and put the link of the all reviews page created above to the all-reviews-page field.
                  Note: You will need to feature at least 1 review (recommended: 3) to show the carousel.
                </p>
                <hr class= "home-page__divider">

                <p> View <a href= "https://support.judge.me/support/solutions/articles/44001699618-widgets-shortcode" target= "_blank"> all the shortcodes and learn more about customizing them in our help desk.</a> </p>

              </div>
            </div>

            <div class= "home-page__judge-me-ideas">
              <p class= "home-page__judge-me-ideas-title">
                Need ideas? Check out what Judge.me can do for you!
              </p>

              <div class= "home-page__judge-me-features-container">
                <div class= "home-page__judge-me-features home-page__judge-me-set-up-reviews">
                  <div class= "home-page__judge-me-features-image">
                    <img src= "<?php echo JGM_PLUGIN_URL.'assets/images/set-up-reviews.png'; ?>">
                  </div>
                    <p class= "home-page__judge-me-subtitle">Set up your reviews so they fit your business perfectly</p>
                    <ul class= "home-page__judge-me-info">
                      <li><a target= "_blank" href= "https://judge.me/import">Import existing reviews</a> or collect new <a target= "_blank" href= "https://judge.me/settings?jump_to=enable+requests">email</a>, <a target= "_blank" href= "https://judge.me/settings?jump_to=review+photos+and+videos">picture</a> and <a target= "_blank" href= "https://judge.me/settings?jump_to=review+photos+and+videos">video</a> reviews by setting up <a target= "_blank" href= "https://judge.me/settings?jump_to=enable+requests">automatic requests </a> and <a target= "_blank" href= "https://judge.me/settings?jump_to=automatic+reminders">reminders</a>. </li>
                      <li>Customize the <a href= "https://judge.me/settings?jump_to=widget+themes" target= "_blank">themes</a> and <a href= "https://judge.me/settings?jump_to=widget+star+color" target= "_blank">design</a> of your Review Widget and choose when to <a href= "https://judge.me/settings?jump_to=review+curation" target= "_blank"> publish reviews automatically.</a></li>
                      <li>Showcase multiple reviews with the <a href= "https://judge.me/settings?jump_to=reviews+carousel+installation" target= "_blank">Review Carousel</a> and <a target= "_blank" href= "https://judge.me/settings?jump_to=all+reviews+page+installation">All Reviews Page</a>, and build trust with the <a target= "_blank" href= "https://judge.me/settings?jump_to=verified+reviews+count+badge+installation">Verified Review Count Badge</a>. </li>
                    </ul>
                </div>

                <div class= "home-page__judge-me-features home-page__judge-me-engage-with-users">
                  <div class= "home-page__judge-me-features-image">
                    <img src= "<?php echo JGM_PLUGIN_URL.'assets/images/engage-users.png'; ?>">
                  </div>
                  <p class= "home-page__judge-me-subtitle">Engage with your users beyond reviews</p>
                    <ul class= "home-page__judge-me-info">
                      <li>Reward your reviewers with <a href= "https://judge.me/settings?jump_to=coupons+email" target= "_blank">coupons</a> with <a href= "https://judge.me/settings?jump_to=generated+coupon+code" target= "_blank">discount codes</a> and get repeat sales.</li>
                      <li>Get community answers by adding a <a href= "https://judge.me/settings?jump_to=question+and+answers+installation" target= "_blank">Q&A section</a> and gather additional product feedback like NPS ratings using <a href= "https://judge.me/custom_forms" target= "_blank">custom forms</a>.</li>
                      <li>Connect your <a href= "https://judge.me/settings?jump_to=facebook+authentication" target= "_blank">Facebook business page</a> and <a href= "https://judge.me/settings?jump_to=twitter+authentication" target= "_blank">Twitter account</a> to automatically share your reviews on social media. </li>
                    </ul>
                </div>

                <div class= "home-page__judge-me-features home-page__judge-me-improve-workflow">
                  <div class= "home-page__judge-me-features-image">
                    <img src= "<?php echo JGM_PLUGIN_URL.'assets/images/improve-workflow.png'; ?>">
                  </div>
                  <p class= "home-page__judge-me-subtitle">Improve your workflow with these advanced features</p>
                    <ul class= "home-page__judge-me-info">
                      <li>Increase your reviews by synchronizing across <a href= "https://judge.me/products" target= "_blank">product groups</a> and <a href= "https://judge.me/settings?jump_to=cross-shop+review+syndication" target= "_blank">shop groups</a>.</li>
                      <li>Supercharge your SEO with <a href= "https://judge.me/settings?jump_to=seo+-+rich+snippets" target= "_blank">Google Rich Snippets</a> (we create them automatically) and add reviews to Google shopping using your <a href= "https://judge.me/settings?jump_to=google+product+review+feed" target= "_blank">Google Product Review Feed</a>.</li>
                      <li>Check out our many integration partners, who can help you <a href= "https://judge.me/settings?jump_to=email+marketing+integration" target= "_blank">schedule requests</a> and <a href= "https://judge.me/settings?jump_to=push+notifications" target= "_blank"> collect reviews</a>, to storefront apps that help with <a href= "https://judge.me/settings?jump_to=marketing+suggestions+and+upsells" target= "_blank">marketing and upsells</a>. </li>
                    </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div id="home-page__tab-for-customer-support" class="tab-pane fade">

          <a href="javascript:void(0);" class="toggle-advanced-debug">Toggle debug functions</a>
          <div class="advanced-debug" style="display: none;">
            <div>
              <p>Clear Synchronize Status of products:</p>
              <button class="clear-sync-btn">Clear full sync status</button>
              <button class="clear-sync-each-product-btn">Clear each product sync</button>
            </div>
            <div>
              <p>Reset Single Product Judge.me Review Data:</p>
              <input type="text" id="jgm-product-id" placeholder="Product ID"/>
              <button class="clean-product-btn">Reset Single Product</button>
            </div>
            <div>
              <p>Register products per page</p>
              <input type="number" id="jgm-per-page" placeholder="200" value="200" />
            </div>
          </div>
        </div>

        <div id="home-page__faq-tab" class="tab-pane fade">
          <div class= "home-page__faq-container">
            <div class= "home-page__faqs">
              <div class= "home-page__faq-post home-page__first-faq-post">
                <i class="fa fa-minus-circle fa-lg home-page__faq-icon"></i>
                <div class= "home-page__faq-info">
                  <div class= "home-page__faq-title">
                    I still see the old version of the Interface!/I don't see the 'Advanced' tab on my Interface.
                  </div>
                  <div class= "home-page__faq-answer">
                    If you don't see the "Advanced" tab, you need to update the Judge.me plugin to the latest version. The changes should happen automatically, but maybe your configuration in WordPress is blocking these automatic updates.<br><br>

                    Read more about <a target= "_blank" href= "https://support.judge.me/support/solutions/articles/44001916989-i-still-see-the-old-version-of-the-interface-i-don-t-see-the-advanced-tab-on-my-interface-">How to update to the latest Judge.me interface</a>.
                  </div>
                </div>
              </div>
              <div class= "home-page__faq-post home-page__second-faq-post">
                <i class="fa fa-minus-circle fa-lg home-page__faq-icon"></i>
                <div class= "home-page__faq-info">
                  <div class= "home-page__faq-title">
                    My WooCommerce products are not synchronized with Judge.me. How can I synchronize them?
                  </div>
                  <div class= "home-page__faq-answer">
                    WooCommerce products should automatically synchronize with Judge.me products, but in rare cases, they may not and you may need to manually synchronize them.<br><br>

                    To synchronize your WooCommerce Products with Judge.me manually, go to Advanced > click the Synchronize Products button, then wait a few minutes and check if the products have synced.<br><br>

                    If you need to synchronize products again, before you click <b>Synchronize Products</b>, first click the <b>Reset Synchronize Products Status button</b>.<br><br>
                    Read more about <a href= "https://support.judge.me/support/solutions/articles/44001917145-my-woocommerce-products-are-not-synchronized-with-judge-me-how-can-i-synchronize-them-" target= "_blank">How to synchronize your products correctly</a>.
                  </div>
                </div>
              </div>
              <div class= "home-page__faq-post">
                <i class="fa fa-plus-circle fa-lg home-page__faq-icon"></i>
                <div class= "home-page__faq-info">
                  <div class= "home-page__faq-title">
                    My Judge.me widgets are not updating. How can I force an update?
                  </div>
                  <div class= "home-page__faq-answer">
                    Judge.me can work with several cache plugins, which automatically purge the cache whenever there is new review. Currently we support: <b>WP Super Cache, W3 Total Cache, Autoptimize, Cache Enabler, Breeze Cache, WP Fastest Cache, SG Optimizer</b>. <br> <br>

                    If your plugin is not in the list, please manually purge the cache daily, so the widgets will update.<br><br>

                    Judge.me widgets only support the following emojis ⌚ ⏩ ⏪ ⏫ ⏬ ⏰ ⏳ ⚽ ⛄ ⛅ ⛎ ⛔ ⛪ ⛲ ⛳ ⛵ ⛺ ⛽ ⬛ ⬜ ⭐ ⭕ ✂ ✅ ✊ ✋ ✨ ❌ ❎ ❓ ❔ ❕ ❗ ❤  so if your reviews contain other emojis this may prevent the widget from updating. Please remove them to allow the widgets to update again.<br><br>

                    Some other issues can prevent our widgets from updating correctly, read more about <a href= "https://support.judge.me/support/solutions/articles/44001923062--our-server-cannot-access-your-shop-domain-error-i-can-t-install-the-plugin-in-the-shop" target= "_blank">specific solutions to particular plugins</a>.
                  </div>
                </div>
              </div>
              <div class= "home-page__faq-post">
                <i class="fa fa-plus-circle fa-lg home-page__faq-icon"></i>
                <div class= "home-page__faq-info">
                  <div class= "home-page__faq-title">
                    I cannot access the Judge.me dashboard (judge.me/admin). How can I access it?
                  </div>
                  <div class= "home-page__faq-answer">
                    If you can access the Judge.me tab in your WooCommerce admin panel but are receiving an <b>"Oops Login Issue"</b> error message when clicking on the Get Started button to access Judge.me settings, it probably means that:<br><br>
                    <ul>
                      <li>Your WooCommerce account is the same but you <b>recently changed your domain</b>, so please check if you have recently changed your WordPress domain (‘www’ changes are also considered a change of domain). This change of domain is probably not updated in our servers yet so please contact support@judge.me so we can update your domain. </li>
                      <li>You <b>duplicated your shop</b> from a shop installed Judge.me. Any duplicated shop needs to be assigned to a different account in Judge.me, so please contact our support at <a href= "mailto:support@judge.me">support@judge.me</a> to solve this issue, as we’ll need to create a new account for the new shop by clearing the token.</li>
                    </ul>
                  </div>
                </div>
              </div>
              <div class= "home-page__faq-post">
                <i class="fa fa-plus-circle fa-lg home-page__faq-icon"></i>
                <div class= "home-page__faq-info">
                  <div class= "home-page__faq-title">
                    I do not see Judge.me widgets on my pages. How can I make them appear?
                  </div>
                  <div class= "home-page__faq-answer">
                    In WooCommerce, the Judge.me product widgets (Preview Badge and Review Widget on Product and Collection Pages) are already <b>automatically installed</b> in a default position that is valid for most of the themes.<br><br>
                    You can find the <b>Judge.me Widgets: Quick Installation</b> under the <b>General tab</b> of Judge.me dashboard in Woocommerce admin panel, please make sure all widgets are enabled.<br><br>
                    If you want to install other widgets, or if the product widgets don’t get installed in the desired position, you can <b>disable the toggles and manually install the widgets</b> using widget shortcodes. The widgets shortcodes are easier to install and position if your shop is using page builders such as Elementor or Divi.<br><br>
                    Additionally, we have the option to <b>change the default position</b> of the product widgets (Preview Badge and Review Widget on Product and Collection Pages) if you can provide us with the right <b>visual hook to the position</b> in which you want them to be installed. In this case:<br><br>
                    <ul>
                      <li>Ask your theme developer for the right name of the visual hook</li>
                      <li>Contact us at <a href= "mailto:support@judge.me">support@judge.me</a> so we can guide you through the process. We normally change the default position of these product widgets with the help of the plugin Code Snippets or by adding a hook to your template <b>functions.php</b> file.</li>
                    </ul>
                  </div>
                </div>
              </div>
              <div class= "home-page__faq-post">
                <i class="fa fa-plus-circle fa-lg home-page__faq-icon"></i>
                <div class= "home-page__faq-info">
                  <div class= "home-page__faq-title">
                    How can I add GTIN/EAN identifier to a product for the Google Product Review Feed?
                  </div>
                  <div class= "home-page__faq-answer">
                    Your products perhaps need a <b>GTIN/EAN identifier</b> if you want to use the Google Product Review Feed feature. This value can be added to your products by a custom attribute named <b>GTIN or EAN or ISBN</b>. Alternatively, you can use the plugin <b>WooCommerce UPC, EAN, and ISBN</b> to add GTIN values to your products.
                  </div>
                </div>
              </div>
              <div class= "home-page__faq-post">
                <i class="fa fa-plus-circle fa-lg home-page__faq-icon"></i>
                <div class= "home-page__faq-info">
                  <div class= "home-page__faq-title">
                    How can I add additional customizations using CSS?
                  </div>
                  <div class= "home-page__faq-answer">
                    If you wish to customize our widgets further you may add some CSS to style them. This can be done by going to your <b>WordPress Dashboard > Appearance > Customize > Additional CSS</b>.
                  </div>
                </div>
              </div>
              <div class= "home-page__faq-post">
                <i class="fa fa-plus-circle fa-lg home-page__faq-icon"></i>
                <div class= "home-page__faq-info">
                  <div class= "home-page__faq-title">
                    Will Judge.me affect my site's speed?
                  </div>
                  <div class= "home-page__faq-answer">
                    Judge.me is a fast plugin. We only load a CSS and Javascript file on your storefront to display the widgets and these files typically load within ~300ms.<br><br>
                    On the WordPress dashboard, we only load our Javascript files on the Judge.me plugin page so it will not affect your WordPress dashboard. For further information see our <a href= "https://support.judge.me/support/solutions/articles/44001699595-why-is-judge-me-so-fast-faq" target= "_blank">Why is Judge.me so fast?</a> article.<br><br>
                    We store all the reviews on our own server. In your shop, we only store <b>the first 5 reviews of each product</b> for caching purposes (so that your customers will see the reviews immediately after loading the product page, it actually improves your site speed overall). The subsequent reviews will be <b>retrieved dynamically from our servers</b> in the customer's browser and will not affect your site's performance.
                  </div>
                </div>
              </div>
              <div class= "home-page__faq-post">
                <i class="fa fa-plus-circle fa-lg home-page__faq-icon"></i>
                <div class= "home-page__faq-info">
                  <div class= "home-page__faq-title">
                    Product identification values to import reviews
                  </div>
                  <div class= "home-page__faq-answer">
                    If you receive errors regarding the products when trying to import reviews, please make sure that your product identification values included in your file are correct. Only one of the 2 values <b>(either product_handle or product_id)</b> is required to successfully import the reviews.<br><br>
                    Read more about <a href= "https://support.judge.me/support/solutions/articles/44001923058-product-identification-values-to-import-reviews" target= "_blank">How to identify product identification values correctly</a>.<br><br>
                    Please also make sure that your products are correctly synced, read more about <a href= "https://support.judge.me/support/solutions/articles/44001917145-my-woocommerce-products-are-not-synchronized-with-judge-me-how-can-i-synchronize-them-" target= "_blank">How to synchronize your products correctly</a>.
                  </div>
                </div>
              </div>
              <div class= "home-page__faq-post">
                <i class="fa fa-plus-circle fa-lg home-page__faq-icon"></i>
                <div class= "home-page__faq-info">
                  <div class= "home-page__faq-title">
                    “Our server cannot access your shop domain” error / I can’t install the plugin in the shop
                  </div>
                  <div class= "home-page__faq-answer">
                    To be able to install the plugins, your shop needs a <b>working domain (no localhost)</b> that is accessible from the Internet and not protected by passwords. Currently, we don’t support installing the application in a sub-directory, only the root domain is supported.<br><br>
                    Besides, there’s a number of issues that can arise when our plugin doesn’t have full access to your shop through our webhooks, but that can be also summarized in the plugin not being able to install, or the reviews or settings not updating correctly.<br><br>
                    Read more about <a href= "https://support.judge.me/support/solutions/articles/44001923062--our-server-cannot-access-your-shop-domain-error-i-can-t-install-the-plugin-in-the-shop" target= "_blank">specific solutions to particular plugins</a>.
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div id= "home-page__advanced-tab" class="tab-pane fade">
          <div class= "home-page__advanced-tab-container">
            <div class= "home-page-sychronize-products-container">
              <div class="judgeme-product-sync" data-token="<?php echo $token; ?>" data-domain="<?php echo get_option( 'judgeme_domain' );?>">
                <h3 class= "home-page__advanced-tab-titles">Synchronize WooCommerce products with Judge.me<span class= "home-page__customer-support-advanced-tab">Advanced</span></h3>
                <p>In order to use Product groups (multiple products share reviews), Shop sync (multiple shops share reviews),
                    or import reviews from other e-commerce platforms,
                    you must fully synchronize your WooCommerce products with Judge.me.
                    Please click the button below to synchronize products.</p>
                <p><strong>Notice:</strong> This process may take a long time if you have lots of products, please be patient.</p>
                <button class="sync-product-btn home-page__advanced-tab-sync-product">Synchronize Products</button>
                <button class="clear-sync-btn home-page__advanced-tab-reset-product-sync">Reset Synchronize Products Status</button>
                <div class= "product-sync-response"></div>
              </div>
            </div>
            <div class="judgeme-exporter" data-nonce="<?php echo wp_create_nonce( 'jgm_export_reviews' ); ?>">
              <h3 class= "home-page__advanced-tab-titles">Export WooCommerce Reviews</h3>
              <p>You can export the WooCommerce's reviews to CSV and import it to Judge.me.</p>
              <p><strong>Notice:</strong> This process may take a long time if you have lots of reviews, please be patient.</p>
              <?php $jgm_reviews_count = JGM_ReviewExporter::get_total_reviews_count(); ?>
              <?php if ( $jgm_reviews_count > 0 ): ?>
                <p>There are <?php echo $jgm_reviews_count; ?> reviews to be exported.</p>
                <button class="wp-core-ui button-primary export-review-btn">Export to CSV</button>
              <?php else: ?>
                <p>There are no reviews to be exported.</p>
              <?php endif; ?>
              <div class="response"></div>
              <a id="jgm-csv-file" style="display:none;"
                href="<?php echo admin_url( 'admin-post.php?action=jgm_download_file' ); ?>">Download the CSV file.</a>
              <p class="result">You can <a href="<?php echo $import_url; ?>" target="_blank">go here</a> to import the CSV to
                  Judge.me.</p>
            </div>
            <div>
            <h3 class= "home-page__advanced-tab-titles">Shop Token</h3>
              <p> Your shop domain: <?php echo get_option('judgeme_domain'); ?> </p>
              <p> Your internal token: <?php echo get_option('judgeme_shop_token');?> </p>
              <button class="clear-shop-token-btn home-page__advanced-tab-clear-shop-token">Clear shop's token</button>
            </div>
            <?php
              $skip_clearing_cache_by_default = get_option('judgeme_option_skip_clearing_cache_by_default');
            ?>
              <div class= "home-page__advanced-tab-clear-cache-option-container">
              <h3 class= "home-page__advanced-tab-titles">Cache clearing by Judge.me</h3>
                <div class= "advanced-tab__cache-clearing-toggle">
                  <label class="switch" for="cache-clearing-toggle">
                    <input <?php if(!$skip_clearing_cache_by_default) echo "checked" ?> type="checkbox" id="cache-clearing-toggle" data-type="judgeme_option_skip_clearing_cache_by_default" class= "advanced-tab__cache-clearing-widget-toggle-switch widget-toggle" />
                    <div class="slider round"></div>
                  </label>
                  <span class="home-page__advanced-tab-cache-clearing-disable-enable-text-toggle">We will clear cache by default</span>
                </div>
              </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php else: ?>
  <p>Our server cannot access your shop domain. Please note you need a working domain (no localhost) that is accessible from the internet
      and is not password-protected.</p>
  <p>Note: Currently we do not support wordpress setup in a sub-directory (eg: example.com/this-is-a-sub-directory/), only root directory is supported, e.g.: yourdomain.com or shop.yourdomain.com</p>
  <p> Check your setting, hosting or cloudflare to whitelist the /wp-json/* urls, it is needed for our system to contact your shop.</p>

  <?php
    if ( get_option( 'judgeme_is_installing' ) ) {
      delete_option( 'judgeme_is_installing' );
    }
  ?>

  <!--
  <?php print_r( get_option( 'judgeme_register_error' ) ); ?>
  -->
<?php endif; ?>
