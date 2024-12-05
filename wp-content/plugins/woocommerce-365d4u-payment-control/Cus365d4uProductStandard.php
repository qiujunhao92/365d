<?php

use \Automattic\WooCommerce\Blocks\Utils\BlockTemplateUtils;

if (!class_exists('Cus365d4uProductStandard')) {
    class Cus365d4uProductStandard
    {
        /**
         * Check Is Standard Product
         *
         * @return bool
         */
        protected function checkIsStandardProduct(): bool
        {
            return checkCurrentIsStandardDetail();
        }

        public function cus_get_pro_tabs($arrTabs)
        {
            $checkStandard = $this->checkIsStandardProduct();
            if ($checkStandard) {
                foreach ($arrTabs as $key => $tab) {
                    if ($key != 'description') {
                        unset($arrTabs[$key]);
                    }
                }
                if (isset($arrTabs['description'])) {
                    $arrTabs['description']['title'] = 'Product Description';
                }
                unset($arrTabs['reviews']);
                $arrTabs['ship_return'] = [
                    'title' => __('Shipping & Return', 'woocommerce'),
                    'priority' => 10,
                    'callback' => array($this, 'cus_ship_return'),
                ];
                $arrTabs['Warranty'] = [
                    'title' => __('Warranty', 'woocommerce'),
                    'priority' => 10,
                    'callback' => array($this, 'cus_warranty'),
                ];
            }
            return $arrTabs;
        }

        function cus_ship_return($key, $product_tab)
        {
            echo <<<HTML
 <div class="cus_tab_ship_ul">
   <div  class="baseshipTblCon">
        <table class="baseshipDevlivery">
            <tr class="cusshipTblheader"><td>Shipping method and time</td><td></td></tr>
            <tr class="cusshipTblnormal"><td>FedEx/DHL express shipping FREE : 4-7 days</td><td>Order more than $100</td></tr>
            <tr class="cusshipTblnormal"><td>FedEx/DHL express shipping $35 : 4-7 days</td><td>Order less than or equal $100</td></tr>
            <tr class="cusshipTblnormal"><td>USPS shipping FREE: 12-15 days</td><td>Order less than or equal $100</td></tr>
        </table>
   </div>
   <p>
       &nbsp;&nbsp;If customer addresses include PO BOX or APO/FPO, We only can use USPS Shipping need 12-15 days.
   </p>
   <div class="baseContainer">
       <p>
           &nbsp;&nbsp;Please note that you may incur customs duties and taxes on the items you purchase,
           and these fees are not charged or calculated by custom365d.
           Delivery to any country, including but not limited to; the US, Canada, Australia,
           or any European country Customers are responsible for any duties & taxes incurred once your parcel reaches the destination country.
       </p>
       <hr>
       <p>
           &nbsp;&nbsp;At custom365d, customer satisfaction is our top priority.
           If you are not completely satisfied with your purchase,
           please contact us and we will be happy to work with you.
       </p>
       <div>
           &nbsp;&nbsp;<b>If you just don&#39;t like or want to cancel,</b> Every piece of jewelry we create is 100% custom,
           which means we can&#39;t take it back and put it in stock for another customer. Because of that,
           <b>we don&#39;t offer full refund/return or cancellations
               after you&#39;ve approved your final proposal once start mold.</b> (because all design and 3d mock up, mold and production videos,
           final videos already sent to you for confirmation)
           and For custom designs, Once the design process has started, if you wish to cancel,
           we cannot issue a full refund because we have already paid the designer to begin creating your custom product.
           However, we can offer a partial refund of 30%-40% based on the circumstances.
       </div>
       <p>
           &nbsp;&nbsp;Please contact us by email at <a class="link rich-text-anchor __anchor-intercept-flag__ text-content-link emailcuslink" href="mailto:SUPPORT@CUSTOM365D.COM" data-eleid="8" data-mce-href="mailto:SUPPORT@CUSTOM365D.COM" contenteditable="false" target="_blank" rel="noopener">SUPPORT@CUSTOM365D.COM</a> and we will provide you with instructions on completing your refund.
       </p>

   </div>
</div>
<style>
   .single_variation_wrap{
      padding-bottom: 40px;
   }
    table.baseshipDevlivery{
       border-spacing: 0;
       border-right: white solid 1px;
       border-bottom: white solid 1px;
        font-size:14px;
        width: 100%;
    }
    table.baseshipDevlivery td{
        border-top: white solid 1px;
        border-left: white solid 1px;
        padding: 7px;
    }
    div.cus_tab_ship_ul b,div.cus_dv_warranty b{        
       color: white;
    }
    .baseshipTblCon{
      margin-top:30px;
    }
    .single_variation_wrap .single_variation .woocommerce-variation-price{
       display: none;
    }
     .single_variation_wrap .single_variation .woocommerce-variation-description{
       display: none;
    }
</style>
HTML;

        }

        function cus_warranty($key, $product_tab)
        {
            echo <<<HTML
  <div class="cus_dv_warranty">
       <p class="cus_first_title" style="padding-left:20px;">
          <b>1.5 Years Warranty</b>
        </p>     
       <ol class="cus_dv_warranty_items_ul">
          <li class="cus_warranty_first">
             All items come with a 1.5 years limited warranty against substantive manufacturing defects in materials or workmanship, 
             and normal wear and tear. Customer negligence is not covered.  custom365d is not responsible for incidental damage on items.
              Which means if there is any problem during normal use in 1.5 years of the delivery date, 
              You can ship it back to us we will fix for you (Please contact our Customer service to get the return address)You can email <a class="link rich-text-anchor __anchor-intercept-flag__ text-content-link emailcuslink" href="mailto:SUPPORT@CUSTOM365D.COM" data-eleid="8" data-mce-href="mailto:SUPPORT@CUSTOM365D.COM" contenteditable="false" target="_blank" rel="noopener">SUPPORT@CUSTOM365D.COM</a> and we will provide you how to ship back.

          </li>
          <li class="cus_warranty_second">
             Regarding fading, we only offer one free electroplating service in 1.5 years of the delivery date.
              If more than one service is needed, we will determine the charge based on the complexity of the custom product.
               Please contact our customer service for confirmation.
         </li>
       </ol> 
</div>
HTML;

        }

        public function cus_hide_product_heading($header)
        {
            return '';
        }


        protected function getArrowUpSpan($isHide)
        {
            $showStyle = $isHide ? '' : ' hidden-option';

            return <<<HTML
         <span class="spanright arrow-right{$showStyle}">
                <svg width="10" height="10" viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg">
                   <g clip-path="url(#clip0_4263_34997)">
                       <path d="M5.62683 7.96972L9.74145 3.60013C9.99641 3.32758 10.0668 2.93194 9.93487 2.58027C9.7942 2.22859 9.47769 2 9.11722 2L0.887984 2C0.527515 2 0.211006 2.22859 0.0703355 2.58027C0.0263759 2.69456 -3.57406e-08 2.81765 -4.11209e-08 2.94074C-5.18815e-08 3.18691 0.0879194 3.42429 0.263758 3.60013L4.37838 7.96972C4.54542 8.14555 4.77401 8.24226 5.0026 8.24226C5.23999 8.25106 5.45978 8.14555 5.62683 7.96972Z" fill="white">
                       </path>
                  </g>
                    <defs>
                        <clipPath id="clip0_4263_34997">
                          <rect width="10" height="10" fill="white" transform="matrix(-4.37114e-08 1 1 4.37114e-08 0 0)">
                          </rect>
                        </clipPath>
                    </defs>
                </svg>
          </span>
HTML;
        }

        protected function getArrowDownSpan($isHide)
        {
            $showStyle = $isHide ? ' hidden-option' : '';
            return <<<HTML
           <span class="spanright arrow-bottom{$showStyle}">
               <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_4263_34997)">
                   <path d="M5.62683 2.03028L9.74145 6.39987C9.99641 6.67242 10.0668 7.06806 9.93487 7.41973C9.7942 7.77141 9.47769 8 9.11722 8L0.887984 8C0.527515 8 0.211006 7.77141 0.0703355 7.41973C0.0263759 7.30544 -3.57406e-08 7.18235 -4.11209e-08 7.05926C-5.18815e-08 6.81309 0.0879194 6.57571 0.263758 6.39987L4.37838 2.03028C4.54542 1.85445 4.77401 1.75774 5.0026 1.75774C5.23999 1.74894 5.45978 1.85445 5.62683 2.03028Z" fill="white">
                    </path>
                </g>
                <defs>
                   <clipPath id="clip0_4263_34997">
                      <rect width="10" height="10" fill="white" transform="translate(0 10) rotate(-90)"></rect>
                    </clipPath>
                </defs>
              </svg>
          </span>
HTML;
        }

        /**
         * Get Custom Div
         *
         * @param $customizeOption
         * @param $upOrDown
         * @return string
         */
        protected function getCustomDivCon($customizeOption, $upOrDown): string
        {

            $customPrice = '$' . ($customizeOption['display_price'] ?? 100);
            $cusDescription = $this->getCustomDESC();
             if (!empty($customizeOption['description'])) {
                $cusDescription = $customizeOption['description'];
            }
            $mode = $upOrDown ? 'on' : 'off';
            $optionDescShow = $upOrDown ? 'block' : 'none';
            $cusDescription = str_replace('[PRICE]', $customPrice, $cusDescription);
            $arrowShow = $this->getArrowUpSpan($upOrDown) . $this->getArrowDownSpan($upOrDown);
            return <<<PHTML
<div class="baseOptionCus baseUpCustimize">
     <div class="curOptionTitle" cus-option="custom" cus-mode="{$mode}">
           <span>Customized option: First Deposit {$customPrice}</span>
          {$arrowShow}    
      </div> 
      <div class="ccdesc customizedesc" style="display: {$optionDescShow};">{$cusDescription}</div>
</div>
PHTML;

        }

        protected function getCustomDESC()
        {
            return 'This [PRICE] payment is included in the total price. If you choose this option, we will have a dedicated customer service representative to work with you one-on-one to confirm the details of your custom product, the total price, and the service process details.';
        }

        protected function getNonCustomDESC()
        {
            return 'This payment is 50% of the total price. Within 7-10 working days, we will provide all the shooting details for confirmation, and the remaining balance will be paid before shipping';
        }

        /**
         * Get Non custom div
         *
         * @param $nonDescription
         * @param $upOrDown
         * @return string
         */
        protected function getNonCustomDivCon($nonDescription, $upOrDown): string
        {
            $description = $this->getNonCustomDESC();
             if (!empty($nonDescription)) {
                $description = $nonDescription;
            }
            $arrowShow = $this->getArrowUpSpan($upOrDown) . $this->getArrowDownSpan($upOrDown);
            $mode = $upOrDown ? 'on' : 'off';
            $optionDescShow = $upOrDown ? 'block' : 'none';
            return <<<PHTML
   <div class="baseOptionCus baseUpNonCustimize">
         <div class="curOptionTitle"  cus-option="noncustom" cus-mode="{$mode}"><span>Non-Customized option</span> 
             {$arrowShow}
         </div>
         <div class="ccdesc noncustomizedesc"  style="display: {$optionDescShow};">{$description}</div>
   </div>
PHTML;
        }

        public function preload_css_script()
        {
            $isStandard = $this->checkIsStandardProduct();
            if ($isStandard) {
                echo <<<HTML
  <!-- Start of Judge.me Core TEST -->
  <link rel="stylesheet" type="text/css" media="all" href="https://cdn.judge.me/widget_v3/base.css"> 
 <link rel="stylesheet" type="text/css" media="all" href="https://cdn.judge.me/widget_v3/main.css">
 <!-- END of Judge.me Core TEST -->
HTML;

            }

        }

        public function cus_before_variations()
        {
            global $product;
            //  $attributes =  $product->get_variation_attributes() ?? [];
            $available_variations = $product->get_available_variations() ?? [];
            $customizeOption = null;
            $otherNonOptionList = [];
            $nonCustomDesc = null;
            $globalProductType = 1;  // custom product and standard product could be selected
            $checkStandard = $this->checkIsStandardProduct();
            foreach ($available_variations as $variation) {
                $optionAttrs = array_values($variation['attributes'] ?? []);
                $optionAttrs = array_map(function ($v) {
                    return $v && is_string($v) ? trim($v) : '';
                }, $optionAttrs);
                //check all option value is 'CUSTOM'
                $optionAttrs = array_unique($optionAttrs);

                if (count($optionAttrs) == 1 && $optionAttrs[0] == 'CUSTOM') {
                    $customizeOption = $variation;
                } else {
                    $otherNonOptionList[] = $variation;
                    if (empty($nonCustomDesc)) {
                        $nonCustomDesc = $variation['description'] ?? '';
                    }
                }

            }
            if (empty($customizeOption)) {
                if (empty($otherNonOptionList)) {
                    return;
                } else {
                    $globalProductType = 1;  // only show standard product
                }
            }  else {  //not custom project
                if (empty($otherNonOptionList)) {
                    $globalProductType = 0;  // only show custom product
                } else {
                    $globalProductType = 2;  // custom product and standard product could be selected
                }
            }

            $curType = $_GET['type'] ?? '';
            $current_url = get_permalink();
            $query_string = $_SERVER['QUERY_STRING'];

            $defaultAction = 'updateCusCtrlStatus("on", true, true);';

            if ($query_string) {
                if (str_contains($query_string, 'attribute_') && str_contains($query_string, '=CUSTOM')) {
                    $globalProductType = 0;  // only show custom product
                } elseif (str_contains($query_string, 'attribute_')) {
                    $globalProductType = 1;  // only show standard product
                }
            }

            if ($globalProductType == 0) {
                $defaultAction = 'currentSelect = "custom"; updateCusCtrlStatus("on", false, true);';
                $customizeDiv = $this->getCustomDivCon($customizeOption, true);
                $nonCustomizeDiv = '';
            } elseif ($globalProductType == 1) {
                $defaultAction = 'currentSelect = "noncustom"; updateCusCtrlStatus("off", false, true);';
                $defaultAction .= "document.querySelector('table.variations').style.display = 'block';";
                $customizeDiv = '';
                $nonCustomizeDiv = $this->getNonCustomDivCon($nonCustomDesc, false);
            } else {
                $customizeDiv = $this->getCustomDivCon($customizeOption, false);
                $nonCustomizeDiv = $this->getNonCustomDivCon($nonCustomDesc, false);
            }


            $jgmPreviewCode = do_shortcode('[jgm-all-reviews]');
            $ratingCode = do_shortcode('[jgm-review-rating] out of 5 based on [jgm-review-count]');

            $optionSetScript = <<<SCRIPT
<script type="text/javascript">
  var jq = jQuery.noConflict();
  var currentSelect = ''; 
  var productType = '{$globalProductType}';
  var checkStandard='{$checkStandard}';
  
  function updateArrowStyle(mode, containerBase)
  {
       jq(containerBase + '  .curOptionTitle .arrow-right').removeClass('hidden-option');
       jq(containerBase + '  .curOptionTitle .arrow-bottom').removeClass('hidden-option');
       if (mode === 'off') {
            jq(containerBase + '  .curOptionTitle .arrow-right').addClass('hidden-option'); 
        } else {
            jq(containerBase + '  .curOptionTitle .arrow-bottom').addClass('hidden-option');
        }
  }
  
  function updateCusCtrlStatus(customizeMode, sameShowCusAndNonCus, bInit)
  {
      let customizeDisplay =  (customizeMode === 'on' ? 'block' : 'none'),
          nonCustomizeDisplay = customizeDisplay,
          nonCustomizeMode = customizeMode;
      if (!sameShowCusAndNonCus) {
          nonCustomizeDisplay = (customizeMode === 'on' ? 'none' : 'block'); 
          nonCustomizeMode = (customizeMode === 'on' ? 'off' : 'on');
      } else {
          nonCustomizeMode = customizeMode = 'off';
          nonCustomizeDisplay = customizeDisplay = 'block';
      }
      jq('.baseUpNonCustimize').css('display', nonCustomizeDisplay); 
      jq('.baseUpCustimize').css('display', customizeDisplay);
         
     //update cus mode
     jq('.baseUpCustimize .curOptionTitle').attr('cus-mode', customizeMode);
     jq('.baseUpNonCustimize .curOptionTitle').attr('cus-mode',customizeMode);
     jq("table.variations").css('display', 'none');
     if (sameShowCusAndNonCus) {
         jq('.noncustomizedesc').css('display', 'none'); 
         jq('.customizedesc').css('display',  'none');      
         jq('table.variations td.value select').prop('selectedIndex', -1);  
         jq('form.variations_form.cart').trigger("reset_data");
     } else {
          jq('.noncustomizedesc').css('display', nonCustomizeDisplay); 
          jq('.customizedesc').css('display', customizeDisplay); 
          if (nonCustomizeMode === 'on') {
              jq("table.variations").css('display', 'block');     
              if (!bInit) {     
                  //no init need to reset data
                  jq('table.variations td.value select').val('');  
                  jq('form.variations_form.cart').trigger("update_variation_values");
                  jq('form.variations_form.cart').trigger("reset_data");     
              }
              hideCustomOptionInSelect();           
          } else {
              jq('form.variations_form.cart').trigger("update_variation_values");
              var allSelect =  jq("table.variations td.value select");
              if (allSelect.length >0) {
                   allSelect.val('CUSTOM').trigger('change');
              }             
          }         
     }
     //update Arrow style
     updateArrowStyle(customizeMode, '.baseUpCustimize');
     updateArrowStyle(nonCustomizeMode, '.baseUpNonCustimize'); 
  }
  
  function hideCustomOptionInSelect()
  {
       jq('table.variations td.value select option[value="CUSTOM"]').removeClass('hidden-option').addClass('hidden-option');
  }
  
  function updatePriceTopHtml()
  {
       var html = jq('.single_variation_wrap .woocommerce-variation-price').html();  
       if (html) {
            jq(".wp-block-woocommerce-product-price>.wc-block-components-product-price").html(html);
       }
      
  }
   
  jq(document).ready(function (jq) {
      const sourcePrice = jq('.wp-block-woocommerce-product-price>.wc-block-components-product-price').html();
      let firstRun = true;      
      {$defaultAction}   
      jq('.curOptionTitle').click(function (){
          if (productType ==='0' || productType ==='1') {
              //customize product and standard product could not change
              return;
          }
          var option = jq(this).attr('cus-option'),
              mode = jq(this).attr('cus-mode');
          if (currentSelect) {
               currentSelect = '';
               updateCusCtrlStatus('off', true, false);
          } else if(option ==='noncustom') {
               currentSelect = option;             
               updateCusCtrlStatus('off', false, false); 
          } else {
               currentSelect = option;
               updateCusCtrlStatus('on', false, false); 
          }
      });
      
      jq('form.variations_form.cart').on('reset_data', function () {
           hideCustomOptionInSelect();
            jq(".wp-block-woocommerce-product-price>.wc-block-components-product-price").html(sourcePrice);
      })
      jq("form.variations_form.cart").on('woocommerce_variation_has_changed', function () {
           hideCustomOptionInSelect();
           updatePriceTopHtml();
      })
      jq('form.variations_form.cart input[name="variation_id"], input.variation_id').on('change', function () {
           if (firstRun) {
               updatePriceTopHtml();
           }
           firstRun = false;
      })
      
      jq(".popup-trigger").on("click", function(e) {
          e.preventDefault(); 
        jq("#custom-popup").fadeIn();
      });

      jq(".popup-close").on("click", function() {
           jq("#custom-popup").fadeOut(); 
      });
      jq('#popup-content').on('click', 'a.jdgm-write-rev-link', function() {     
           jq('.jdgm-all-reviews__header').css("display", "none");
           jq('#custom-popup .wp-block-post-title').css("display", "none");
      })
 });
</script>         
SCRIPT;

            $backgroundColor = 'rebeccapurple';
            $borderColor = '#d7d7d7bc';
            echo <<<HTML
    <div class="baseSiteStar">
      <span class="jdgm-all-reviews__summary-stars" tabindex="0" aria-label="Average rating is 4.98" role="img"> <span class="jdgm-star jdgm--on"></span><span class="jdgm-star jdgm--on"></span><span class="jdgm-star jdgm--on"></span><span class="jdgm-star jdgm--on"></span><span class="jdgm-star jdgm--on"></span></span>
       <a href="#" class="popup-trigger">
          {$ratingCode}
       </a>
   </div>
   <div class="baseAllmizeContainr">
       {$nonCustomizeDiv}
       {$customizeDiv}
   </div>
   <div id="custom-popup" style="display: none;">
       <div class="judgeTitle">
         <span class="popup-close">✕</span>               
       </div>
       
        <div id="popup-content">
             <h2 class="wp-block-post-title">CUSTOMER REVIEWS</h2>    
            {$jgmPreviewCode}
         </div>
   </div>
   <style>
      table.variations{
         display: none;
      }
      #custom-popup .wp-block-post-title{
         text-align: center;
      }
      .baseSiteStar{
         padding-bottom: 40px;
      }       
       #custom-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 0 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            z-index: 1000;
            width: 80vw; 
            max-width: 1180px;
            color: black;   
            max-height:95vh;
        }
        #custom-popup .jdgm-form {
              max-width:100%;
              max-height:60vh;
              overflow:auto;
        }
       @media (max-width: 1023px) {
            #custom-popup h2.wp-block-post-title{
               font-size:28px;
               line-height:50px;
            } 
            
        }
        @media (min-width: 728px) {
             #custom-popup .jdgm-rev__content{ 
                 width: 60%;
                 margin-left: 32%;
                 margin-top: -80px; 
            }           
            
        }
        #custom-popup .jdgm-form__fieldset input[name='yt_url']{
            display:none;
        }
        @media only screen and (min-width: 1300px) {
              div.baseSiteStar { 
                   margin-top: -66px;
                    text-align: right;
                    width: 100%
               }
        }
        @media only screen and (max-width: 767px) {
             #custom-popup {
                width: 90%;
             }
             #custom-popup .jdgm-form { 
                  max-height:45vh; 
             } 
             .popup-close { 
                position: fixed;
                float: right;
                width: 94%;
                margin-top:10px;
                padding-right: 20px;
             } 
             #popup-content .wp-block-post-title{
                margin-top: 50px;
             }
             #custom-popup{
                 max-height:98vh;
             }
        }
        #popup-content .jdgm-shop-reviews__body{
            height: 40vh;
            max-height: 500px;
            overflow: auto;   
            padding-right: 10px;
            margin-bottom: 20px;
        }
        #popup-content .jdgm-all-reviews__footer{
           display: none;
        }
        #custom-popup .popup-close {
            cursor: pointer;
            display: block;
            text-align: right;
            font-size: 20px;
            margin-bottom: 10px;
        }
        .single_variation_wrap{
           display: block;
        }
        .hidden-option {
            display: none;
        }
        .cus-support-detail>div.ace-line:first-child, .cus-support-detail>div.ace-line:nth-child(2){
           display: none;
        }
         .alignwide p.product__text.cusSubtitle {
            display: none;
         }
         .baseOptionCus{
            margin-bottom:20px;
            cursor:pointer;
         }
         .spanright {
            float: right;
         }
         .ccdesc{
            font: 14px Lato, sans-serif;
            color: {$borderColor};
            line-height: 25px;
            text-transform: uppercase;
            margin-left: -10px;
            padding: 10px;
            margin-right: -12px;
            border: {$borderColor} 1px solid;
            border-top: none;
         }
         .curOptionTitle{
           width: 100%;
            padding: 10px 10px 10px 10px;
            background-color: {$backgroundColor};
            margin-left: -10px;
            border: {$borderColor} 1px solid;
        }
         .woocommerce table.variations tr th {
             min-width: 60px;
             text-align: left;
         }         
   </style>
   {$optionSetScript}
