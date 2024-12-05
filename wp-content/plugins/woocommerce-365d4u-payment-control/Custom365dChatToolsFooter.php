<?php
if (!class_exists('Custom365dChatToolsFooter')) {
    class Custom365dChatToolsFooter {
        function custom_footer_script() {
            $imgDomain = '/icons';

            $instagram = 'https://www.instagram.com/custom365d/';
            $email = 'support@custom365d.com';
            $whatsapp = '16173097891';
            $telephone = '+16173097891';
            $sms = '+16173097891';
            echo <<<FOOTER
      <div class="cus365d4-LiveChatChannel">
         <div class="cus365d4-LiveChatChannelsWidget" style="">
            <div class="Vtl-LiveChatChannelsWidget__Content cusHide">
                  <div class="Vtl-ChannelList Vtl-ChannelList Vtl-ChannelList--Minimal">
                      <div class="Vtl-Channel Vtl-Channel--Minimal instagram">
                        <a class="Vtl-Channel__Link" target="_blank" rel="noreferrer" href="{$instagram}">
                            <img class="Vtl-Channel__Icon" width="50" height="50"
                             src="{$imgDomain}/instagram.svg" 
                              style="border-radius: 50%;">
                         </a>
                       </div>
                       <div class="Vtl-Channel Vtl-Channel--Minimal email">
                          <a class="Vtl-Channel__Link" target="_blank" rel="noreferrer" href="mailto:{$email}">
                              <img class="Vtl-Channel__Icon" width="50" height="50"  
                              src="{$imgDomain}/email.svg"  
                              style="border-radius: 50%;">
                             <span class="Vtl-Channel__Tooltip">E-mail</span>
                         </a>
                        </div>
                        <div class="Vtl-Channel Vtl-Channel--Minimal whatsapp">
                            <a class="Vtl-Channel__Link"
                             target="_blank" rel="noreferrer" 
                             href="https://wa.me/{$whatsapp}">
                             <img class="Vtl-Channel__Icon" src="{$imgDomain}/whatsapp.svg"
                              width="50" height="50"  style="border-radius: 50%;">
                              <span class="Vtl-Channel__Tooltip">WhatsApp</span>
                            </a>
                        </div>
                        <div class="Vtl-Channel Vtl-Channel--Minimal sms">
                             <a class="Vtl-Channel__Link"
                                target="_blank" rel="noreferrer" href="sms:{$sms}">
                                <img class="Vtl-Channel__Icon" width="50" height="50"
                                 src="{$imgDomain}/sms.svg"  
                                style="border-radius: 50%;"><span class="Vtl-Channel__Tooltip">SMS</span>
                               </a>
                        </div>
                        <div class="Vtl-Channel Vtl-Channel--Minimal phone">
                           <a class="Vtl-Channel__Link" target="_blank" rel="noreferrer" href="tel:{$telephone}">
                             <img class="Vtl-Channel__Icon" src="{$imgDomain}/phone.svg"
                              width="50" height="50"  style="border-radius: 50%;">
                              <span class="Vtl-Channel__Tooltip">Phone</span>
                             </a>
                        </div>
                </div>
            </div>
            <div class="cus365d4-Trigger" onclick="onFooterClicked();">
               <div class="cus365d4-Trigger__Icon">
                   <img class="cus365d4-LiveChatChannels-Animated--wobble-wobble" alt="Chat icon" 
                      src="{$imgDomain}/theme-5.png"
                        >
                </div>
                <div class="cus365d4-Trigger_Close cusHide"><span>✕</span></div>
             </div>
        </div>
   </div>
   <style> 
      .phone{
          display: none;
      }
       .cus365d4-Trigger{
          cursor: pointer;
       }
       .Vtl-Channel__Tooltip{
          display:none;
       }     
       .Vtl-LiveChatChannelsWidget__Content{
              display:block;
               position: absolute;
              bottom: 60px;
       }
        #carthike-chat-button-container {
           display: none;
        }
        .cus365d4-LiveChatChannelsWidget{
            position: fixed;
            bottom: 20px;
            z-index: 9999999;
            right: 20px;
            height: 48px;
            width: 46px;
        }
        .cus365d4-Trigger__Icon, .cus365d4-Trigger_Close{
           background-color: #2967d0;
           border-radius: 50%;
        }
        .cus365d4-LiveChatChannel .cus365d4-Trigger__Icon img{
            width:30px;
            height:30px;
            margin-top: 8px;
            margin-left: 8px;
         }
        .cusHide{
           display: none!important;
        }
        .cus365d4-Trigger_Close{
           padding:8px;
           text-align: center;
        }   
        @media only screen and (max-width: 979px) {
            .cus365d4-Trigger_Close{
               padding:10px 8px;
               text-align: center;
            }   
        }    
    </style>
   <script>
       var jq = jQuery.noConflict();
       function onFooterClicked() {
           if (jq('.cus365d4-Trigger .cus365d4-Trigger_Close').hasClass('cusHide')) {
                jq('.cus365d4-Trigger .cus365d4-Trigger_Close').removeClass('cusHide');
                jq('.cus365d4-Trigger .cus365d4-Trigger__Icon').addClass('cusHide');
                jq('.Vtl-LiveChatChannelsWidget__Content').removeClass('cusHide');
           } else {
               jq('.cus365d4-Trigger .cus365d4-Trigger_Close').addClass('cusHide');
               jq('.cus365d4-Trigger .cus365d4-Trigger__Icon').removeClass('cusHide');
                jq('.Vtl-LiveChatChannelsWidget__Content').addClass('cusHide'); 
           }
       }      
   </script>
FOOTER;
        }

        /**
         * Facebook event code
         *
         * @return void
         */
        function custom_facebook_event_code()
        {
            echo <<<FOOTER
<!-- Facebook Pixel Code CUSTOM -->
<script>
  !function(f,b,e,v,n,t,s)
  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
  n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];
  s.parentNode.insertBefore(t,s)}(window, document,'script',
  'https://connect.facebook.net/en_US/fbevents.js');
  fbq('init', '1027353525366824'); 
  fbq('track', 'PageView');
</script> 
<!-- End Facebook Pixel Code -->
FOOTER;

        }
    }
    $chatFooter = new Custom365dChatToolsFooter();
    add_action('wp_footer', array($chatFooter, 'custom_footer_script'));
    add_action('wp_footer', array($chatFooter, 'custom_facebook_event_code'));
}