HTML;

        }

        public function cus_ajax_variation_threshold($count, $product)
        {
            //product sku<2000, never ajax
            return 2000;
        }

        public function cus_add_Custom_variation_button()
        {
            $nonce = wp_create_nonce('custom_admin_nonce');
            $ajaxUrl = admin_url('admin-ajax.php');
            $current_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $current_url = preg_replace('/[&\?]a_.*?=.*?[&$]/ius', '', $current_url);
            global $product_object;
            $sFilterHtml = '';
            if (!empty($product_object)) {
                $variant_attributes = $product_object->get_attributes();
                if (!empty($variant_attributes)) {
                    $sFilterHtml = '<div style="padding: 10px 10px 0 12px;">';
                    foreach ($variant_attributes as $attribute) {
                        if ($attribute->is_taxonomy()) {
                            // 处理分类属性
                            $taxonomy = $attribute->get_name();
                            $terms = get_terms(array(
                                'taxonomy' => $taxonomy,
                                'hide_empty' => false,
                            ));
                            $name = wc_attribute_label($taxonomy ); // 获取显示名称
                            $source = $taxonomy;
                            $options = [];
                            foreach ($terms as $term) {
                                $options[] = $term->name;
                            }
                        } else {
                            $name = $attribute->get_name();
                            $source = $name;
                            $options = $attribute->get_options();
                        }
                        $sFilterHtml .= '<span>'. $name . ':</span>';

                        $sFilterHtml .= '<select class="filterSel" name="a_' . $name . '" source="' . $source . '" style="margin-right:15px;">';
                        $sFilterHtml .= '<option value=""></option>';
                        foreach ($options as  $option) {
                            $selOption = $_GET['a_' . $name] ?? '';
                            $selOption = str_replace('\\', '', $selOption);
                            if ($selOption == $option) {
                                $sFilterHtml .= '<option value="' . htmlentities($option) . '" selected="selected">' . $option . '</option>';
                            } else {
                                $sFilterHtml .= '<option value="' . htmlentities($option) . '">' . $option . '</option>';
                            }
                        }
                        $sFilterHtml .= '</select>';
                    }
                    $sFilterHtml .= '<button id="filter_variant" class="button">Filter Variants</button>';
                    $sFilterHtml .= '<span style="margin:0 10px;">Bulk Update:</span>';
                    $sFilterHtml .= '<select id="buildUpdatePrice">';
                    $sFilterHtml .= '<option value=""></option>';
                    $sFilterHtml .= '<option value="regular_price">Set Regular Price</option>';
                    $sFilterHtml .= '<option value="sale_price">Set Sale Price</option>';
                    $sFilterHtml .= '<option value="delete_filtered">Delete Filtered Variations</option>';
                    $sFilterHtml .='</select>';
                    $sFilterHtml .= "</div>";
                }
            }

            echo <<<HTML
 <div style="padding: 10px 10px 0 12px;">
    <button id="add-custom-variation" class="button button-primary">Add Custom Variation</button>
    <button id="remove-custom-variation" class="button button-primary">Remove All Custom Variation</button>   
 </div>
 {$sFilterHtml}
 <script type="text/javascript">
   var lockButton =  false;
   jQuery(document).ready(function($) {
    // 处理按钮点击事件
    $('#add-custom-variation').on('click', function(e) {
        e.preventDefault();
        if (lockButton) {
            alert('Processing, please wait a moment.');
            return;
        }
        lockButton = true;

        const data = {
            action: 'cwcv_add_custom_variation',
            product_id: $('#post_ID').val(),
            security: '{$nonce}'
        };

        $.post('{$ajaxUrl}', data,  function(response) {            
            if (response.success) {
                // 成功处理，可以刷新页面或者其他操作
                alert('Custom variation added successfully.');
                 // 成功处理，异步刷新变体列表
                location.reload(true);
            } else if (response.data && response.data.message) {
                  alert(response.data && response.data.message);
            } else {
                // 处理失败
                alert('Failed to add variation.');
            }
            lockButton = false;
        });
    });
    $('#remove-custom-variation').on('click', function(e) {
        e.preventDefault();
         if (lockButton) {
            alert('Processing, please wait a moment.');
            return;
        }
        lockButton = true;
        const data = {
            action: 'cwcv_remove_custom_variation',
            product_id: $('#post_ID').val(),
            security: '{$nonce}'
        };

        $.post('{$ajaxUrl}', data,  function(response) {          
            if (response.success) {
                // 成功处理，可以刷新页面或者其他操作
                alert('Custom variation removed successfully.');
                 // 成功处理，异步刷新变体列表
                location.reload(true);
            } else if (response.data && response.data.message) {
                  alert(response.data && response.data.message);
            } else {
                // 处理失败
                alert('Failed to remove variation.');
            }
            lockButton = false;
        });
    }); 
  
    
    function refreshFilterVarient(e, fieldName, fieldValue)
    {
        e.preventDefault();    
        let isNotEmpty = false;
        let metaData = {};
        $('select.filterSel').each(function() {
              const name =  $(this).attr('source');  
              const val = $(this).val();
              if (val.trim() !== '') {
                  isNotEmpty = true; 
                  metaData[name] = val;
              } 
        });   
        let data = {
            action: 'cwcv_custom_load_variations',
            product_id: $('#post_ID').val(),
            security: '{$nonce}',
            meta : metaData
        };
        if (fieldName) {
            data.action = 'cwcv_custom_bulk_update_variations';
            data.batchName = fieldName;
            data.batchValue = fieldValue;
        }
    	$( '#woocommerce-product-data' ).block( {
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6,
				},
	    });
        $.post('{$ajaxUrl}', data,  function(response) {
              // 成功处理，可以刷新页面或者其他操作
            var wrapper = $( '#variable_product_options' ).find(
                '.woocommerce_variations'
            );
            const prodData = $('#woocommerce-product-data');
            wrapper.empty()
                        .append( response )
                        .attr( 'data-page', 1 );
            prodData.trigger(
                  'woocommerce_variations_loaded'
            ); 
             prodData.unblock();
             if (!isNotEmpty) {
                 $('#variable_product_options .variations-pagenav').css('display', 'block');
             } else {
                 $('#variable_product_options .variations-pagenav').css('display', 'none');
             }
        });
    }
    
    
    $("#filter_variant").on('click', function(e) { 
        refreshFilterVarient(e, null, null);       
    })
    $('#buildUpdatePrice').change(function (e){
         var name = $('#buildUpdatePrice').val();
         if (!name) {
             return;
         }
         switch (name) {
             case 'regular_price':
             case 'sale_price':
                   var price = prompt('Please Enter the price');
                     var floatPrice = parseFloat(price);
                     if (isNaN(floatPrice)) {
                         alert('Please input a valid price');
                     }
                    refreshFilterVarient(e, name, floatPrice);    
                    break;
            case  'delete_filtered':
                if (confirm('Are you sure you want to delete this filtered variations? This cannot be undone.')) {
                    refreshFilterVarient(e, name, 1);   
                }   
                break;
         }
       
    })
});
</script>
HTML;

        }

        /**
         * Load variations via AJAX.
         */
        public function cwcv_custom_load_variations_ajax() {
            $this->getAjaxUpdate(null, null);
        }

        public function cwcv_custom_bulk_update_variations_ajax() {
            $fieldName = $_POST['batchName'] ?? null;
            $fieldValue = $_POST['batchValue'] ?? null;
            $this->getAjaxUpdate($fieldName, $fieldValue);
        }

        protected function getAjaxUpdate($fieldName, $fieldValue) {
            ob_start();

            $meta = $_POST['meta'] ?? [];
            if ( empty( $_POST['product_id'] ) ) {
                wp_die( -1 );
            }

            // Set $post global so its available, like within the admin screens.
            global $post;

            $loop           = 0;
            $product_id     = absint( $_POST['product_id'] );
            $post           = get_post( $product_id ); // phpcs:ignore
            $product_object = wc_get_product( $product_id );
            $defaultPage = empty($meta) ? 10 : 1000;
            $per_page       = ! empty( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : $defaultPage;
            $page           = ! empty( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
            $query_args     =     array(
                'status'  => array( 'private', 'publish' ),
                'type'    => 'variation',
                'parent'  => $product_id,
                'limit'   => $per_page,
                'page'    => $page,
                'orderby' => array(
                    'menu_order' => 'ASC',
                    'ID'         => 'DESC',
                ),
                'return'  => 'objects',
            );
            $variations     = wc_get_products(
                $query_args
            );
            $arrNewVariations = [];
            foreach ($variations as $variation) {
                $attributes = $variation->get_attributes();
                $bAllFitCond = true;
                foreach ($meta as $key => $value) {
                    $value = html_entity_decode($value);
                    $value = str_replace('\\', '', $value);
                    foreach ($attributes as $attr_name => $attr_value) {
                        if (strtolower($key) == strtolower($attr_name) && strtolower($value) != strtolower($attr_value)) {
                            $bAllFitCond = false;
                            break;
                        }
                    }
                }
                if (empty($meta) || $bAllFitCond) {
                    if ($fieldName == 'delete_filtered') {
                        $variation->delete(true);  //delete all
                    } else {
                        if (!empty($fieldName)) {
                            $variation->{ "set_$fieldName" }( wc_clean( $fieldValue ) );
                            $variation->save();
                        }
                        $arrNewVariations[] = $variation;
                    }
                }
            }
            if (!empty($fieldName)) {
                WC_Product_Variable::sync($product_id);
                wc_delete_product_transients($product_id);
            }
            $variations = $arrNewVariations;
            if ($variations ) {
                wc_render_invalid_variation_notice( $product_object );
                foreach ( $variations as $variation_object ) {
                    $variation_id   = $variation_object->get_id();
                    $variation      = get_post( $variation_id );
                    $variation_data = array_merge( get_post_custom( $variation_id ), wc_get_product_variation_attributes( $variation_id ) ); // kept for BW compatibility.
                    include dirname(__DIR__) . '/woocommerce/includes/admin/meta-boxes/views/html-variation-admin.php';
                    $loop++;
                }
            }
            wp_die();
        }


        function cwcv_add_custom_variation_ajax() {

            $product_id = intval($_POST['product_id']);
            $product = wc_get_product($product_id);

            if ($product && $product->is_type('variable')) {
                $attributes = $product->get_attributes();
                $custom_attributes = array();
                $hadAdd = false;
                // 遍历每个属性并添加 "CUSTOM" 选项（如不存在）
                foreach ($attributes as $attribute) {
                    if ($attribute->is_taxonomy()) {
                        // 处理分类属性
                        $taxonomy = $attribute->get_name();
                        $terms = get_terms(array(
                            'taxonomy' => $taxonomy,
                            'hide_empty' => false,
                        ));
                        $customOptionId = 0;
                        $options =   $attribute->get_options();
                        $has_custom = false;
                        foreach ($terms as $term) {
                            if (strtolower(trim($term->name)) == 'custom') {
                                $has_custom = true;
                                $customOptionId = $term->term_id;
                                break;
                            }
                        }
                        if (!$has_custom) {
                            $newTerm = wp_insert_term('CUSTOM', $taxonomy);
                            if (isset($newTerm['term_id'])) {
                                $customOptionId =  $newTerm['term_id'];
                            }
                        }
                        if ($customOptionId &&!in_array($customOptionId, $options)) {
                            $options[] = $customOptionId;
                            $attribute->set_options($options);
                            $hadAdd = true;
                        }
                        // 在属性值中设置 'CUSTOM'
                        $custom_attributes[$taxonomy] = 'custom';
                    } else {
                        // 处理非分类法属性
                        $options = $attribute->get_options();
                        $has_custom = false;

                        foreach ($options as $option) {
                            if (trim($option) == 'CUSTOM') {
                                $has_custom = true;
                                break;
                            }
                        }

                        if (!$has_custom) {
                            $options[] = 'CUSTOM';
                            $attribute->set_options($options);
                            $hadAdd = true;
                        }

                        // 在属性值中设置 'CUSTOM'
                        $custom_attributes['attribute_' . sanitize_title($attribute->get_name())] = 'CUSTOM';
                    }
                }
                if ($hadAdd) {
                    $product      = new WC_Product_Variable( $product_id );
                    $product->set_attributes( $attributes );
                    $product->save();
                }
                // 检查是否已经存在所有属性值为 'CUSTOM' 的变体
                $existing_variations = $product->get_children();
                $variation_exists = false;

                foreach ($existing_variations as $variation_id) {
                    $variation = wc_get_product($variation_id);
                    $variation_data = $variation->get_attributes();
                    $match = true;

                    foreach ($custom_attributes as $attr_name => $attr_value) {
                        if (!isset($variation_data[$attr_name]) || strtolower($variation_data[$attr_name]) !== 'custom') {
                            $match = false;
                            break;
                        }
                    }

                    if ($match) {
                        $variation_exists = true;
                        break;
                    }
                }

                if ($variation_exists) {
                    wp_send_json_error(array('message' => 'A variation with all attributes set to CUSTOM already exists.'));
                } else {
                    // 如果不存在，创建新的变体
                    $variation_data = array(
                        'attributes'      => $custom_attributes,
                        'regular_price'   => '100',
                        'sale_price'      => '',
                        'stock_qty'       => 0,  // 将库存设置为0
                        'backorders'      => 'yes',  // 设置允许缺货订购
                        'stock_status'    => 'onbackorder',  // 设置库存状态为缺货
                    );
                    $variation_id = $this->cwcv_create_product_variation($product_id, $variation_data);
                    if ($variation_id) {
                        wp_send_json_success();
                    } else {
                        wp_send_json_error(array('message' => 'Failed to create variation_data'));
                    }
                }
            } else {
                wp_send_json_error(array('message' => 'Only variable could add custom option'));
            }
        }

        function cwcv_remove_custom_variation_ajax() {

            $product_id = intval($_POST['product_id']);
            $product = wc_get_product($product_id);

            if ($product && $product->is_type('variable')) {
                // 获取商品的所有变体
                $variations = $product->get_children();
                $removed = 0; // 计数已删除的变体数量

                foreach ($variations as $variation_id) {
                    $variation = wc_get_product($variation_id);
                    if (!$variation) {
                        continue;
                    }

                    // 获取变体的所有属性
                    $variation_attributes = $variation->get_attributes();
                    $had_toDelete = false;

                    // 检查变体的每个属性是否为 "CUSTOM"
                    foreach ($variation_attributes as $attr_name => $attr_value) {
                        if (empty($attr_value) || trim(strtolower($attr_value)) === 'custom') {
                            $had_toDelete = true;
                            break;
                        }
                    }
                    // 如果所有属性均为 "CUSTOM"，则删除该变体
                    if ($had_toDelete) {
                        wp_delete_post($variation_id, true); // 第二个参数 true 表示强制删除，不进入回收站
                        $removed++;
                    }
                }
                // 如果没有找到符合条件的变体，返回错误信息
                if ($removed === 0) {
                    wp_send_json_error(array('message' => 'No Custom Variations found to remove.'));
                } else {
                    wp_send_json_success();
                }
            } else {
                wp_send_json_error(array('message' => 'Only variable could remove custom option'));
            }
        }

        function cwcv_create_product_variation($product_id, $variation_data) {
            $variation_post = array(
                'post_title'  => 'Variation #' . $product_id,
                'post_name'   => 'product-' . $product_id . '-variation',
                'post_status' => 'publish',
                'post_parent' => $product_id,
                'post_type'   => 'product_variation',
                'guid'        => home_url() . '/?product_variation=product-' . $product_id . '-variation',
            );

            // 创建变体
            $variation_id = wp_insert_post($variation_post);

            // 保存变体数据
            if ($variation_id) {
                $variation = new WC_Product_Variation($variation_id);

                // 设置价格
                if (isset($variation_data['regular_price'])) {
                    $variation->set_regular_price($variation_data['regular_price']);
                }
                if (isset($variation_data['sale_price'])) {
                    $variation->set_sale_price($variation_data['sale_price']);
                }

                // 设置库存数量
                if (isset($variation_data['stock_qty'])) {
                    $variation->set_stock_quantity($variation_data['stock_qty']);
                }

                // 设置是否允许缺货订购
                if (isset($variation_data['backorders'])) {
                    $variation->set_backorders($variation_data['backorders']);
                }

                // 设置库存状态
                if (isset($variation_data['stock_status'])) {
                    $variation->set_stock_status($variation_data['stock_status']);
                }

                // 保存属性
                if (isset($variation_data['attributes'])) {
                    $attributes = $variation_data['attributes'];
                    $variation->set_attributes($attributes);
                }

                // 保存变体
                $variation->save();

                return $variation_id;
            }

            return false;
        }

        function cus_display_item_meta($html, $item, $args)
        {
            $bCustomProduct = false;
            if ($item->get_variation_id()) {
                $metaData = $item->get_meta_data();
                foreach ($metaData as $meta_key => $meta_value) {
                    if ($meta_value->value == 'CUSTOM') {
                        $bCustomProduct = true;
                    }
                }
            }

            if (!$bCustomProduct) {
                $message = $this->getNonCustomDESC();
            } else {
                $message =  $this->getCustomDESC();
                try{
                    $defaultPrice = '$' . ( $item->get_total() ?? 100);
                } catch (\Exception $ex) {
                    $defaultPrice = '$100';
                }
                $message = str_replace( '[PRICE]', $defaultPrice, $message);
            }
            $HeaderHtml = '<p class="singleTitle" style="font-size: 12px; max-width:700px;"><b>' . $message . '</b></p>';

            return $HeaderHtml . $html;
        }

        function cus_get_description($description,  $item)
        {
            if (!empty($description)) {
                return $description;
            }
            $attributes = $item->get_attributes();
            $bCustomProduct = true;
            foreach ($attributes as $key => $attribute) {
                if ($attribute !== 'CUSTOM') {
                    $bCustomProduct = false;
                }
            }

            if (!$bCustomProduct) {
                $message = 'The price [PRICE] is only 50% of the total price.';
            } else {
                $message =  'The price [PRICE] is only deposit in total price.';
            }
            try{
                $defaultPrice = '$' . ( $item->get_price() ?? 100);
            } catch (\Exception $ex) {
                $defaultPrice = '$100';
            }
            return str_replace( '[PRICE]', $defaultPrice, $message);
        }

    }


    $customProduct365d4u = new Cus365d4uProductStandard();

    add_filter( 'woocommerce_before_variations_form', array( $customProduct365d4u, 'cus_before_variations' ));

    add_filter( 'woocommerce_product_tabs', array( $customProduct365d4u, 'cus_get_pro_tabs' ), 11, 1 );
    add_filter( 'woocommerce_product_description_heading', array( $customProduct365d4u, 'cus_hide_product_heading' ), 11, 1 );

    add_filter( 'woocommerce_ajax_variation_threshold', array( $customProduct365d4u, 'cus_ajax_variation_threshold' ), 10, 2 );

    add_action('wp_head', array( $customProduct365d4u, 'preload_css_script'));

    add_action('woocommerce_variable_product_before_variations', array( $customProduct365d4u, 'cus_add_Custom_variation_button'));

    add_action('wp_ajax_cwcv_add_custom_variation', array( $customProduct365d4u, 'cwcv_add_custom_variation_ajax'));

    add_action('wp_ajax_cwcv_remove_custom_variation', array( $customProduct365d4u, 'cwcv_remove_custom_variation_ajax'));

    add_action('wp_ajax_cwcv_custom_load_variations', array( $customProduct365d4u, 'cwcv_custom_load_variations_ajax'));
    add_action('wp_ajax_cwcv_custom_bulk_update_variations', array( $customProduct365d4u, 'cwcv_custom_bulk_update_variations_ajax'));


    add_action('woocommerce_display_item_meta', array($customProduct365d4u, 'cus_display_item_meta'), 10, 3);
    add_filter('woocommerce_product_variation_get_description', array($customProduct365d4u, 'cus_get_description'), 10, 2);

}